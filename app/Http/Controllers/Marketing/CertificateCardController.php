<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\Certificate\CertificateNominalDto;
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
        return $this->render('Marketing/Certificate/Card/Edit', [
            'card' => $this->service()->certificateQuery()->id($id)->certificates()->first() ?? [],
        ]);
    }

}