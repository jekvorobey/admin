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
use Greensight\Customer\Dto\ReferralBillOperationDto;
use Greensight\Customer\Dto\ReferralOrderHistoryDto;
use Greensight\Customer\Services\ReferralService\Dto\GetReferralBillOperationDto;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Illuminate\Http\JsonResponse;

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
        $referralReturnBillingOperations = $referralService->getReferralBillOperations(
            (new GetReferralBillOperationDto())
                ->setReferralId($customer_id)
                ->setRelations(['orderHistory'])
        );

        if ($referralReturnBillingOperations->isEmpty()) {
            return collect();
        }

        return $referralReturnBillingOperations
            ->where('orderHistory')
            ->map(function (ReferralBillOperationDto $billOperationDto) {
                $orderHistoryDto = new ReferralOrderHistoryDto($billOperationDto->orderHistory);
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
                    'is_returned' => $billOperationDto->type === ReferralBillOperationDto::TYPE_RETURN,
                ];
            });
    }
}
