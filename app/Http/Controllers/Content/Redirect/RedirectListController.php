<?php

namespace App\Http\Controllers\Content\Redirect;

use Cms\Core\CmsException;
use Cms\Dto\RedirectDto;
use Cms\Services\RedirectService\RedirectService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RedirectListController extends Controller
{
    /**
     * @return mixed
     * @throws CmsException
     */
    public function index(Request $request, RedirectService $redirectService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $this->title = 'Редиректы (short url)';
        $query = $this->makeQuery($request);

        return $this->render('Content/RedirectList', [
            'iRedirects' => $this->loadItems($query, $redirectService),
            'iPager' => $redirectService->redirectsCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'host' => config('app.showcase_host'),
            ],
        ]);
    }

    /**
     * @throws CmsException
     */
    public function page(Request $request, RedirectService $redirectService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $query = $this->makeQuery($request);
        $data = [
            'redirects' => $this->loadItems($query, $redirectService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $redirectService->redirectsCount($query);
        }

        return response()->json($data);
    }

    /**
     * @return Collection|RedirectDto[]
     * @throws CmsException
     */
    protected function loadItems(RestQuery $query, RedirectService $redirectService): Collection
    {
        return $redirectService->redirects($query);
    }

    protected function makeQuery(Request $request): RestQuery
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $filter = $request->get('filter', []);

        if (isset($filter['from']) && $filter['from']) {
            $query->setFilter('from', $filter['from']);
        }

        if (isset($filter['to']) && $filter['to']) {
            $query->setFilter('to', $filter['to']);
        }

        if (isset($filter['product_id']) && $filter['product_id']) {
            $query->setFilter('product_id', $filter['product_id']);
        }

        $query->addSort('id', 'desc');

        return $query;
    }

    /**
     * @throws CmsException
     */
    protected function import(RedirectService $redirectService, Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $res = $redirectService->importRedirects($request->get('file_id'));
        return response()->json($res);
    }
}
