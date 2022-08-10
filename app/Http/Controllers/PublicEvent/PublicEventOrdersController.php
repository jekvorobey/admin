<?php

namespace App\Http\Controllers\PublicEvent;

use App\Core\CustomerHelper;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Oms\Dto\Order\OrderType;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Pim\Services\PublicEventService\PublicEventService;
use Illuminate\Http\Request;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Oms\Dto\OrderDto;
use Illuminate\Support\Collection;

class PublicEventOrdersController extends Controller
{
    public const PER_PAGE = 15;

    public function getList(Request $request, int $eventId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $sprints = $request->input('sprint_id')
            ? [(int) $request->input('sprint_id')]
            : resolve(PublicEventService::class)->getSprints($eventId)->pluck('id')->toArray();

        $orderService = resolve(OrderService::class);
        $restQuery = $this->makeRestQuery($orderService, $sprints);
        $pager = $orderService->ordersCount($restQuery);
        $orders = $this->loadOrders($orderService, $restQuery);

        return response()->json([
            'orders' => [
                'page' => $this->getPage(),
                'pager' => $pager,
                'orders' => $orders,
            ],
        ]);
    }

    protected function makeRestQuery(OrderService $orderService, array $sprints): DataQuery
    {
        $restQuery = $orderService->newQuery()->include('basketitem', 'promoCodes');

        $page = $this->getPage();
        $restQuery->pageNumber($page, self::PER_PAGE);
        $restQuery->setFilter('sprint_id', $sprints);
        $restQuery->setFilter('type', OrderType::PUBLIC_EVENT);
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    protected function loadOrders(OrderService $orderService, DataQuery $restQuery): Collection
    {
        $orders = $orderService->orders($restQuery);
        if ($orders->isEmpty()) {
            return collect();
        }

        //Получаем реферальных партнеров заказов
        $referralIds = collect();
        foreach ($orders as $order) {
            $referralIds->merge($order->basket->items->pluck('referrer_id')->filter()->unique());
            $referralIds->merge($order->promoCodes->pluck('owner_id')->filter()->unique());
        }
        $referralIds = $referralIds->unique();

        // Получаем покупателей и реферальных партнеров заказов
        $customerIds = $orders->pluck('customer_id')->merge($referralIds)->unique()->all();
        $customers = CustomerHelper::getCustomersByIds($customerIds);

        // Получаем самих пользователей
        $userIds = $customers->pluck('user_id')->all();
        $users = UserHelper::getUsersByIds($userIds);

        return $orders->map(function (OrderDto $order) use ($users, $customers) {
            $data = $order->toArray();

            $data['customer'] = $customers->has($order->customer_id) && $users->has($customers[$order->customer_id]->user_id)
                ? $users[$customers[$order->customer_id]->user_id] : null;

            $data['status'] = $order->status()->toArray();
            $data['created_at'] = date_time2str(new Carbon($order->created_at));
            $data['updated_at'] = date_time2str(new Carbon($order->updated_at));
            $data['count_tickets'] = 0;
            foreach ($order->basket->items as $item) {
                $data['count_tickets'] += $item->product && !empty($item->product['ticket_ids'])
                    ? count($item->product['ticket_ids'])
                    : 0;
            }

            return $data;
        });
    }
}
