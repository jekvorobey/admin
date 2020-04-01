<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerDocumentDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Illuminate\Validation\Rule;

/**
 * Class TabDocumentController
 * @package App\Http\Controllers\Customers\Detail
 */
class TabDocumentController extends Controller
{
    /**
     * @param int $customerId
     * @param CustomerService $customerService
     * @param FileService $fileService
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $customerId, CustomerService $customerService, FileService $fileService)
    {
        $documents = $customerService->documents($customerId);
        $files = [];
        if ($documents) {
            $files = $fileService->getFiles($documents->pluck('file_id')->all())->keyBy('id');
        }

        return response()->json([
            'documents' => $documents->map(function (CustomerDocumentDto $document) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($document->file_id);
                if (!$file) {
                    return false;
                }
                return [
                    'id' => $document->id,
                    'period_since' => $document->period_since,
                    'period_to' => $document->period_to,
                    'date' => $document->updated_at,
                    'amount_reward' => $document->amount_reward,
                    'statusId' => $document->status,
                    'statusVerbal' => $document->statusName($document->status),
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
        ]);
    }

    public function export(int $customerId, CustomerService $customerService, FileService $fileService)
    {
        $this->validate(request(), [
            'format' => [
                'required',
                'string',
                // Using available types in Box\Spout\Common\Type //
                Rule::in(['csv', 'xlsx', 'ods']),
            ]
        ]);
        $format = request('format');

        $writer = WriterFactory::createFromType($format);
        $writer->openToBrowser("Акты о реферальных зачислениях реферрера {$customerId}.{$format}");
        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'Номер акта',
            'Начало периода',
            'Окончание периода',
            'Дата документа',
            'Сумма вознаграждения',
            'Статус',
            'Файл',
        ], null));

        $documents = $customerService->documents($customerId);
        $files = [];
        if ($documents) {
            $files = $fileService->getFiles($documents->pluck('file_id')->all())->keyBy('id');
        }

        foreach ($documents as $document) {
            $file = $files->get($document->file_id);
                $writer->addRow(WriterEntityFactory::createRowFromArray([
                    $document->id,
                    $document->period_since,
                    $document->period_to,
                    $document->updated_at,
                    $document->amount_reward,
                    $document->statusName($document->status),
                    $file->absoluteUrl(),
                ], null));

        }

        $writer->close();
    }

    /**
     * @param int $customerId
     * @param int $file_id
     * @param CustomerService $customerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDocument(int $customerId, CustomerService $customerService)
    {
        $this->validate(request(), [
            'file' => 'required|max:4000',
            'period_since' => 'required|date',
            'period_to' => 'required|date',
            'amount_reward' => 'required|numeric',
            'status' => 'required|numeric',
        ]);

        $documentFile = request('file');
        $documentDto = new CustomerDocumentDto();
        $documentDto->file_id = $documentFile['id'];
        $documentDto->period_since = request('period_since');
        $documentDto->period_to = request('period_to');
        $documentDto->amount_reward = request('amount_reward');
        $documentDto->status = request('status');

        $createdDocumentId = $customerService->createDocument($customerId, $documentDto);
        $createdDocument = $customerService->documents($customerId)->where('id', $createdDocumentId)->first();

        return response()->json([
            'id' => $createdDocumentId,
            'period_since' => $createdDocument->period_since,
            'period_to' => $createdDocument->period_to,
            'date' => $createdDocument->updated_at,
            'amount_reward' => $createdDocument->amount_reward,
            'statusId' => $createdDocument->status,
            'statusVerbal' => $documentDto->statusName($createdDocument->status),
        ]);
    }

    /**
     * @param int $customerId
     * @param int $document_id
     * @param CustomerService $customerService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteDocument(int $customerId, int $document_id, CustomerService $customerService)
    {
        $customerService->deleteDocument($customerId, $document_id);

        return response('', 204);
    }
}