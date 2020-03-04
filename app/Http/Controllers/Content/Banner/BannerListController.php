<?php

namespace App\Http\Controllers\Content\Banner;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\BannerDto;
use Cms\Dto\BannerTypeDto;
use Cms\Services\BannerService\BannerService;
use Cms\Services\BannerTypeService\BannerTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BannerListController extends Controller
{
    public function listPage(
        Request $request,
        BannerService $bannerService,
        BannerTypeService $bannerTypeService,
        FileService $fileService
    ) {
        $this->title = 'Баннеры';
        $query = $this->makeQuery($request);

        return $this->render('Content/BannerList', [
            'iBanners' => $this->loadItems($query, $bannerService, $fileService),
            'iPager' => $bannerService->bannersCount($query),
            'iCurrentPage' => $request->get('page', 1),
            'iFilter' => $request->get('filter', []),
            'options' => [
                'types' => $this->loadTypes($bannerTypeService),
            ]
        ]);
    }

    public function page(
        Request $request,
        BannerService $bannerService,
        FileService $fileService
    ) {
        $query = $this->makeQuery($request);
        $data = [
            'banners' => $this->loadItems($query, $bannerService, $fileService),
        ];
        if (1 == $request->get('page', 1)) {
            $data['pager'] = $bannerService->bannersCount($query);
        }

        return response()->json($data);
    }

    /**
     * @param Request $request
     * @return RestQuery
     */
    protected function makeQuery(Request $request)
    {
        $query = new RestQuery();
        $page = $request->get('page', 1);
        $query->pageNumber($page, 10);

        $query->addFields('type', '*');

        $filter = $request->get('filter', []);

        if (isset($filter['id']) && $filter['id']) {
            $query->setFilter('id', $filter['id']);
        }

        if (isset($filter['type'])) {
            $query->setFilter('type_id', $filter['type']);
        }

        return $query;
    }

    /**
     * @param RestQuery $query
     * @param BannerService $bannerService ,
     * @param FileService $fileService
     * @return BannerDto[]|Collection
     * @throws CmsException
     */
    protected function loadItems(
        RestQuery $query,
        BannerService $bannerService,
        FileService $fileService
    ) {
        $banners = $bannerService->banners($query);
        $imagesIds = $banners->pluck('desktop_image_id')->all();
        $images = $fileService->getFiles($imagesIds)->keyBy('id');

        // Получается дополненный BannerDto
        return $banners->map(function (BannerDto $banner) use ($images) {
            /** @var FileDto $photo */
            $image = $images->get($banner['desktop_image_id']);
            $banner['desktop_image'] = $image ? $image->absoluteUrl() : null;
            return $banner;
        });
    }

    /**
     * @param BannerTypeService $bannerTypeService
     * @return BannerTypeDto[]|Collection
     * @throws CmsException
     */
    protected function loadTypes(BannerTypeService $bannerTypeService)
    {
        return $bannerTypeService->bannerTypes($bannerTypeService->newQuery());
    }
}
