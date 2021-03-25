<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\Oms\Dto\Order\OrderType;
use Greensight\Oms\Services\OrderService\OrderService;
use Pim\Dto\PublicEvent\TicketDto;
use Pim\Dto\PublicEvent\TicketStatus;
use Pim\Services\PublicEventService\PublicEventService;
use Illuminate\Http\Request;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Pim\Services\PublicEventTicketService\PublicEventTicketService;

class PublicEventTicketsController extends Controller
{
    public function getList(Request $request, int $eventId)
    {
        $sprints = $request->input('sprint_id')
            ? [(int)$request->input('sprint_id')]
            : resolve(PublicEventService::class)->getSprints($eventId)->pluck('id')->toArray();

        $tickets = $this->getData($sprints);
        return response()->json(['tickets' => $tickets]);
    }

    protected function getData($sprints)
    {
        $orderService = resolve(OrderService::class);
        $restQuery = $this->makeRestQuery($orderService, $sprints);
        $orders = $orderService->orders($restQuery);
        if (empty($orders))
            return [];
        $ticketIds = [];
        $orders = $orders->map(function (OrderDto $order) use (&$ticketIds) {
            $data['id'] = $order->id;
            $data['number'] = $order->number;
            $data['tickets'] = [];
            $data['count_tickets'] = 0;
            foreach ($order->basket->items as $item) {
                $data['tickets'] = array_merge($data['tickets'], $item->product['ticket_ids']);
                $data['count_tickets'] += $item->product && !empty($item->product['ticket_ids'])
                    ? count($item->product['ticket_ids'])
                    : 0;
            }

            $ticketIds = array_merge($ticketIds, $data['tickets']);
            return $data;
        });
        array_unique($ticketIds);
        if (!$ticketIds)
            return [];

        $orderByTicketId = [];
        foreach ($orders as $order) {
            foreach ($order['tickets'] as $ticketId) {
                $orderByTicketId[$ticketId] = $order;
            }
        }

        $activities = resolve(CustomerService::class)->activities()->setActive(true)->load()->pluck('name', 'id')->toArray();
        $ticketService = resolve(PublicEventTicketService::class);
        $restQuery = $ticketService->newQuery()->include('type', 'status')->setFilter('ids', $ticketIds);
        $tickets = $ticketService->tickets($restQuery);

        return $tickets->map(function (TicketDto $ticket) use ($orderByTicketId, $activities) {
            $data = $ticket->toArray();
            $data['status'] = TicketStatus::statusById($ticket->status_id);
            $data['profession'] = $activities[$ticket->profession_id] ?? null;
            $data['order'] = !empty($orderByTicketId[$ticket->id])
                ? [
                    'id' => $orderByTicketId[$ticket->id]['id'],
                    'number' => $orderByTicketId[$ticket->id]['number'],
                    'count_tickets' => $orderByTicketId[$ticket->id]['count_tickets']
                ]
                : null;
            return $data;
        });
    }

    protected function makeRestQuery(OrderService $orderService, array $sprints): DataQuery
    {
        $restQuery = $orderService->newQuery()->include(
            'basketitem'
        );

        $restQuery->setFilter('sprint_id', $sprints);
        $restQuery->setFilter('type', OrderType::PUBLIC_EVENT);
        return $restQuery;
    }

    public function getFile(Request $request, int $eventId)
    {
        $sprints = $request->input('sprint_id')
            ? [(int)$request->input('sprint_id')]
            : resolve(PublicEventService::class)->getSprints($eventId)->pluck('id')->toArray();

        $tickets = $this->getData($sprints);
        $fileName = 'tickets.csv';

        $columns = array('ID билета', 'ID заказа', 'ФИО', 'Телефон', 'Email', 'Профессия', 'Тип билета', 'Кол-во билетов в заказе', 'Статус');

        return response()->streamDownload(function () use ($tickets, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns, ";");
            foreach ($tickets as $ticket) {
                fputcsv($file, [
                    $ticket['id'],
                    $ticket['order']['number'],
                    $ticket['last_name'] . " " . $ticket['first_name'] . " " . $ticket['middle_name'],
                    $ticket['phone'],
                    $ticket['email'],
                    $ticket['profession'],
                    $ticket['type']['name'],
                    $ticket['order']['count_tickets'],
                    $ticket['status']['name'],
                ], ";");
            }

            fclose($file);
        }, $fileName);
    }
}
