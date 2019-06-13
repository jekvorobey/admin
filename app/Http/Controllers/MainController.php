<?php

namespace App\Http\Controllers;

use Common\Auth\TokenStore;
use Common\Services\Auth;
use Illuminate\Http\Request;

class MainController extends Controller
{
    public function home()
    {
        $this->breadcrumbs = 'home';
        return $this->render('Index', [
            'message' => 'Banana!'
        ]);
    }

    public function login()
    {
        $this->breadcrumbs = 'login';
        return $this->render('Login', []);
    }

    public function loginAjax(Request $request, Auth $auth, TokenStore $store)
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $data = $auth->token($email, $password);
        if ($data) {
            ['token' => $token, 'refresh' => $refresh] = $data;
            $store->saveToken($token);
            $store->saveRefreshToken($refresh);
        }
        return response()->json([]);
    }

    public function logoutAjax(TokenStore $store)
    {
        $store->saveToken('');
        $store->saveRefreshToken('');
        return response()->json([]);
    }
}