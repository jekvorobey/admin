<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Core\PublicEventBillingReport;
use Exception;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Oms\Dto\Order\OrderType;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Dto\OrderStatus;
use Greensight\Oms\Dto\Payment\PaymentStatus;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use MerchantManagement\Services\MerchantService\MerchantService;
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
     * @return \Illuminate\Support\Collection|\Pim\Dto\PublicEvent\PublicEventDto[]
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
        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        $orders = $orderService->orders($restQuery);

        //Получаем реферальных партнеров заказов
        $referralIds = collect();
        foreach ($orders as $order) {
            $referralIds->merge($order->basket->items->pluck('referrer_id')->filter()->unique());
            $referralIds->merge($order->promoCodes->pluck('owner_id')->filter()->unique());
        }
        $referralIds = $referralIds->unique();

        // Получаем покупателей и реферальных партнеров заказов
        $customerIds = $orders->pluck('customer_id')->merge($referralIds)->unique()->all();
        $customerQuery = $customerService->newQuery()
            ->setFilter('id', $customerIds);
        /** @var Collection|CustomerDto[] $customers */
        $customers = $customerService->customers($customerQuery)->keyBy('id');

        // Получаем самих пользователей
        $userIds = $customers->pluck('user_id')->all();
        /** @var Collection|UserDto[] $users */
        $users = collect();
        if ($userIds) {
            // chunking for prevent large query string
            foreach (array_chunk($userIds, 50) as $userIdsChunk) {
                $userQuery = $userService->newQuery()->setFilter('id', $userIdsChunk);
                $users->concat(
                    $userService->users($userQuery)->keyBy('id')
                );
            }
        }

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

    /**
     * @throws \Exception
     */
    public function downloadEventBillingList(int $merchantId, Request $request)
    {
        $organizersIds = $this->loadOrganizers($merchantId);
        $events = $this->loadEvents($organizersIds);
        $sprints = [];
        foreach ($events as $eventId) {
            $eventSprints = resolve(PublicEventService::class)->getSprints($eventId)->pluck('id')->toArray();
            $sprints = array_merge($sprints, $eventSprints);
        }

        $dates = [
            'date_from' => $request->date_from ?: now()->startOfMonth()->subMonth()->toDateString(),
            'date_to' => $request->date_to ?: now()->subMonth()->endOfMonth()->toDateString(),
        ];

        $orderService = resolve(OrderService::class);
        $query = $this->makeDownloadRestQuery($sprints, $dates);
        $orders = $this->loadOrders($orderService, $query);

        $merchantService = resolve(MerchantService::class);
        $merchant = $merchantService->merchants((new RestQuery())->setFilter('id', $merchantId))->first();

        PublicEventBillingReport::makePublicEventReport($orders, $merchant, $dates);
    }

    protected function makeDownloadRestQuery(array $sprints, array $dates): DataQuery
    {
        $orderService = resolve(OrderService::class);

        return $orderService->newQuery()
            ->include('basketitem', 'promoCodes')
            ->setFilter('created_at', '>=', Carbon::parse($dates['date_from'])->startOfDay()->toDateTimeString())
            ->setFilter('created_at', '<=', Carbon::parse($dates['date_to'])->endOfDay()->toDateTimeString())
            ->setFilter('sprint_id', $sprints)
            ->setFilter('status', OrderStatus::DONE)
            ->setFilter('payment_status', PaymentStatus::PAID)
            ->setFilter('is_canceled', 0)
            ->setFilter('type', OrderType::PUBLIC_EVENT)
            ->addSort('created_at', 'desc');
    }
}
