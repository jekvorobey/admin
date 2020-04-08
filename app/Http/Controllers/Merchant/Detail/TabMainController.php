<?php


namespace App\Http\Controllers\Merchant\Detail;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use MerchantManagement\Dto\MerchantDocumentDto;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabMainController extends Controller
{
    public function load(int $id, MerchantService $merchantService, FileService $fileService)
    {
        $documents = $merchantService->documents($id);
        $files = collect();
        if ($documents->isNotEmpty()) {
            $files = $fileService->getFiles($documents->pluck('file_id')->all())->keyBy('id');
        }

        return response()->json([
            'documents' => $documents->map(function (MerchantDocumentDto $document) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($document->file_id);

                return [
                    'file_id' => $document->file_id,
                    'name' => $file->original_name,
                ];
            }),
        ]);
    }

    public function createDocument(int $id, MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'file_id' => 'required'
        ]);

        $merchantService->createDocument($id, $data['file_id']);

        return response('', 201);
    }

    public function deleteDocument(int $id, MerchantService $merchantService)
    {
        $data = $this->validate(request(), [
            'file_id' => 'required'
        ]);

        $merchantService->deleteDocument($id, $data['file_id']);

        return response('', 204);
    }
}