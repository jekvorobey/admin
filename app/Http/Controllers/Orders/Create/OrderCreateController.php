<?php
namespace App\Http\Controllers\Orders\Create;


use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\Oms\Dto\BasketItemDto;
use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\Delivery\DeliveryDto;
use Greensight\Oms\Dto\Delivery\ShipmentDto;
use Illuminate\Http\JsonResponse;
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
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function searchProducts(
        Request $request,
        ProductService $productService
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

        $query = $productService->newQuery();

        $vendorCodes = explode(',', $data['search']);
        $query->setFilter('vendor_code', $vendorCodes);

        $products = $productService->products($query);

        return response()->json($products);
    }
}
