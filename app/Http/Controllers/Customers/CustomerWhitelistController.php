<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerWhitelistController extends Controller
{
    protected function index()
    {
        $this->title = 'Вайтлист';

        return $this->render('Customer/Whitelist', []);
    }

    protected function import(Request $request, CustomerService $customerService): Response
    {
        $request->validate([
            'file_id' => 'required|integer',
        ]);

        $customerService->importWhitelist($request->file_id);

        return response()->noContent();
    }

    protected function export(CustomerService $customerService, FileService $fileService): StreamedResponse
    {
        $fileId = $customerService->exportWhitelist();
        /** @var FileDto $file */
        $file = $fileService->getFiles([$fileId])->first();

        return response()->streamDownload(function () use ($file) {
            echo file_get_contents($file->absoluteUrl());
        }, $file->original_name);
    }
}
