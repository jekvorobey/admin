<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\Request;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerWhitelistController extends Controller
{
    protected function index()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $this->title = 'Вайтлист';

        return $this->render('Customer/Whitelist', []);
    }

    protected function import(Request $request, CustomerService $customerService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $request->validate([
            'file_id' => 'required|integer',
        ]);

        $customerService->importWhitelist($request->file_id);

        return response()->noContent();
    }

    protected function export(CustomerService $customerService, FileService $fileService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $fileId = $customerService->exportWhitelist();
        /** @var FileDto $file */
        $file = $fileService->getFiles([$fileId])->first();

        return response()->streamDownload(function () use ($file) {
            echo file_get_contents($file->absoluteUrl());
        }, $file->original_name);
    }
}
