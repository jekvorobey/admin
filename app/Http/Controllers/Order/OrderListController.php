<?php

namespace App\Http\Controllers\Order;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\AbstractDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\PointDto;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\DeliveryType;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\PaymentMethod;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;

/**
 * Class OrderListController
 * @package App\Http\Controllers\Order
 */
class OrderListController extends Controller
{
    /**
     * @param Request $request
     * @param OrderService $orderService
     * @param BrandService $brandService
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request, OrderService $orderService, BrandService $brandService) {
        $this->title = 'Список заказов';

        $restQuery = $this->makeRestQuery($orderService, true);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($restQuery);

        return $this->render('Order/List', [
            'iOrders' => $orders,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
            'orderStatuses' => OrderStatus::allStatuses(),
            'deliveryTypes' => DeliveryType::allTypes(),
            'paymentMethods' => PaymentMethod::allMethods(),
            'brands' => $brandService->newQuery()->addFields(BrandDto::entity(), 'id', 'name')->brands(),
            'iFilter' => $this->getFilter(true),
            'iSort' => $request->get('sort', 'created_at'),
        ]);
    }

    /**
     * @return int
     */
    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @param  OrderService  $orderService
     * @return JsonResponse
     * @throws \Exception
     */
    public function page(OrderService $orderService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($orderService);
        $orders = $this->loadOrders($restQuery);
        $result = [
            'orders' => $orders,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $orderService->ordersCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * @param bool $withDefault
     * @return array
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::make(request('filter') ??
            [],
            [
                'number' => 'string|someone',
                'customer' => 'string|someone',
                'created_at' => 'array|someone',
                'created_at.*' => 'string',
                'status' => Rule::in(array_keys(OrderStatus::allStatuses())),
                'price_from' => 'numeric|someone',
                'price_to' => 'numeric|someone',
                'offer_xml_id' => 'string|someone',
                'product_vendor_code' => 'string|someone',
                'brands' => 'array|someone',
                'brands.*' => 'integer',
                'payment_method' => Rule::in(array_keys(PaymentMethod::allMethods())),
                'delivery_type' => Rule::in(array_keys(DeliveryType::allTypes())),
            ]
        )->attributes();
    }

    /**
     * @param DataQuery $restQuery
     * @return Collection
     */
    protected function loadOrders(DataQuery $restQuery): Collection
    {
        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);
        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);
        /** @var ListsService $listsService */
        $listsService = resolve(ListsService::class);

        $orders = $orderService->orders($restQuery);

        // Получаем покупателей заказов
        $customerQuery = $customerService->newQuery()
            ->setFilter('id', array_values($orders->pluck('customer_id')->unique()->toArray()));
        /** @var Collection|CustomerDto[] $customers */
        $customers = $customerService->customers($customerQuery)->keyBy('id');

        // Получаем самих пользователей
        $userQuery = $userService->newQuery()
            ->setFilter('id', array_values($customers->pluck('user_id')->unique()->toArray()))
            ->setFilter('front', Front::FRONT_SHOWCASE);
        /** @var Collection|UserDto[] $users */
        $users = $userService->users($userQuery)->keyBy('id');

        /** @var Collection|PointDto[] $points */
        $points = collect();
        $deliveries = collect();
        foreach ($orders as $order) {
            $deliveries = $deliveries->merge($order->deliveries);
        }
        $pointIds = $deliveries->pluck('point_id')->filter()->unique()->values()->all();
        if ($pointIds) {
            $points = $listsService->points($listsService->newQuery()->setFilter('id', $pointIds))
                ->keyBy('id');
        }

        $orders = $orders->map(function (OrderDto $order) use ($users, $customers, $points) {
            $data = $order->toArray();

            $data['customer'] = $customers->has($order->customer_id) && $users->has($customers[$order->customer_id]->user_id)
                ? $users[$customers[$order->customer_id]->user_id] : [];

            $delivery_dates = collect();
            $cities = collect();
            foreach ($order->deliveries as $delivery) {
                $delivery_dates->push(
                    Carbon::createFromFormat(AbstractDto::DATE_TIME_FORMAT, $delivery->delivery_at)
                        ->format(AbstractDto::DATE_FORMAT)
                );

                if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
                    if ($points->has($delivery->point_id)) {
                        $cities->push($points[$delivery->point_id]->getCityWithType());
                    }
                } else {
                    $cities->push($delivery->getCity());
                }
            }

            $data['status'] = $order->status()->toArray();
            $data['payment_status'] = $order->paymentStatus()->toArray();
            $data['delivery_method'] = $order->deliveries[0]->deliveryMethod()->toArray();
            $data['created_at'] = (new Carbon($order->created_at))->format('H:i:s Y-m-d');
            $data['updated_at'] = (new Carbon($order->updated_at))->format('H:i:s Y-m-d');
            $data['status_at'] = (new Carbon($order->status_at))->format('H:i:s Y-m-d');
            $data['delivery_dates'] = $delivery_dates->unique()->join(', ');
            $data['cities'] = $cities->unique()->join(', ');

            return $data;
        });

        return $orders;
    }

    /**
     * @param  OrderService  $orderService
     * @param  bool  $withDefaultFilter
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(OrderService $orderService, bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $orderService->newQuery()->include('deliveries');

        $page = $this->getPage();
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'created_at':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter($key, '>=', $value[0]);
                            $restQuery->setFilter($key, '<=', $value[1]);
                        }
                        break;
                    case 'price_from':
                        if ($value) {
                            $restQuery->setFilter('price', '>=', $value);
                        }
                        break;
                    case 'price_to':
                        if ($value) {
                            $restQuery->setFilter('price', '<=', $value);
                        }
                        break;

                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }
}
