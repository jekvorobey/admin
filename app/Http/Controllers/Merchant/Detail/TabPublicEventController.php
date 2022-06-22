<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Core\CustomerHelper;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Oms\Dto\Order\OrderType;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\Payment\PaymentStatus;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Pim\Dto\PublicEvent\PublicEventDto;
use Pim\Services\PublicEventOrganizerService\PublicEventOrganizerService;
use Pim\Services\PublicEventService\PublicEventService;

class TabPublicEventController extends Controller
{
    public const PER_PAGE = 50;

     /**
     * AJAX пагинация списка операций биллинга Мастер-классов
     *
     * @throws Exception
     */
    public function eventBillingList(int $merchantId): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MERCHANTS);

        $organizersIds = $this->loadOrganizers($merchantId);
        $events = $this->loadEvents($organizersIds);

        $sprints = [];
        foreach ($events as $eventId) {
            $eventSprints = resolve(PublicEventService::class)->getSprints($eventId)->pluck('id')->toArray();
            $sprints = array_merge($sprints, $eventSprints);
        }

        $orderService = resolve(OrderService::class);
        $restQuery = $this->makeRestQuery($orderService, $sprints);
        $pager = $sprints ? $orderService->ordersCount($restQuery) : null;
        $orders = $sprints ? $this->loadOrders($orderService, $restQuery) : null;

        return response()->json([
            'billingList' => [
                'page' => $this->getPage(),
                'pager' => $pager,
                'items' => $orders,
            ],
        ]);
    }

    /**
     * @param $merchantId
     * @return array $organizers
     */
    private function loadOrganizers($merchantId): array
    {
        $publicEventOrganizerService = resolve(PublicEventOrganizerService::class);
        $query = $publicEventOrganizerService->query()->setFilter('merchant_id', $merchantId);

        return $publicEventOrganizerService->find($query)->pluck('id')->toArray();
    }

    /**
     * @param $organizersIds
     * @return Collection|PublicEventDto[]
     * @throws Exception
     */
    private function loadEvents($organizersIds)
    {
        if (!count($organizersIds)) {
            return [];
        }

        $publicEventService = resolve(PublicEventService::class);

        return $publicEventService
            ->query()
            ->include('sprints', 'sprints.ticketTypes', 'sprints.ticketTypes.offer', 'sprints.tickets')
            ->setFilter('organizer_id', $organizersIds)
            ->get()
            ->pluck('id')
            ->toArray();
    }

    protected function makeRestQuery(OrderService $orderService, array $sprints): DataQuery
    {
        return $orderService->newQuery()
            ->include('basketitem', 'promoCodes')
            ->pageNumber($this->getPage(), self::PER_PAGE)
            ->setFilter('sprint_id', $sprints)
            ->setFilter('status', OrderStatus::DONE)
            ->setFilter('payment_status', PaymentStatus::PAID)
            ->setFilter('type', OrderType::PUBLIC_EVENT)
            ->addSort('created_at', 'desc');
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
