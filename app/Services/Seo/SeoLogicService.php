<?php

namespace App\Services\Seo;

use Cms\Core\CmsException;
use Cms\Dto\SeoDto;
use Cms\Services\SeoService\SeoService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SeoLogicService
{
    private const PER_PAGE = 10;

    /**
     * @throws CmsException
     */
    public function page(Request $request, SeoService $seoService): array
    {
        $query = $this->makeQuery($request);
        $data = [
            'landings' => $this->getSeoList($query, $seoService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $seoService->seoCount($query);
        }

        return $data;
    }

    public function makeQuery(Request $request): RestQuery
    {
        $query = new RestQuery();

        $page = $request->get('page', 1);
        $query->pageNumber($page, self::PER_PAGE);

        return $query;
    }

    /**
     * @return Collection|SeoDto[]
     * @throws CmsException
     */
    public function getSeoList(RestQuery $query, SeoService $seoService): Collection
    {
        return $seoService->seo($query);
    }

    /**
     * @throws CmsException
     */
    public function getSeo(int $id, SeoService $seoService)
    {
        $query = $seoService->newQuery()->setFilter('id', $id);

        return $this->getSeoList($query, $seoService)->first();
    }
}
