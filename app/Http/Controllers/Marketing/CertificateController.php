<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Greensight\Marketing\Dto\Certificate\CardSearchQuery;
use Greensight\Marketing\Dto\Certificate\DesignSearchQuery;
use Greensight\Marketing\Dto\Certificate\NominalSearchQuery;
use Greensight\Marketing\Dto\Certificate\ReportSearchQuery;
use Greensight\Marketing\Dto\History\HistorySearchQuery;
use Greensight\Marketing\Dto\Option\OptionDto;
use Greensight\Marketing\Services\Certificate\DesignService;
use Greensight\Marketing\Services\Certificate\NominalService;
use Greensight\Marketing\Services\Certificate\ReportService;
use Greensight\Marketing\Services\Certificate\CardService;
use Greensight\Marketing\Services\HistoryService\HistoryService;
use Greensight\Marketing\Services\OptionService\OptionService;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function index(Request $request, ReportService $reportService)
    {
        $this->title = 'Подарочные сертификаты';
        $tab = $request->get('tab', 'nominals');

        return $this->render('Marketing/Certificate/Main', $this->getTab($tab, $request) + [
            'kpis' => $reportService->kpi()
        ]);
    }

    public function getTab(string $tab, Request $request)
    {
        switch ($tab)
        {
            case 'nominals': return $this->getTabNominals($request);
            case 'designs': return $this->getTabDesigns($request);
            case 'cards': return $this->getTabCards($request);
            case 'content': return $this->getTabContent($request);
            case 'reports': return $this->getTabReports($request);
            case 'logs': return $this->getTabLogs($request);
        }
    }

    private function getTabNominals(Request $request): array
    {
        return [
            'nominals' => (new NominalSearchQuery())
                ->include('designs')
                ->addSort('price', 'asc')
                ->pagination($request->get('page'), $request->get('per_page'))
                ->prepare(resolve(NominalService::class), 'nominals')
                ->get()
        ];

    }

    private function getTabDesigns(Request $request): array
    {
        return [
            'designs' => (new DesignSearchQuery())
                ->addSort('id')
                ->pagination($request->get('page'), $request->get('per_page'))
                ->prepare(resolve(DesignService::class), 'designs')
                ->get()
        ];
    }

    private function getTabContent(Request $request): array
    {
        $optionService = resolve(OptionService::class);
        return [
            'content' => $optionService->get(OptionDto::GIFT_CERTIFICATE_CONTENT) ?? []
        ];
    }

    private function getTabReports(Request $request): array
    {
        $filter = (array) $request->get('filter', []);

        return [
            'reports' => (new ReportSearchQuery())
                ->addSort('created_at', 'desc')
                ->pagination($request->get('page'), $request->get('per_page'))
                ->createdAt($filter['date'] ?? null)
                ->creatorId($filter['creator_id'] ?? null)
                ->prepare(resolve(ReportService::class), 'reports')
                ->get()
        ];
    }

    private function getTabLogs(Request $request): array
    {
        $filter = (array) $request->get('filter', []);

        return [
            'logs' => (new HistorySearchQuery())
                ->tag('gift_card')
                ->include('entity', 'user.short_name')
                ->addSort('id', 'desc')
                ->pagination($request->get('page'), $request->get('per_page'))
                ->id($filter['id'] ?? null)
                ->createdAt($filter['date'] ?? null)
                ->entityType($filter['entity_type'] ?? null)
                ->entityId($filter['entity_id'] ?? null)
                ->userId($filter['user_id'] ?? null)
                ->prepare(resolve(HistoryService::class), 'history')
                ->get()
        ];
    }

    private function getTabCards(Request $request): array
    {
        $filter = (array) $request->get('filter', []);

        return [
            'cards' => (new CardSearchQuery())
                ->processed()
                ->include('order', 'customer.full_name', 'recipient.full_name')
                ->addSort('id', 'desc')
                ->pagination($request->get('page'), $request->get('per_page'))
                ->id($filter['id'] ?? null)
                ->createdAt($filter['date'] ?? null)
                ->pin($filter['pin'] ?? null)
                ->recipientId($filter['recipient_id'] ?? null)
                ->customerId($filter['customer_id'] ?? null)
                ->prepare(resolve(CardService::class), 'cards')
                ->get()
        ];
    }

    public function storeContent(Request $request, OptionService $optionService)
    {
        $optionService->put(OptionDto::GIFT_CERTIFICATE_CONTENT, $request->json()->all());

        return response('', 204);
    }
}
