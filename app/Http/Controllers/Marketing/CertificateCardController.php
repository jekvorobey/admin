<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\Certificate\CertificateRequestDto;
use Pim\Services\CertificateService\CertificateService;

class CertificateCardController extends Controller
{

    private function setActiveMenu()
    {
        Menu::setActiveUrl(route('certificate.index'));
    }

    private function service(): CertificateService
    {
        return resolve(CertificateService::class);
    }

    public function editPage($id)
    {
        $this->title = 'Редактирование ПС';
        $this->setActiveMenu();
        return $this->render('Marketing/Certificate/Main/Edit', [
            'card' => $this->service()->requestQuery()->id($id)->requests()->first() ?? [],
        ]);
    }

    public function update($id, Request $request): JsonResponse
    {
        $this->service()->updateRequest($id, new CertificateRequestDto($request->all()));
        return response()->json(['status' => 'ok']);
    }

    public function updateExpireAt($id, Request $request): JsonResponse
    {
        $this->service()->updateExpireAt($id, (int)$request->get('days'));
        return response()->json(['status' => 'ok']);
    }

}