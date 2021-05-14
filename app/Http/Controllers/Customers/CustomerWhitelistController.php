<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\Request;

class CustomerWhitelistController extends Controller
{
    protected function index()
    {
        $this->title = 'Вайтлист';

        return $this->render('Customer/Whitelist', []);
    }

    protected function import(Request $request, CustomerService $customerService)
    {
        $request->validate([
            'file_id' => 'required|integer',
        ]);

        $customerService->importWhitelist($request->file_id);

        return response()->noContent();
    }
}
