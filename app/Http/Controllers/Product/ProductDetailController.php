<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductDetailController extends Controller
{
    public function index(int $id, Request $request)
    {
        return $this->render('', [
        
        ]);
    }
}