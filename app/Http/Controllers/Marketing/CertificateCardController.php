<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\Certificate\CertificateRequestDto;
use Pim\Services\CertificateService\CertificateService;

class CertificateCardController extends Controller
{
    private function setActiveMenu(): void
    {
        Menu::setActiveUrl(route('certificate.index'));
    }

    private function service(): CertificateService
    {
        return resolve(CertificateService::class);
    }

    public function editPage($id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Редактирование ПС';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Main/Edit', [
            'card' => $this->service()->requestQuery()->id($id)->requests()->first() ?? [],
        ]);
    }

    public function update($id, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->updateRequest($id, new CertificateRequestDto($request->all()));

        return response()->json(['status' => 'ok']);
    }

    public function updateExpireAt($id, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->updateExpireAt($id, (int) $request->get('days'));

        return response()->json(['status' => 'ok']);
    }

    public function activate(Request $request): JsonResponse
    {
        $result = $this->service()->activate((string) $request->get('pin'), (int) $request->get('customer_id'));
        $response = ['status' => 'ok'];
        $statusCode = 200;
        if (!$result->success) {
            $response = ['message' => $result->message];
            $statusCode = 400;
        }
        return response()->json($response, $statusCode);
    }

    public function deactivate($id, RequestInitiator $user): JsonResponse
    {
        $result = $this->service()->deactivate((int) $id, $user->userId());
        $response = ['status' => 'ok'];
        $statusCode = 200;
        if (!$result->success) {
            $response = ['message' => $result->message];
            $statusCode = 400;
        }
        return response()->json($response, $statusCode);
    }

    public function sendNotification($id): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->sendNotification($id);

        return response()->json(['status' => 'ok']);
    }
}
