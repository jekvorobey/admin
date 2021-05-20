<?php

namespace App\Http\Controllers\Order\Create;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Services\BasketService\BasketService;
use Greensight\Store\Services\StockService\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Services\MerchantService\MerchantService;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Services\ProductService\ProductService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderCreateController
 * @package App\Http\Controllers\Order\Create
 */
class OrderCreateController extends Controller
{
    /**
     * @return mixed
     */
    public function create()
    {
        $this->title = 'Создание заказа';

        return $this->render('Order/Create', [
            'iOrder' => '',
            'iDeliveries' => '',
            'offerSaleStatuses' => OfferSaleStatus::allStatuses(),
        ]);
    }

    public function searchCustomer(
        Request $request,
        UserService $userService,
        CustomerService $customerService
    ): JsonResponse {
        $data = $request->all();
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($data, [
            'type' => 'required|string',
            'search' => 'required',
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        $query = $userService->newQuery();
        $query->include('profile');

        switch ($data['type']) {
            case 'fio':
                $query->setFilter('first_name', 'like', $data['search']);
                $query->setFilter('last_name', 'like', $data['search']);
                break;
            case 'email':
                $query->setFilter('email', 'like', $data['search']);
                break;
            default:
                $query->setFilter('id', (int) $data['search']);
        }

        $users = $userService->users($query);
        if ($users->isEmpty()) {
            throw new NotFoundHttpException();
        }

        $customers = $customerService->customers($query);
        if ($customers->isEmpty()) {
            throw new NotFoundHttpException();
        }

        $customer = $customers->first();

        return response()->json($customer);
    }

    /**
     * @param Request $request
     * @param ProductService $productService
     * @param StockService $stockService
     * @param MerchantService $merchantService
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function searchProducts(
        Request $request,
        ProductService $productService,
        StockService $stockService,
        MerchantService $merchantService
    ): JsonResponse {
        $data = $request->all();
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($data, [
            'search' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        // Продукты и их офферы
        $productVendors = explode(',', $data['search']);
        $productVendors = array_map('trim', $productVendors);

        $query = $productService->newQuery()
            ->include('offers')
            ->setFilter('vendor_code', $productVendors);
        $products = $productService->products($query);
        if ($products->isEmpty()) {
            throw new NotFoundHttpException();
        }

        // Изображения продуктов
        $productsIds = $products->pluck('id')->values()->toArray();
        $images = $productService
            ->allImages($productsIds, 1)
            ->pluck('url', 'productId')
            ->toArray();

        // Остатки на складах по продуктам
        $stocksQuery = $stockService->newQuery()
            ->setFilter('product_id', $productsIds);
        $stocks = $stockService->stocks($stocksQuery);

        // Мерчанты офферов
        $merchantsIds = $products->pluck('merchant_id')->unique()->values()->toArray();
        $query = $merchantService->newQuery()
            ->setFilter('id', $merchantsIds);
        $merchants = $merchantService->merchants($query);

//        $stocks = $stocks->groupBy('offer_id')
//            ->map(function ($item) {
//                $item = $item->toArray();
//                return array_merge(...$item);
//            })
//            ->toArray();


        foreach ($products as &$product) {
            $product['photo'] = $images[$product->id] ?? '';
            $product['qty'] = 1;
        }

        return response()->json([
            'products' => $products,
            'stocks' => $stocks,
            'merchants' => $merchants,
        ]);
    }

    public function createOrder(Request $request, BasketService $basketService)
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'items' => 'required|array',
            'delivery_service' => ['required', Rule::in(array_keys(DeliveryServiceDto::allServices()))],
            'delivery_method' => ['required', Rule::in(array_keys(DeliveryMethod::allMethods()))],
            'delivery_address' => ['nullable', 'array'],
            'price' => 'numeric|required',
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        $basket = $basketService->getByUser($data['user_id'], 1, true);

        foreach ($data['items'] as $item) {
            $basketService->setItem($basket->id, $item['offer_id']);
        }

//        $basketService->setItem();
    }
}
