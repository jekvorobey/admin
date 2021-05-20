<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Greensight\Customer\Dto\ReferralOrderHistoryDto;
use Greensight\Customer\Services\ReferralService\Dto\GetReferralOrderHistoryDto;
use Greensight\Customer\Services\ReferralService\ReferralService;

class TabOrderReferrerController extends Controller
{
    public function load($id)
    {
        return response()->json([
            'orders' => $this->loadOrders($id),
        ]);
    }

    public function export($id)
    {
        $writer = WriterFactory::createFromType(Type::XLSX);

        $writer->openToBrowser("Реферальные заказы {$id}.xlsx");

        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'Номер заказа',
            'Товар заказа',
            'Кол-во',
            'Размер вознаграждения',
            'Дата заказа',
            'Источник',
            'ID реферала',
        ], null));

        $orders = $this->loadOrders($id);
        foreach ($orders as $order) {
            $writer->addRow(WriterEntityFactory::createRowFromArray([
                $order['order_number'],
                $order['name'],
                $order['qty'],
                $order['price_commission'],
                $order['order_date'],
                $order['source_name'],
                $order['customer_id'],
            ], null));
        }

        $writer->close();
    }

    public function delete($id, $history_id, ReferralService $referralService)
    {
        $referralService->deleteReferralOrderHistory((int) $id, (int) $history_id);

        return response()->json([
            'orders' => $this->loadOrders($id),
        ]);
    }

    protected function loadOrders($customer_id)
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);

        return $referralService->getReferralOrderHistories(
            (new GetReferralOrderHistoryDto())->setReferralId($customer_id)
        )->map(function (ReferralOrderHistoryDto $orderHistoryDto) {
            return [
                'id' => $orderHistoryDto->id,
                'order_number' => $orderHistoryDto->order_number,
                'name' => $orderHistoryDto->name,
                'qty' => $orderHistoryDto->qty,
                'price_commission' => $orderHistoryDto->price_commission,
                'order_date' => $orderHistoryDto->order_date,
                'source' => $orderHistoryDto->source,
                'source_name' => $orderHistoryDto->getSourceName(),
                'customer_id' => $orderHistoryDto->customer_id,
            ];
        });
    }
}
