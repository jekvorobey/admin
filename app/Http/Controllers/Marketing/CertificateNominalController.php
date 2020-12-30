<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\Marketing\Builder\Certificate\NominalBuilder;
use Greensight\Marketing\Dto\Certificate\DesignSearchQuery;
use Greensight\Marketing\Dto\Certificate\NominalSearchQuery;
use Greensight\Marketing\Services\Certificate\DesignService;
use Greensight\Marketing\Services\Certificate\NominalService;
use Illuminate\Http\Request;

class CertificateNominalController extends Controller
{
    private function setActiveMenu()
    {
        Menu::setActiveUrl(route('certificate.index'));
    }

    public function index(Request $request)
    {
        $this->title = 'Конструктор сертификатов (номиналы)';

        $c = resolve(CertificateController::class);

        return $this->render('Marketing/Certificate/Nominals', $c->getTab('nominals', $request));
    }

    public function createPage()
    {
        $this->title = 'Создание номинала';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Nominals/Add', [
            'nominal' => [
                'status' => 1
            ],
            'all_designs' => $this->designDictionary()
        ]);
    }

    public function editPage($id)
    {
        $this->title = 'Редактирование номинала';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Nominals/Edit', [
            'nominal' => (new NominalSearchQuery())
                    ->id($id)
                    ->include('designs')
                    ->prepare(resolve(NominalService::class), 'nominals')
                    ->get()->items->first() ?? [],
            'all_designs' => $this->designDictionary()
        ]);
    }

    private function designDictionary()
    {
        return (new DesignSearchQuery())
            ->addSort('id')
            ->pagination(1, 300)
            ->prepare(resolve(DesignService::class), 'designs')
            ->get()->items;
    }

    public function delete($id, NominalService $nominalService)
    {
        $nominalService->delete($id);
        return response('', 204);
    }

    public function create(Request $request, NominalService $nominalService, RequestInitiator $requestInitiator)
    {
        $data = $request->all();
        $data['creator_id'] = $requestInitiator->userId();

        $builder = new NominalBuilder($data);
        $item = $nominalService->create($builder);

        return response()->json(['status' => 'ok', 'id' => $item['id'] ?? null]);
    }

    public function update($id, Request $request, NominalService $nominalService)
    {
        $builder = new NominalBuilder($request->all());
        $nominalService->update($id, $builder);

        return response()->json(['status' => 'ok']);
    }
}
