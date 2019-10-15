<?php
namespace App\Http\Controllers\Orders;


use Illuminate\Http\Request;
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
use Pim\Services\BrandService\BrandService;

class FlowController extends Controller
{
    public function index(
        Request $request,
        OrderService $orderService,
        BrandService $brandService
    )
    {
        $this->title = 'Поток сборки';
        $this->breadcrumbs = 'orders.flowList';

        $restQuery = $this->makeRestQuery($orderService, $request);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($restQuery, $orderService);


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



    protected function loadOrders(DataQuery $restQuery, OrderService $orderService): Collection
    {
        $orders = $orderService->orders($restQuery);

        $orders = $orders->map(function (OrderDto $order) {
            $data = $order->toArray();

            $data['status'] = $order->status()->toArray();
            $data['delivery_type'] = $order->deliveryType()->toArray();
            $data['created_at'] = (new Carbon($order->created_at))->format('h:i:s Y-m-d');
            $data['delivery_time'] = (new Carbon($order->delivery_time))->format('h:i:s Y-m-d');
            $data['delivery_store'] = DeliveryStore::allStores()[array_rand(DeliveryStore::allStores())]->toArray(); //todo
            $data['delivery_city'] = DeliveryCity::allCities()[array_rand(DeliveryCity::allCities())]->toArray(); //todo
            $data['payment_method'] = PaymentMethod::allMethods()[array_rand(PaymentMethod::allMethods())]->toArray(); //todo

            return $data;
        });

        return $orders;
    }

    protected function makeRestQuery(OrderService $orderService, Request $request): DataQuery
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

        if (isset($filter['deliveryTime'])) {
            $restQuery->setFilter('delivery_time', '>=', $filter['deliveryTime']);
            $restQuery->setFilter('delivery_time', '<=', (new Carbon($filter['deliveryTime']))->modify('+1 day'));
        }

//        if (isset($filter['deliveryStore'])) {
//            $restQuery->setFilter('delivery_store', $filter['deliveryStore']);
//        }

        if (isset($filter['deliveryMethod'])) {
            $restQuery->setFilter('delivery_method', $filter['deliveryMethod']);
        }

        if (isset($filter['orderStatus'])) {
            $restQuery->setFilter('status', $filter['orderStatus']);
        }

        return $restQuery;
    }
}
