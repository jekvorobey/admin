<?php
namespace App\Http\Controllers\Orders;


use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Oms\Dto\BasketItemDto;
use Greensight\Oms\Dto\OrderHistoryDto;
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
use Pim\Services\ProductService\ProductService;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\BrandDto;

/**
 * Class FlowController
 * @package App\Http\Controllers\Orders
 */
class FlowController extends Controller
{
    /**
     * @param Request $request
     * @param OrderService $orderService
     * @param BrandService $brandService
     * @param CustomerService $customerService
     * @param UserService $userService
     * @return mixed
     */
    public function index(
        Request $request,
        OrderService $orderService,
        BrandService $brandService,
        CustomerService $customerService,
        UserService $userService
    )
    {
        $this->title = 'Поток сборки';
        $this->breadcrumbs = 'orders.flowList';

        $restQuery = $this->makeRestQuery($orderService, $request);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($restQuery, $orderService, $customerService, $userService);

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
     * @return JsonResponse
     */
    public function page(
        Request $request,
        OrderService $orderService,
        CustomerService $customerService,
        UserService $userService
    ): JsonResponse {
        $restQuery = $this->makeRestQuery($orderService, $request);
        $orders = $this->loadOrders($restQuery, $orderService, $customerService, $userService);
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
     * @return Collection
     */
    protected function loadOrders(
        DataQuery $restQuery,
        OrderService $orderService,
        CustomerService $customerService,
        UserService $userService
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

        $orders = $orders->map(function (OrderDto $order) use ($users, $customers) {
            $data = $order->toArray();

            if(isset($customers[$data['customer_id']]) && isset($users[$customers[$data['customer_id']]['user_id']])) {
                $data['customer'] = $users[$customers[$data['customer_id']]['user_id']];
            }

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

    public function detail(
        int $id,
        OrderService $orderService,
        ProductService $productService,
        UserService $userService
    )
    {
        $restQuery = $orderService
            ->newQuery()
            ->setFilter('id', $id)
            ->include(BasketItemDto::entity());
        $orders = $orderService->orders($restQuery);
        if (!$orders->count()) {
            throw new NotFoundHttpException();
        }

        /** @var OrderDto $order */
        $order = $orders->first();
        $data = $order->toArray();

        if (isset($data['basket']['items'])) {
            $basketItems = &$data['basket']['items'];

            $offersIds = array_column($basketItems, 'offer_id');
            $restQuery = $productService->newQuery()
                ->addFields(ProductDto::entity(), 'vendor_code', 'segment')
                ->include(CategoryDto::entity(), BrandDto::entity());
            $productsByOffers = $productService->productsByOffers($restQuery, $offersIds);
            $productsIds = $productsByOffers->keyBy(function ($item) {
                return $item['product']->id;
            })->keys()->toArray();
            if ($productsIds) {
                $images = $productService
                    ->allImages($productsIds, 1)
                    ->pluck('url', 'productId'); //todo Добавить типы фото и константы для них
            }

            foreach ($basketItems as &$basketItem) {
                if (!isset($productsByOffers[$basketItem['offer_id']])) {
                    continue;
                }
                $productByOffers = $productsByOffers[$basketItem['offer_id']];
                /** @var ProductDto $product */
                $product = $productByOffers['product'];

                $basketItem['product'] = $product->toArray();
                $basketItem['product']['photo'] = $images[$product->id] ?? '';
                $basketItem['cost'] = $basketItem['qty'] * $basketItem['price'];
            }
        }

        $data['notification'] = collect(['Упаковать с особой любовью', 'Обязательно вложить в заказ подарок', 'Обработать заказ в первую очередь', '', '', ''])->random(); //todo
        $data['status'] = $order->status()->toArray();
        $data['delivery_type'] = $order->deliveryType()->toArray();
        $data['delivery_method'] = $order->deliveryMethod()->toArray();
        $data['delivery_cost'] = rand(0, (int)$data['cost'] / 4); //todo
        $data['created_at'] = (new Carbon($order->created_at))->format('h:i:s Y-m-d');
        $data['delivery_time'] = (new Carbon($order->delivery_time))->format('h:i:s Y-m-d');
        $data['delivery_store'] = DeliveryStore::allStores()[array_rand(DeliveryStore::allStores())]->toArray(); //todo
        $data['totalQty'] = $order->basket()->items()->reduce(function (int $sum, BasketItemDto $item) {
            return $sum + $item->qty;
        }, 0);
        $data['payment_method'] = PaymentMethod::allMethods()[array_rand(PaymentMethod::allMethods())]->toArray(); //todo
        $data['discount'] = rand(0, (int)$data['cost'] / 4); //todo
        $data['cost_without_discount'] = $data['cost'] - $data['discount'];
        $data['products_cost'] = $data['cost_without_discount'] - $data['delivery_cost'];
        $data['box_qty'] = rand(1, 4); //todo
        $data['weight'] = rand(100, 3000); //todo
        $data['packaging_type'] = collect(['стандартная', 'подарочная', 'специальная'])->random(); //todo
        $data['delivery_address'] = 'г. Москва, г. Зеленоград, Центральный проспект, корпус 305'; //todo

        // История по заказу
        $history = [];
        try {
            $restQuery = $orderService
                ->newQuery()
                ->setFilter('order_id', $order->id);
            $history = $orderService->ordersHistory($restQuery);

            if(!empty($history) && count($history) > 1) {
                $users = $history->pluck('user_id')->toArray();

                $restQuery = $orderService
                    ->newQuery()
                    ->setFilter('id', $users);

                $users = $userService->users($restQuery);

                $history = $history->map(function(OrderHistoryDto $item) use($users) {
                    $data = $item->toArray();
                    $data['data'] = json_decode($item->data, true);
                    $data['typeName'] = $item->typeDto()->name;

                    if($item->user_id) {
                        $data['user'] = $users->filter(function($user) use($item) {
                            return $user->id == $item->user_id;
                        })->first();
                    }
                    return $data;
                });
            }

            $history = $history->sortByDesc('created_at')->toArray();
            $history = array_values($data['history']);

        } catch (\Exception $e) {}

        $data['history'] = $history;

        return $this->render('Orders/Flow/View', [
            'iOrder' => $data
        ]);
    }
}
