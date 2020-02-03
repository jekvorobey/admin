<?php
namespace App\Http\Controllers\Orders\Create;


use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Logistics\Dto\Lists\DeliveryMethod;
use Greensight\Oms\Dto\BasketItemDto;
use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Greensight\Oms\Services\BasketService\BasketService;
use Greensight\Store\Services\StockService\StockService;
use Illuminate\Http\JsonResponse;
use Greensight\Logistics\Dto\Lists\DeliveryService as DeliveryServiceDto;
use Greensight\Oms\Services\DeliveryService\DeliveryService;
use Greensight\Oms\Services\ShipmentService\ShipmentService;
use Greensight\Oms\Services\ShipmentPackageService\ShipmentPackageService;
use Greensight\Oms\Services\OrderService\OrderService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Dto\Front;
use Pim\Dto\Offer\OfferSaleStatus;
use Pim\Services\ProductService\ProductService;
use Pim\Dto\Product\ProductDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\BrandDto;
use Greensight\Oms\Dto\History\HistoryDto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OrderCreateController
 * @package App\Http\Controllers\Orders\Create
 */
class OrderCreateController extends Controller
{
    public function create(
        OrderService $orderService,
        ProductService $productService,
        UserService $userService,
        CustomerService $customerService,
        DeliveryService $deliveryService,
        ShipmentService $shipmentService,
        ShipmentPackageService $shipmentPackageService
    )
    {
        $this->title = 'Создание заказа';



        return $this->render('Orders/Create', [
            'iOrder' => '',
            'iDeliveries' => '',
            'offerSaleStatuses' => OfferSaleStatus::allStatuses(),
        ]);
    }

    public function searchUsers
    (
        Request $request,
        CustomerService $customerService
    ): JsonResponse
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $data = $request->all();
        $validator = Validator::make($data, [
            'search' => 'required',
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        $query = $customerService->newQuery();

        $userIds = explode(',', $data['search']);
        $query->setFilter('user_id', $userIds);

        $products = $customerService->customers($query);

        return response()->json($products);
    }


    /**
     * @param Request $request
     * @param ProductService $productService
     * @param StockService $stockService
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function searchProducts(
        Request $request,
        ProductService $productService,
        StockService $stockService
    ): JsonResponse
    {
        /** @var \Illuminate\Validation\Validator $validator */
        $data = $request->all();
        $validator = Validator::make($data, [
            'search' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }

        // Получаем продукты и их офферы
        $vendorCodes = explode(',', $data['search']);
        $query = $productService->newQuery()
            ->include('offers')
            ->setFilter('vendor_code', $vendorCodes);

        $products = $productService->products($query);

        // Получаем остатки на всех складах по продуктам
//        $stocksQuery = $stockService->newQuery()
//            ->setFilter('id', array_values($products->pluck('customer_id')->unique()->toArray()));
//        $stocks = $stockService->stocks($stocksQuery);

        return response()->json($products);
    }

    public function createOrder(Request $request, OrderService $orderService, BasketService $basketService)
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
            $basketService->setItem($basket->id , $item['offer_id']);
        }

//        $basketService->setItem();


    }
}
