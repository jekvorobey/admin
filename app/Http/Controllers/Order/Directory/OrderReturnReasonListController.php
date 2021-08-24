<?php

namespace App\Http\Controllers\Order\Directory;

use App\Http\Controllers\Controller;
use Greensight\Oms\Core\OmsException;
use Greensight\Oms\Services\OrderService\Dto\OrderReturnReasonDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrderReturnReasonListController extends Controller
{
    public function list(Request $request, OrderService $orderService)
    {
        $page = $request->get('page', 1);
        [$total, $reasons] = $this->loadReasons($orderService, $page);

        return $this->render('Order/OrderReturnReasonList', [
            'iReasons' => $reasons,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, OrderService $orderService)
    {
        $page = $request->get('page', 1);
        [$total, $brands] = $this->loadReasons($orderService, $page);

        return response()->json([
            'brands' => $brands,
            'total' => $total['total'],
        ]);
    }

    public function save(Request $request, OrderService $orderService)
    {
        $id = $request->get('id');
        $text = $request->get('text');

        if (!$text) {
            throw new BadRequestHttpException('text required');
        }

        $orderReturnReasonDto = new OrderReturnReasonDto();

        if ($id) {
            $orderService->updateOrderReturnReason($id, $orderReturnReasonDto);
        } else {
            $orderService->createOrderReturnReason($orderReturnReasonDto);
        }

        return response()->json();
    }

    public function delete(Request $request, OrderService $orderService)
    {
        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        $orderService->deleteOrderReturnReason($ids);

        return response()->json();
    }

    /**
     * @param $page
     * @return array
     * @throws OmsException
     */
    private function loadReasons(OrderService $orderService, $page): array
    {
        $query = $orderService->newQuery()->pageNumber($page, 10);

        $total = $orderService->orderReturnReasonsCount($query);
        $reasons = $orderService->orderReturnReasons($query);
        return [$total, $reasons];
    }
}
