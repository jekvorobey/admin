<?php
namespace App\Http\Controllers\Orders\Flow;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Dto\BasketItemDto;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\DeliveryStatus;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Dto\History\HistoryDto;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\PaymentMethod;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Illuminate\Support\Carbon;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FlowDetailController
 * @package App\Http\Controllers\Orders\Flow
 */
class FlowDetailController extends Controller
{
    /**
     * @param int $id
     * @param OrderService $orderService
     * @param ProductService $productService
     * @param UserService $userService
     * @param DeliveryService $deliveryService
     * @param ShipmentService $shipmentService
     * @return mixed
     * @throws \Exception
     */
    public function detail(
        int $id,
        OrderService $orderService,
        ProductService $productService,
        UserService $userService,
        DeliveryService $deliveryService,
        ShipmentService $shipmentService
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

        $this->title = 'Редактирование заказа ' . $data['number'];

        if (isset($data['basket']['items']) && !empty($data['basket']['items'])) {
            $basketItems = &$data['basket']['items'];

            $offersIds = array_column($basketItems, 'offer_id');
            $restQuery = $productService->newQuery()
                ->addFields(ProductDto::entity(), 'vendor_code')
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
            }

            $basketItems = collect($basketItems);
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
        $shipmentQuery = $shipmentService
            ->newQuery()
            ->addFields(
                ShipmentDto::entity(),
                'id',
                'store_id',
                'status',
                'number',
                'required_shipping_at',
                'cost',
                'package_qty',
                'created_at',
                'updated_at'
            )
            ->setFilter('delivery_id', $deliveries->pluck('id')->unique()->values()->toArray())
            ->include('basketItems', 'items', 'packages.items');
        $shipments = $shipmentService->shipments($shipmentQuery);

        // Добавляем отправления в доставки
        $data['deliveries'] = $deliveries->map(function (DeliveryDto $item) use ($shipments) {
            $data = $item->toArray();
            $data['shipments'] = $shipments->where('delivery_id', $item->id)->values()->toArray();

            return $data;
        });


        // Берем информацию по товарам из корзин и объединяем ее с товарами в отправлениях и коробках
        $shipments->transform(function (ShipmentDto $shipment) use ($basketItems) {
            $shipmentItems = [];

            foreach ($shipment->basketItems as $item) {
                $basketItem = $basketItems->where('offer_id', $item['offer_id'])->first();
                foreach ($basketItem as $key => $value) {
                    $item[$key] = $value;
                }
                $shipmentItems[] = $item;
            }

            if($shipment->packages) {
                $shipmentPackages = [];

                foreach ($shipment->packages as $package) {
                    $itemBasketIds = collect($package['items'])->pluck('basket_item_id')->toArray();
                    $items = $basketItems->whereIn('id', $itemBasketIds)->values()->toArray();
                    $package['items'] = $items;

                    $shipmentPackages[] = $package;
                }

                $shipment->packages = $shipmentPackages;
            }

            $shipment->basketItems = $shipmentItems;

            return $shipment;
        });

        $data['shipments'] = $shipments;

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

        } catch (\Exception $e) {
        }

        $data['delivery_statuses'] = DeliveryStatus::allStatuses();
        $data['delivery_services'] = DeliveryServiceDto::allServices();
        $data['notification'] = collect(['Упаковать с особой любовью', 'Обязательно вложить в заказ подарок', 'Обработать заказ в первую очередь', '', '', ''])->random(); //todo
        $data['status'] = $order->status()->toArray();
        $data['delivery_type'] = $order->deliveryType()->toArray();
        $data['delivery_method'] = []; // todo
        $data['delivery_cost'] = rand(0, (int)$data['cost'] / 4); //todo
        $data['created_at'] = (new Carbon($order->created_at))->format('d.m.y H:i');
        $data['updated_at'] = (new Carbon($order->updated_at))->format('y.m.d H:i');
        $data['totalQty'] = !empty($data['basket']['items']) ? $order->basket()->items()->reduce(function (int $sum, BasketItemDto $item) {
            return $sum + $item->qty;
        }, 0) : null;
        $data['payment_method'] = PaymentMethod::allMethods()[array_rand(PaymentMethod::allMethods())]->toArray(); //todo
        $data['discount'] = rand(0, (int)$data['cost'] / 4); //todo
        $data['cost_without_discount'] = $data['cost'] - $data['discount'];
        $data['products_cost'] = $data['cost_without_discount'] - $data['delivery_cost'];
        $data['weight'] = rand(100, 3000); //todo
        $data['packaging_type'] = collect(['стандартная', 'подарочная', 'специальная'])->random(); //todo
        $data['delivery_address'] = 'г. Москва, г. Зеленоград, Центральный проспект, корпус 305'; //todo

        return $this->render('Orders/Flow/View', [
            'iOrder' => $data
        ]);
    }

}
