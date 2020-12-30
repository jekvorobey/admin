<?php

namespace App\Http\Controllers\Marketing;

use App\Core\Menu;
use App\Http\Controllers\Controller;
use Greensight\Marketing\Builder\Certificate\DesignBuilder;
use Greensight\Marketing\Dto\Certificate\DesignSearchQuery;
use Greensight\Marketing\Services\Certificate\DesignService;
use Illuminate\Http\Request;

class CertificateDesignController extends Controller
{
    private function setActiveMenu()
    {
        Menu::setActiveUrl(route('certificate.index'));
    }

    public function index(Request $request)
    {
        $this->title = 'Справочник дизайнов';

        $c = resolve(CertificateController::class);

        return $this->render('Marketing/Certificate/Designs', $c->getTab('designs', $request));
    }

    public function createPage()
    {
        $this->title = 'Создание дизайна сертификатов';

        $this->setActiveMenu();
        return $this->render('Marketing/Certificate/Designs/Add', [
            'design' => [
                'status' => 1
            ]
        ]);
    }

    public function editPage($id)
    {
        $this->title = 'Редактирование дизайна сертификатов';
        $this->setActiveMenu();

        return $this->render('Marketing/Certificate/Designs/Edit', [
            'design' => (new DesignSearchQuery())
                ->id($id)
                ->prepare(resolve(DesignService::class), 'designs')
                ->get()->items->first() ?? [],
        ]);
    }

    public function delete($id, DesignService $designService)
    {
        $designService->delete($id);
        return response('', 204);
    }

    public function create(Request $request, DesignService $designService)
    {
        $builder = new DesignBuilder($request->all());
        $item = $designService->create($builder);

        return response()->json(['status' => 'ok', 'id' => $item['id'] ?? null]);
    }

    public function update($id, Request $request, DesignService $designService)
    {
        $builder = new DesignBuilder($request->all());
        $designService->update($id, $builder);

        return response()->json(['status' => 'ok']);
    }
}
