<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerDocumentDto;
use Greensight\Customer\Services\CustomerService\CustomerService;

class TabDocumentController extends Controller
{
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
                return [
                    'id' => $document->id,
                    'date' => $document->updated_at,
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
        ]);
    }

    public function createDocument(int $id, int $file_id, CustomerService $customerService)
    {
        $documentDto = new CustomerDocumentDto();
        $documentDto->file_id = $file_id;
        $id = $customerService->createDocument($id, $documentDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    public function deleteDocument(int $id, int $document_id, CustomerService $customerService)
    {
        $customerService->deleteDocument($id, $document_id);

        return response('', 204);
    }
}