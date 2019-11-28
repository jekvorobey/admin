<?php
namespace App\Http\Controllers\Orders\Create;


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

    ): array
    {
        return [];
    }

    public function searchProducts(

    ): array
    {
        return [];
    }
}
