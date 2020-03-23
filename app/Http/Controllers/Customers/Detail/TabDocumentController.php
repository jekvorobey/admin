<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerDocumentDto;
use Greensight\Customer\Services\CustomerService\CustomerService;

/**
 * Class TabDocumentController
 * @package App\Http\Controllers\Customers\Detail
 */
class TabDocumentController extends Controller
{
    /**
     * @param int $id
     * @param CustomerService $customerService
     * @param FileService $fileService
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $id, CustomerService $customerService, FileService $fileService)
    {
        $documents = $customerService->documents($id);
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

                switch ($document->status)
                {
                    case 1:
                        $documentStatusVerbal = 'Сформирован';
                        break;
                    case 2:
                        $documentStatusVerbal = 'Согласован';
                        break;
                    case 3:
                        $documentStatusVerbal = 'Отклонен';
                        break;
                    default:
                        $documentStatusVerbal = 'N/A';
                }

                return [
                    'id' => $document->id,
                    'period_since' => $document->period_since,
                    'period_to' => $document->period_to,
                    'date' => $document->updated_at,
                    'amount_reward' => $document->amount_reward,
                    'status' => $document->status,
                    'status_verbal' =>$documentStatusVerbal,
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
        ]);
    }

    /**
     * @param int $id
     * @param int $file_id
     * @param CustomerService $customerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDocument(int $id, int $file_id, CustomerService $customerService)
    {
        $documentDto = new CustomerDocumentDto();
        $documentDto->file_id = $file_id;
        $id = $customerService->createDocument($id, $documentDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    /**
     * @param int $id
     * @param int $document_id
     * @param CustomerService $customerService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteDocument(int $id, int $document_id, CustomerService $customerService)
    {
        $customerService->deleteDocument($id, $document_id);

        return response('', 204);
    }
}