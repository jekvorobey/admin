<?php

namespace App\Http\Controllers;

use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
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
     * @return RedirectResponse|Redirector
     */
    public function home()
    {
        return $this->render('Index', []);
    }

    /**
     * Вывести форму авторизации
     * @return mixed
     */
    public function login(RequestInitiator $user)
    {
        return !$user->userId() ? $this->render('Login', []) : $this->home();
    }

    /**
     * Вывести форму смены пароля для нового пользователя
     * @return mixed
     * @throws AuthorizationException
     */
    public function changePassword(Request $request)
    {
        $userId = $request->route('id');
        $userHash = $request->route('hash');

        $user = app(UserService::class)->users((new RestQuery())->setFilter('id', $userId))->first();
        if (!$user) {
            throw new AuthorizationException();
        }
        if (!hash_equals((string) $userHash, sha1($user->email . $user->full_name))) {
            throw new AuthorizationException();
        }

        return $this->render('PasswordConfirm', ['id' => $userId]);
    }

    /**
     * Выполнить авторизацию
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
     */
    public function logoutAjax(RequestInitiator $user): JsonResponse
    {
        $user->logout();

        return response()->json([]);
    }

    public function uploadFile(Request $request, FileService $fileService): JsonResponse
    {
        $destination = request('destination');
        /** @var UploadedFile $file */
        $file = $request->file('file');
        if (!$file) {
            throw new BadRequestHttpException();
        }

        $id = $fileService->uploadFile($destination, $file->getClientOriginalName(), $file->path());
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
