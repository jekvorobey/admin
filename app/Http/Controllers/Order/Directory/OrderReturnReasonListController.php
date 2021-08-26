<?php

namespace App\Http\Controllers\Order\Directory;

use App\Http\Controllers\Controller;
use Greensight\Oms\Core\OmsException;
use Greensight\Oms\Services\OrderService\Dto\OrderReturnReasonDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\Request;

class OrderReturnReasonListController extends Controller
{
    public function list(Request $request, OrderService $orderService)
    {
        $page = $request->get('page', 1);
        [$total, $reasons] = $this->loadReasons($orderService, $page);

        return $this->render('Order/Directory/OrderReturnReasonList', [
            'iOrderReturnReasons' => $reasons,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, OrderService $orderService)
    {
        $page = $request->get('page', 1);
        [$total, $reasons] = $this->loadReasons($orderService, $page);

        return response()->json([
            'orderReturnReasons' => $reasons,
            'total' => $total['total'],
        ]);
    }

    public function save(Request $request, OrderService $orderService)
    {
        $this->validate($request, [
            'id' => ['nullable', 'int'],
            'orderReturnReason' => ['required', 'array'],
            'orderReturnReason.text' => ['required', 'string'],
        ]);

        $id = $request->get('id');
        $orderReturnReason = $request->get('orderReturnReason');
        $orderReturnReasonDto = new OrderReturnReasonDto($orderReturnReason);

        if ($id) {
            $orderService->updateOrderReturnReason($id, $orderReturnReasonDto);
        } else {
            $orderService->createOrderReturnReason($orderReturnReasonDto);
        }

        return response()->json();
    }

    public function delete(Request $request, OrderService $orderService)
    {
        $id = $request->get('id');

        $this->validate($request, [
            'id' => ['required', 'int'],
        ]);

        $orderService->deleteOrderReturnReason($id);

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
