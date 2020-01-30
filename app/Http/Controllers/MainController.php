<?php

namespace App\Http\Controllers;

use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
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
     * @param Request $request
     * @param RequestInitiator $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function loginAjax(Request $request, RequestInitiator $user): JsonResponse
    {
        $email = $request->get('email');
        $password = $request->get('password');

        $data = $user->loginByPassword($email, $password, Front::FRONT_ADMIN);

        return response()->json(['status' => $data ? 'ok' : 'fail']);
    }

    /**
     * Выполнить выход
     * @param RequestInitiator $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function logoutAjax(RequestInitiator $user): JsonResponse
    {
        $user->logout();
        return response()->json([]);
    }

    public function uploadFile(Request $request, FileService $fileService)
    {
        /** @var UploadedFile $file */
        $file = $request->file('file');
        if (!$file) {
            throw new BadRequestHttpException();
        }

        $id = $fileService->uploadFile('catalog', $file->getClientOriginalName(), $file->path());
        $fileDto = $fileService->getFiles([$id])->first();
        if (!$fileDto) {
            throw new HttpException(500);
        }

        return response()->json([
            'id' => $id,
            'name' => $fileDto->original_name,
            'url' => $fileDto->absoluteUrl(),
        ]);
    }
}