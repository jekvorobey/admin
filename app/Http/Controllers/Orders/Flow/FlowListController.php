<?php

namespace App\Http\Controllers\Orders\Flow;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\AbstractDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Services\ListsService\ListsService;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\PaymentMethod;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Pim\Services\BrandService\BrandService;

/**
 * Class FlowListController
 * @package App\Http\Controllers\Orders\Flow
 */
class FlowListController extends Controller
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

        $restQuery = $this->makeRestQuery($request);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($restQuery);

        return $this->render('Orders/Flow/List', [
            'iOrders' => $orders,
            'iCurrentPage' => $request->get('page', 1),
            'iPager' => $pager,
            'orderStatuses' => OrderStatus::allStatuses(),
            'deliveryMethods' => DeliveryMethod::allMethods(),
            'paymentMethods' => PaymentMethod::allMethods(),
            'brands' => $brandService->newQuery()->addFields('brand', 'id', 'name')->brands(),
            'iFilter' => $request->get('filter', []),
            'iSort' => $request->get('sort', 'created_at'),
        ]);
    }

    /**
     * @param Request $request
     * @param OrderService $orderService
     * @return JsonResponse
     * @throws \Exception
     */
    public function page(Request $request, OrderService $orderService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($request);
        $orders = $this->loadOrders($restQuery);
        $result = [
            'orders' => $orders,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $orderService->ordersCount($restQuery);
        }

        return response()->json($result);
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
        /** @var DeliveryService $deliveryService */
        $deliveryService = resolve(DeliveryService::class);
        /** @var ListsService $listsService */
        $listsService = resolve(ListsService::class);

        $orders = $orderService->orders($restQuery);

        // Получаем покупателей заказов
        $customerQuery = new RestQuery();
        $customerQuery->setFilter('id', array_values($orders->pluck('customer_id')->unique()->toArray()));
        $customers = $customerService->customers($customerQuery);

        // Получаем самих пользователей
        $userQuery = new RestQuery();
        $userQuery->addFields('profile', '*');
        $userQuery->setFilter('id', array_values($customers->pluck('user_id')->unique()->toArray()));
        $userQuery->setFilter('front', Front::FRONT_SHOWCASE);

        $users = $userService->users($userQuery)->keyBy('id')->toArray();
        $customers = $customers->keyBy('id')->toArray();

        $deliveryQuery = new RestQuery();
        $deliveryQuery->setFilter('order_id', array_values($orders->pluck('id')->unique()->toArray()));
        $deliveries = $deliveryService->deliveries($deliveryQuery);

        $points = collect();
        $pointIds = $deliveries->pluck('point_id')->filter()->unique()->values()->all();
        if ($pointIds) {
            $points = $listsService->points((new RestQuery())->setFilter('id', $pointIds))->keyBy('id');
        }

        $orders = $orders->map(function (OrderDto $order) use ($users, $customers, $deliveries, $points) {
            $data = $order->toArray();
            $orderDeliveries = $deliveries->where('order_id', $order->id);

            if (isset($customers[$data['customer_id']]) && isset($users[$customers[$data['customer_id']]['user_id']])) {
                $data['customer'] = $users[$customers[$data['customer_id']]['user_id']];
            }

            $deliveryDate = collect();
            $cities = collect();
            $data['deliveries'] = [];
            foreach ($deliveries as $delivery) {
                if ($delivery->order_id != $order->id) {
                    continue;
                }
                $data['deliveries'][] = $delivery;
                $deliveryDate->push(Carbon::createFromFormat(AbstractDto::DATE_TIME_FORMAT, $delivery->delivery_at)->format(AbstractDto::DATE_FORMAT));

                if ($delivery->delivery_method == DeliveryMethod::METHOD_PICKUP) {
                    if ($points->has($delivery->point_id)) {
                        $cities->push($points[$delivery->point_id]->address['city']);
                    }
                } else {
                    if (isset($delivery->delivery_address['city'])) {
                        $cities->push($delivery->delivery_address['city']);
                    }
                }
            }

            $data['status'] = $order->status()->toArray();
            $data['delivery_method'] = [];// todo $order->deliveryMethod()->toArray();
            $data['created_at'] = (new Carbon($order->created_at))->format('h:i:s Y-m-d');
            $data['updated_at'] = (new Carbon($order->updated_at))->format('h:i:s Y-m-d');
            $data['deliveryDate'] = $deliveryDate->unique()->join(', ');
            $data['cities'] = $cities->unique()->join(', ');

            return $data;
        });

        return $orders;
    }

    /**
     * @param Request $request
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(Request $request): DataQuery
    {
        /** @var OrderService $orderService */
        $orderService = resolve(OrderService::class);

        $restQuery = $orderService->newQuery();

        $page = $request->get('page', 1);
        $restQuery->pageNumber($page, 20);

        $filter = $request->get('filter', []);
        if (isset($filter['number'])) {
            $restQuery->setFilter('number', $filter['number']);
        }

        if (isset($filter['created'])) {
            $restQuery->setFilter('created_at', '>=', $filter['created'][0]);
            $restQuery->setFilter('created_at', '<=', $filter['created'][1]);
        }

        if (isset($filter['deliveryMethod'])) {
            $restQuery->setFilter('delivery_method', $filter['deliveryMethod']);
        }

        if (isset($filter['orderStatus'])) {
            $restQuery->setFilter('status', $filter['orderStatus']);
        }


        if (isset($filter['customer'])) {
            $restQuery->setFilter('customer', $filter['customer']);
        }

        if (isset($filter['deliveryCount'])) {
            $restQuery->setFilter('deliveryCount', $filter['deliveryCount']);
        }

        $restQuery->addSort('id', 'desc');

        return $restQuery;
    }
}
