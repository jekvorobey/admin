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
            ->get()->pluck('id')->toArray();
    }

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
        $users = collect();
        if ($userIds) {
            $userQuery = $userService->newQuery()
                ->setFilter('id', $userIds);
            /** @var Collection|UserDto[] $users */
            $users = $userService->users($userQuery)->keyBy('id');
        }

        $orders = $orders->map(function (OrderDto $order) use ($users, $customers) {
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

        return $orders;
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
            'date_from' => $request->date_from ?? Carbon::now()->startOfMonth()->subMonth()->toDateString(),
            'date_to' => $request->date_to ?? Carbon::now()->subMonth()->endOfMonth()->toDateString(),
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
        $restQuery = $orderService->newQuery()->include('basketitem', 'promoCodes');
        $restQuery->setFilter('created_at', '>=', $dates['date_from']);
        $restQuery->setFilter('created_at', '<=', $dates['date_to']);
        $restQuery->setFilter('sprint_id', $sprints);
        $restQuery->setFilter('is_canceled', 0);
        $restQuery->setFilter('type', OrderType::PUBLIC_EVENT);
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }
}
