<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Box\Spout\Common\Exception\IOException;
use Box\Spout\Common\Exception\UnsupportedTypeException;
use Box\Spout\Common\Type;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Box\Spout\Writer\Exception\WriterNotOpenedException;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Customer\Dto\ReferralOrderHistoryDto;
use Greensight\Customer\Services\ReferralService\Dto\GetReferralOrderHistoryDto;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Oms\Dto\OrderDto;
use Greensight\Oms\Services\OrderService\OrderService;
use Illuminate\Http\JsonResponse;
use MerchantManagement\Dto\MerchantBillOperation\ShipmentStatusDto;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabOrderReferrerController extends Controller
{
    public function load($id): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        return response()->json([
            'orders' => $this->loadOrders($id),
        ]);
    }

    /**
     * @throws UnsupportedTypeException
     * @throws WriterNotOpenedException
     * @throws IOException
     */
    public function export($id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

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

    public function delete($id, $history_id, ReferralService $referralService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $referralService->deleteReferralOrderHistory((int) $id, (int) $history_id);

        return response()->json([
            'orders' => $this->loadOrders($id),
        ]);
    }

    protected function loadOrders($customer_id)
    {
        /** @var ReferralService $referralService */
        $referralService = resolve(ReferralService::class);
        $referralOrderHistories = $referralService->getReferralOrderHistories(
            (new GetReferralOrderHistoryDto())->setReferralId($customer_id)
        );

        if ($referralOrderHistories->isEmpty()) {
            return collect();
        }

        $orderService = resolve(OrderService::class);
        $orderServiceQuery = $orderService->newQuery();
        $orderServiceQuery
            ->addFields(OrderDto::entity(), 'id', 'number')
            ->setFilter('number', $referralOrderHistories->pluck('order_number')->toArray());
        $orders = $orderService->orders($orderServiceQuery)->keyBy('number');

        $merchantService = resolve(MerchantService::class);
        $merchantsQuery = $merchantService->newQuery();
        $merchantsQuery
            ->setFilter('order_id', $orders->pluck('id')->toArray())
            ->setFilter('product_id', $referralOrderHistories->pluck('product_id')->toArray())
            ->setFilter('shipment_status', ShipmentStatusDto::SHIPMENT_STATUS_RETURN);

        $merchantBillingOperations = collect($merchantService->merchantBillingList($merchantsQuery)['items'])->keyBy(function ($billOperation) {
            return implode('_', [$billOperation['order_id'], $billOperation['product_id']]);
        });

        return $referralOrderHistories->map(function (ReferralOrderHistoryDto $orderHistoryDto) use ($merchantBillingOperations, $orders) {

            $billingOperationKey = implode('_', [$orders[$orderHistoryDto->order_number]->id, $orderHistoryDto->product_id]);
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
                'is_returned' => isset($merchantBillingOperations[$billingOperationKey]),
            ];
        });
    }
}
