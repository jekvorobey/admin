<?php
namespace App\Http\Controllers\Orders\Flow;


use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Oms\Dto\BasketItemDto;
use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Illuminate\Support\Carbon;
use Greensight\Oms\Dto\DeliveryStore;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\PaymentMethod;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Oms\Services\ShipmentPackageService\ShipmentPackageService;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Dto\Front;
use Pim\Services\ProductService\ProductService;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\BrandDto;
use Greensight\Oms\Dto\History\HistoryDto;

/**
 * Class FlowDetailController
 * @package App\Http\Controllers\Orders\Flow
 */
class FlowDetailController extends Controller
{
    public function detail(
        int $id,
        OrderService $orderService,
        ProductService $productService,
        UserService $userService,
        CustomerService $customerService,
        DeliveryService $deliveryService,
        ShipmentService $shipmentService,
        ShipmentPackageService $shipmentPackageService
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


        if (isset($data['basket']['items']) && !empty($data['basket']['items'])) {
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


        $userQuery = new RestQuery();
        $userQuery->addFields('profile', '*');
        $userQuery->setFilter('id', $data['customer_id']);
        $userQuery->setFilter('front', Front::FRONT_SHOWCASE);

        $data['customer'] = $userService->users($userQuery)->first();

        // Получаем пользователя заказа
        $customerQuery = new RestQuery();
        $customerQuery->setFilter('id', $data['customer_id']);

        // Все заказы пользователя
        $previousQuery = new RestQuery();
        $previousQuery->setFilter('id', '!=', $data['id']);
        $previousQuery->setFilter('customer_id', $data['customer_id']);
        $previousOrders = $orderService->orders($customerQuery);
        $data['customer_history'] = $previousOrders ?? null;

        // Доставки заказа
        $deliveries = $deliveryService->deliveries(null, $order->id);

        // Отправления заказа
        $shipmentQuery = new RestQuery();
        $shipmentQuery->setFilter('delivery_id', $deliveries->pluck('id')->unique()->values()->toArray());
        $shipments = $shipmentService->shipments($shipmentQuery);

        // Коробки отправлений
        $shipmentPackagesQuery = new RestQuery();
        $shipmentPackagesQuery->setFilter('shipment_id', $shipments->pluck('id')->unique()->values()->toArray());
        $shipmentPackages = $shipmentPackageService->shipmentPackages($shipmentPackagesQuery);

        // Добавляем коробки в отправления
        $shipments = $shipments->map(function (ShipmentDto $item) use ($shipmentPackages) {
            $data = $item->toArray();
            $data['packages'] = $shipmentPackages->where('shipment_id', $item->id)->values()->toArray();

            return $data;
        });

        // Добавляем отправления в доставки
        $data['deliveries'] = $deliveries->map(function (DeliveryDto $item) use ($shipments) {
            $data = $item->toArray();
            $data['shipments'] = $shipments->where('delivery_id', $item->id)->values()->toArray();

            return $data;
        });


        $data['shipments'] = $shipments->toArray();
        $data['shipments_packages'] = $shipmentPackages->toArray();

        // История заказа
        $data['history'] = [];
        try {
            $history = $orderService->orderHistory($order->id);

            if (!empty($history) && count($history) > 0) {
                $users = $history->pluck('user_id')->unique()->toArray();

                $restQuery = $userService
                    ->newQuery()
                    ->setFilter('id', $users);

                $users = $userService->users($restQuery);

                $history = $history->map(function (HistoryDto $item) use ($users) {
                    $data = $item->toArray();
                    $data['type'] = $item->type()->toArray();

                    if ($item->user_id) {
                        $data['user'] = $users->filter(function ($user) use ($item) {
                            return $user->id == $item->user_id;
                        })->first();
                    }

                    return $data;
                });
            }

            $data['history'] = $history->sortByDesc('created_at')->values()->toArray();

        } catch (Exception $e) {
        }

        $data['notification'] = collect(['Упаковать с особой любовью', 'Обязательно вложить в заказ подарок', 'Обработать заказ в первую очередь', '', '', ''])->random(); //todo
        $data['status'] = $order->status()->toArray();
        $data['delivery_type'] = $order->deliveryType()->toArray();
        $data['delivery_method'] = $order->deliveryMethod()->toArray();
        $data['delivery_cost'] = rand(0, (int)$data['cost'] / 4); //todo
        $data['created_at'] = (new Carbon($order->created_at))->format('d.m.y h:i');
        $data['updated_at'] = (new Carbon($order->updated_at))->format('y.m.d h:i');
        $data['delivery_time'] = (new Carbon($order->delivery_time))->format('y.m.d h:i');
        $data['delivery_store'] = DeliveryStore::allStores()[array_rand(DeliveryStore::allStores())]->toArray(); //todo
        $data['totalQty'] = !empty($data['basket']['items']) ? $order->basket()->items()->reduce(function (int $sum, BasketItemDto $item) {
            return $sum + $item->qty;
        }, 0) : null;
        $data['payment_method'] = PaymentMethod::allMethods()[array_rand(PaymentMethod::allMethods())]->toArray(); //todo
        $data['discount'] = rand(0, (int)$data['cost'] / 4); //todo
        $data['cost_without_discount'] = $data['cost'] - $data['discount'];
        $data['products_cost'] = $data['cost_without_discount'] - $data['delivery_cost'];
        $data['box_qty'] = rand(1, 4); //todo
        $data['weight'] = rand(100, 3000); //todo
        $data['packaging_type'] = collect(['стандартная', 'подарочная', 'специальная'])->random(); //todo
        $data['delivery_address'] = 'г. Москва, г. Зеленоград, Центральный проспект, корпус 305'; //todo

        return $this->render('Orders/Flow/View', [
            'iOrder' => $data
        ]);
    }
}
