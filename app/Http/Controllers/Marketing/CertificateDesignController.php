<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\Certificate\CertificateDesignDto;
use Pim\Services\CertificateService\CertificateService;

class CertificateDesignController extends Controller
{
    private function setActiveMenu()
    {
        Menu::setActiveUrl(route('certificate.index'));
    }

    private function service(): CertificateService
    {
        return resolve(CertificateService::class);
    }

    public function index(Request $request)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Справочник дизайнов';
        $this->setActiveMenu();

        $c = resolve(CertificateController::class);

        return $this->render('Marketing/Certificate/Designs', $c->getTab('designs', $request));
    }

    public function createPage()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Создание дизайна сертификатов';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Designs/Add', [
            'design' => [
                'is_active' => 1,
            ],
        ]);
    }

    public function editPage($id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Редактирование дизайна сертификатов';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Designs/Edit', [
            'design' => $this->service()->designQuery()->withFile()->id($id)->designs()->first(),
        ]);
    }

    public function delete($id)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->deleteDesign($id);

        return response('', 204);
    }

    public function create(Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $id = $this->service()->createDesign(new CertificateDesignDto($request->all()));

        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function update($id, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->updateDesign($id, new CertificateDesignDto($request->all()));

        return response()->json(['status' => 'ok']);
    }
}
