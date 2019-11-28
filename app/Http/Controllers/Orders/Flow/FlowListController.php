<?php
namespace App\Http\Controllers\Orders\Flow;


use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Oms\Dto\DeliveryCity;
use Greensight\Oms\Dto\DeliveryMethod;
use Greensight\Oms\Dto\DeliveryStore;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\PaymentMethod;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Pim\Services\BrandService\BrandService;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Dto\Front;

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
     * @param CustomerService $customerService
     * @param UserService $userService
     * @param DeliveryService $deliveryService
     * @return mixed
     * @throws \Exception
     */
    public function index(
        Request $request,
        OrderService $orderService,
        BrandService $brandService,
        CustomerService $customerService,
        UserService $userService,
        DeliveryService $deliveryService
    )
    {
        $this->title = 'Поток сборки';
        $this->breadcrumbs = 'orders.flowList';

        $restQuery = $this->makeRestQuery($orderService, $request);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($restQuery, $orderService, $customerService, $userService, $deliveryService);

        return $this->render('Orders/Flow/List', [
            'iOrders' => $orders,
            'iCurrentPage' => $request->get('page', 1),
            'iPager' => $pager,
            'orderStatuses' => OrderStatus::allStatuses(),
            'deliveryStores' => DeliveryStore::allStores(), //todo
            'deliveryCities' => DeliveryCity::allCities(), //todo
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
     * @param CustomerService $customerService
     * @param UserService $userService
     * @param DeliveryService $deliveryService
     * @return JsonResponse
     * @throws \Exception
     */
    public function page(
        Request $request,
        OrderService $orderService,
        CustomerService $customerService,
        UserService $userService,
        DeliveryService $deliveryService
    ): JsonResponse {
        $restQuery = $this->makeRestQuery($orderService, $request);
        $orders = $this->loadOrders($restQuery, $orderService, $customerService, $userService, $deliveryService);
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
     * @param OrderService $orderService
     * @param CustomerService $customerService
     * @param UserService $userService
     * @param DeliveryService $deliveryService
     * @return Collection
     */
    protected function loadOrders(
        DataQuery $restQuery,
        OrderService $orderService,
        CustomerService $customerService,
        UserService $userService,
        DeliveryService $deliveryService
    ): Collection
    {
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

        $orders = $orders->map(function (OrderDto $order) use ($users, $customers, $deliveries) {
            $data = $order->toArray();

            if(isset($customers[$data['customer_id']]) && isset($users[$customers[$data['customer_id']]['user_id']])) {
                $data['customer'] = $users[$customers[$data['customer_id']]['user_id']];
            }

            $data['status'] = $order->status()->toArray();
            $data['delivery_method'] = $order->deliveryMethod()->toArray();
            $data['deliveries'] = $deliveries->where('order_id', $order->id)->values()->toArray();
            $data['created_at'] = (new Carbon($order->created_at))->format('h:i:s Y-m-d');
            $data['updated_at'] = (new Carbon($order->updated_at))->format('h:i:s Y-m-d');
            $data['delivery_time'] = (new Carbon($order->delivery_time))->format('h:i:s Y-m-d');
            $data['delivery_city'] = DeliveryCity::allCities()[array_rand(DeliveryCity::allCities())]->toArray(); //todo

            return $data;
        });

        return $orders;
    }

    /**
     * @param OrderService $orderService
     * @param Request $request
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(
        OrderService $orderService,
        Request $request
    ): DataQuery
    {
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


        if(isset($filter['customer'])) {
            $restQuery->setFilter('customer', $filter['customer']);
        }

        if (isset($filter['deliveryTime'])) {
            $restQuery->setFilter('deliveryTime', '>=', $filter['deliveryTime'][0]);
            $restQuery->setFilter('deliveryTime', '<=', $filter['deliveryTime'][1]);
        }

        if (isset($filter['deliveryCount'])) {
            $restQuery->setFilter('deliveryCount', $filter['deliveryCount']);
        }

        return $restQuery;
    }
}
