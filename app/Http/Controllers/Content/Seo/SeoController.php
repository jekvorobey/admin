<?php

namespace App\Http\Controllers\Content\Seo;

use App\Http\Requests\Content\Seo\SeoUpdateRequest;
use App\Services\Seo\SeoLogicService;
use Cms\Core\CmsException;
use Cms\Dto\SeoDto;
use Cms\Services\SeoService\SeoService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    private SeoLogicService $service;

    public function __construct()
    {
        $this->service = new SeoLogicService();
    }

    /**
     * @throws CmsException
     */
    public function listPage(Request $request, SeoService $seoService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $this->title = 'SEO';
        $query = $this->service->makeQuery($request);

        return $this->render('Content/SeoList', [
            'iSeo' => $this->service->getSeoList($query, $seoService),
            'iPager' => $seoService->seoCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'options' => [],
        ]);
    }

    public function page(Request $request, SeoService $seoService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        return response()->json($this->service->page($request, $seoService));
    }

    /**
     * @throws CmsException
     */
    public function edit(int $id, SeoService $seoService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $seo = $this->service->getSeo($id, $seoService);

        return $this->render('Content/SeoDetail', [
            'iSeo' => $seo,
            'options' => [],
        ]);
    }

    /**
     * @throws CmsException
     */
    public function update(SeoUpdateRequest $request, int $id, SeoService $seoService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $seoService->updateSeo($id, new SeoDto($request->validated()));

        return response()->noContent();
    }
}
