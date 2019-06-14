<?php

namespace App\Http\Controllers;

use Common\Services\Auth;
use Illuminate\Http\Request;

class TestController extends Controller
{
    public function index(Request $request, Auth $auth)
    {
        return $this->render('Test', [
            'roles' => []//$auth->roles()
        ]);
    }
}