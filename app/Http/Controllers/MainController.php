<?php

namespace App\Http\Controllers;

use Greensight\CommonMsa\Services\AuthService\AuthService;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Greensight\Filestorage\Services\FileService\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class MainController
 * @package App\Http\Controllers
 */
class MainController extends Controller
{
    /**
     * Вывести домашнюю страницу
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function home()
    {
        $this->breadcrumbs = 'home';
        
        return $this->render('Index', []);
    }
    
    /**
     * Вывести форму авторизации
     * @return mixed
     */
    public function login()
    {
        return $this->render('Login', []);
    }
    
    /**
     * Выполнить авторизацию
     * @param  Request  $request
     * @param  AuthService  $auth
     * @param  TokenStore  $store
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function loginAjax(Request $request, AuthService $auth, TokenStore $store): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');
        $data = $auth->token(1, $email, $password);
        if ($data) {
            ['token' => $token, 'refresh' => $refresh] = $data;
            $store->saveToken($token);
            $store->saveRefreshToken($refresh);
        }
        return response()->json(['status' => $data ? 'ok' : 'fail']);
    }
    
    /**
     * Выполнить выход
     * @param  TokenStore  $store
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAjax(TokenStore $store): JsonResponse
    {
        $store->saveToken('');
        $store->saveRefreshToken('');
        return response()->json([]);
    }

    public function uploadFile(Request $request, FileService $fileService)
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        if (!$file) {
            throw new BadRequestHttpException();
        }

        $id = $fileService->uploadFile('catalog', $file->getBasename(), $file->path());
        $fileDto = $fileService->getFiles([$id])->first();
        if (!$fileDto) {
            throw new HttpException(500);
        }

        return response()->json([
            'id' => $id,
            'url' => $fileDto->absoluteUrl(),
        ]);
    }
}