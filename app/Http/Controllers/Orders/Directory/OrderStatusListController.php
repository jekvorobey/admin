<?php

namespace App\Http\Controllers\Orders\Directory;

use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\OrderStatus;
use Illuminate\Http\JsonResponse;

/**
 * Class OrderStatusListController
 * @package App\Http\Controllers\Orders\Directory
 */
class OrderStatusListController extends Controller
{
    /** @var int - кол-во выводимых статусов на странице */
    const PER_PAGE = 20;

    /**
     * @return mixed
     */
    public function index() {
        $this->title = 'Статусы заказа';
        
        return $this->render('Orders/Directory/OrderStatusList', [
            'iOrderStatuses' => $this->getOrderStatuses(),
            'iCurrentPage' => $this->getPage(),
            'iPager' => $this->getPager(),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function page(): JsonResponse
    {
        $result = [
            'orderStatuses' => $this->getOrderStatuses(),
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $this->getPager();
        }
        
        return response()->json($result);
    }

    /**
     * @return int
     */
    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @return array
     */
    protected function getPager(): array
    {
        $orderStatuses = collect(OrderStatus::allStatuses());

        return [
            'pageSize' => self::PER_PAGE,
            'pages' => ceil($orderStatuses->count() / self::PER_PAGE),
            'total' => $orderStatuses->count(),
        ];
    }

    /**
     * @return array
     */
    protected function getOrderStatuses(): array
    {
        return collect(OrderStatus::allStatuses())
            ->forPage($this->getPage(), self::PER_PAGE)
            ->values()
            ->toArray();
    }
}
