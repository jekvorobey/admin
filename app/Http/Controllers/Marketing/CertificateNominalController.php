<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Pim\Dto\Certificate\CertificateNominalDto;
use Pim\Services\CertificateService\CertificateService;

class CertificateNominalController extends Controller
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

        $this->title = 'Конструктор сертификатов (номиналы)';
        $this->setActiveMenu();

        $c = resolve(CertificateController::class);

        return $this->render('Marketing/Certificate/Nominals', $c->getTab('nominals', $request));
    }

    public function createPage()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Создание номинала';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Nominals/Add', [
            'nominal' => [
                'is_active' => 1,
            ],
            'all_designs' => $this->designDictionary(),
        ]);
    }

    public function editPage($id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Редактирование номинала';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Nominals/Edit', [
            'nominal' => $this->service()->nominalQuery()->withDesigns()->id($id)->nominals()->first() ?? [],
            'all_designs' => $this->designDictionary(),
        ]);
    }

    private function designDictionary(): Collection
    {
        return $this->service()->designQuery()->designs();
    }

    public function delete($id): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->deleteNominal($id);

        return response('', 204);
    }

    public function create(Request $request, RequestInitiator $requestInitiator): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $data = $request->all() + ['creator_id' => $requestInitiator->userId()];

        $id = $this->service()->createNominal(new CertificateNominalDto($data));

        return response()->json(['status' => 'ok', 'id' => $id]);
    }

    public function update($id, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->service()->updateNominal($id, new CertificateNominalDto($request->all()));

        return response()->json(['status' => 'ok']);
    }
}
