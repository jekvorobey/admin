<?php

namespace App\Http\Controllers\Content\Banner;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\BannerTypeDto;
use Cms\Services\BannerService\BannerService;
use Cms\Services\BannerTypeService\BannerTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Support\Collection;

class BannerDetailController extends Controller
{
    /**
     * @param int $id
     * @param BannerService $bannerService
     * @param BannerTypeService $bannerTypeService
     * @param FileService $fileService
     * @return mixed
     * @throws CmsException
     */
    public function updatePage(
        $id,
        BannerService $bannerService,
        BannerTypeService $bannerTypeService,
        FileService $fileService
    ) {
        $banner = $this->getBanner($id, $bannerService);
        $bannerImages = $this->getBannerImages([
            $banner['desktop_image_id'],
            $banner['tablet_image_id'],
            $banner['mobile_image_id']
        ], $fileService);
        $bannerTypes = $this->getBannerTypes($bannerTypeService);

        return $this->render('Content/BannerDetail', [
            'iBanner' => $banner,
            'iBannerTypes' => $bannerTypes,
            'iBannerImages' => $bannerImages,
            'options' => [],
        ]);
    }

    /**
     * @param int $id
     * @param BannerService $bannerService
     * @param BannerTypeService $bannerTypeService
     * @return mixed
     * @throws CmsException
     */
    public function createPage(
        BannerService $bannerService,
        BannerTypeService $bannerTypeService
    ) {
        $bannerTypes = $this->getBannerTypes($bannerTypeService);

        return $this->render('Content/BannerDetail', [
            'iBanner' => [],
            'iBannerTypes' => $bannerTypes,
            'iBannerImages' => [],
            'options' => [],
        ]);
    }

    public function delete(int $id, BannerService $bannerService)
    {
        $bannerService->deleteBanner($id);

        return response()->json([], 204);
    }

    private function getBanner(
        int $id,
        BannerService $bannerService
    ) {
        $query = $bannerService->newQuery();
        $query->setFilter('id', $id);
        $query->include('button');
        $query->include('type');

        return $bannerService->banners($query)->first();
    }

    /**
     * @param BannerTypeService $bannerTypeService
     * @return BannerTypeDto[]|Collection
     * @throws CmsException
     */
    private function getBannerTypes(BannerTypeService $bannerTypeService)
    {
        return $bannerTypeService->bannerTypes($bannerTypeService->newQuery());
    }

    /**
     * @param array $ids
     * @param FileService $fileService
     * @return Collection|FileDto[]
     */
    private function getBannerImages(array $ids, FileService $fileService)
    {
        return $fileService
            ->getFiles($ids)
            ->transform(function ($file) {
                /** @var FileDto $file */
                $file['url'] = $file->absoluteUrl();

                return $file;
            })
            ->keyBy('id');
    }
}
