<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Services\CertificateService\CertificateService;

class CertificateController extends Controller
{
    const PER_PAGE = 15;

    private function service(): CertificateService
    {
        return resolve(CertificateService::class);
    }

    public function index(Request $request)
    {
//        $query = $this->service()->transactionItemQuery()
//            ->withUser()
//            ->type(1,2)
//
////            ->id(1, 2)
////            ->withNominal()
////            ->withDesign(true)
////            ->withCustomer()
////            ->withCertificates(true)
//        ;
//
//        dump($query->transactions());
//        dump($query->transactionsCount());
//
//        dd('end');


        $this->title = 'Подарочные сертификаты';
        $tab = $request->get('tab', 'nominals');

        return $this->render('Marketing/Certificate/Main', $this->getTab($tab, $request) + [
            'kpis' => $this->service()->kpi()
        ]);
    }

    public function getTab(string $tab, Request $request): array
    {
        switch ($tab)
        {
            case 'nominals': return $this->getTabNominals($request);
            case 'designs': return $this->getTabDesigns($request);
            case 'cards': return $this->getTabCards($request);
            case 'content': return $this->getTabContent($request);
            case 'reports': return $this->getTabReports($request);
            case 'logs': return $this->getTabLogs($request);
            default: return [];
        }
    }

    private function getTabNominals(Request $request): array
    {
        $page = (int) $request->get('page', 1);
        $query = $this->service()->nominalQuery()->withDesigns()->pageNumber($page, self::PER_PAGE);

        return [
            'nominals' => [
                'page' => $page,
                'pager' => $query->nominalsCount(),
                'items' => $query->nominals(),
            ]
        ];
    }

    private function getTabDesigns(Request $request): array
    {
        $page = (int) $request->get('page', 1);
        $query = $this->service()->designQuery()->withFile()->pageNumber($page, self::PER_PAGE);

        return [
            'designs' => [
                'page' => $page,
                'pager' => $query->designsCount(),
                'items' => $query->designs(),
            ]
        ];
    }

    private function getTabContent(Request $request): array
    {
        return [
            'content' => $this->service()->options()
        ];
    }

    private function getTabReports(Request $request): array
    {
        $page = (int) $request->get('page', 1);
        $query = $this->service()->reportQuery()
            ->withCreator()
            ->addSort('id', 'desc')
            ->pageNumber($page, self::PER_PAGE);

        return [
            'reports' => [
                'page' => $page,
                'pager' => $query->reportsCount(),
                'items' => $query->reports(),
            ]
        ];
    }

    private function getTabLogs(Request $request): array
    {
        $filter = (array) $request->get('filter', []);

        $page = (int) $request->get('page', 1);
        $query = $this->service()->transactionItemQuery()
            ->withTransaction(true)
            ->withCertificate(true)
            ->createdAt($filter['date_from'] ?? null, $filter['date_to'] ?? null)
            ->certificateId(preg_split('/[^\d]+/', $filter['certificate_id'] ?? ''))
            ->transactionId(preg_split('/[^\d]+/', $filter['transaction_id'] ?? ''))
            ->addSort('id', 'desc')
            ->pageNumber($page, self::PER_PAGE);

        return [
            'logs' => [
                'page' => $page,
                'pager' => $query->transactionItemsCount(),
                'items' => $query->transactionItems(),
            ]
        ];
    }

    private function getTabCards(Request $request): array
    {
        $page = (int) $request->get('page', 1);
        $query = $this->service()->certificateQuery()
            ->withRequestCustomer()
            ->withRecipient()
            ->withOrderPayTransactions()
            ->addSort('id', 'desc')
            ->pageNumber($page, self::PER_PAGE);

        $filter = (array) $request->get('filter');

        foreach ($filter as $key => $val)
        {
            switch ($key)
            {
                case 'customer_or_recipient_id': $query->customerOrRecipientId($val); break;
                case 'customer_id': $query->customerId($val); break;
                case 'recipient_id': $query->recipientId($val); break;
            }
        }

        return [
            'cards' => [
                'page' => $page,
                'pager' => $query->certificatesCount(),
                'items' => $query->certificates(),
            ]
        ];
    }

    public function storeContent(Request $request)
    {
        $this->service()->updateOptions($request->all());

        return response('', 204);
    }
}
