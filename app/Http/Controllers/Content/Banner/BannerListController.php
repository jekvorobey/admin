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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
            ],
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
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $bannerService->bannersCount($query);
        }

        return response()->json($data);
    }

    public function widgetBanners(
        BannerService $bannerService,
        BannerTypeService $bannerTypeService
    ) {
        $type = $this->loadTypes($bannerTypeService)
            ->keyBy('code')
            ->get(BannerTypeDto::WIDGET_CODE);

        if (is_null($type)) {
            throw new NotFoundHttpException('type not found');
        }

        $query = $bannerService->newQuery()
            ->setFilter('type_id', $type['id']);

        $banners = $bannerService->banners($query);

        return response()->json($banners);
    }

    public function productGroupBanners(
        BannerService $bannerService,
        BannerTypeService $bannerTypeService
    ) {
        $type = $this->loadTypes($bannerTypeService)
            ->keyBy('code')
            ->get(BannerTypeDto::PRODUCT_GROUP_CODE);

        if (is_null($type)) {
            throw new NotFoundHttpException('type not found');
        }

        $query = $bannerService->newQuery()
            ->setFilter('type_id', $type['id']);

        $banners = $bannerService->banners($query);

        return response()->json($banners);
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

        return $query->addSort('id', 'desc');
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
            /** @var FileDto $image */
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
