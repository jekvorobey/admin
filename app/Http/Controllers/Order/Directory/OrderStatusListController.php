<?php

namespace App\Http\Controllers\Order\Directory;

use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\OrderStatus;
use Illuminate\Http\JsonResponse;

/**
 * Class OrderStatusListController
 * @package App\Http\Controllers\Order\Directory
 */
class OrderStatusListController extends Controller
{
    public const PER_PAGE = 20;

    /**
     * @return mixed
     */
    public function index()
    {
        $this->title = 'Статусы заказа';
        $this->loadBasketTypes = true;

        return $this->render('Order/Directory/OrderStatusList', [
            'iOrderStatuses' => $this->getOrderStatuses(),
            'iCurrentPage' => $this->getPage(),
            'iPager' => $this->getPager(),
        ]);
    }

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
