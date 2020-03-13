<?php

namespace App\Http\Controllers\Content\Banner;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\BannerButtonLocationDto;
use Cms\Dto\BannerButtonTypeDto;
use Cms\Dto\BannerDto;
use Cms\Dto\BannerTypeDto;
use Cms\Services\BannerService\BannerService;
use Cms\Services\BannerTypeService\BannerTypeService;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;

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
        $bannerButtonTypes = $this->getBannerButtonTypes();
        $bannerButtonLocations = $this->getBannerButtonLocations();

        return $this->render('Content/BannerDetail', [
            'iBanner' => $banner,
            'iBannerTypes' => $bannerTypes,
            'iBannerButtonTypes' => $bannerButtonTypes,
            'iBannerButtonLocations' => $bannerButtonLocations,
            'iBannerImages' => $bannerImages,
            'options' => [],
        ]);
    }

    /**
     * @param BannerTypeService $bannerTypeService
     * @return mixed
     * @throws CmsException
     */
    public function createPage(
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

    /**
     * @param Request $request
     * @param BannerService $bannerService
     * @return \Illuminate\Http\JsonResponse
     * @throws CmsException
     */
    public function create(Request $request, BannerService $bannerService)
    {
        $validatedData = $request->validate([
            'name' => 'string|required',
            'active' => 'boolean|required',
            'desktop_image_id' => 'integer|required',
            'tablet_image_id' => 'integer|nullable',
            'mobile_image_id' => 'integer|nullable',
            'type_id' => 'integer|required',
            'button_id' => 'integer|nullable',
            'button' => 'array',
        ]);

        $bannerService->createBanner(new BannerDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @param int $id
     * @param Request $request
     * @param BannerService $bannerService
     * @return \Illuminate\Http\JsonResponse
     * @throws CmsException
     */
    public function update(int $id, Request $request, BannerService $bannerService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'active' => 'boolean|required',
            'desktop_image_id' => 'integer|required',
            'tablet_image_id' => 'integer|nullable',
            'mobile_image_id' => 'integer|nullable',
            'type_id' => 'integer|required',
            'button_id' => 'integer|nullable',
            'button' => 'array',
        ]);

        $validatedData['id'] = $id;

        $bannerService->updateBanner($validatedData['id'], new BannerDto($validatedData));

        return response()->json([], 204);
    }

    public function delete(int $id, BannerService $bannerService)
    {
        $bannerService->deleteBanner($id);

        return response()->json([], 204);
    }

    /**
     * @param int $id
     * @param BannerService $bannerService
     * @return BannerDto|null
     * @throws CmsException
     */
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

    /**
     * @return BannerButtonLocationDto[]|Collection
     */
    private function getBannerButtonLocations()
    {
        return new Collection(BannerButtonLocationDto::all());
    }

    /**
     * @return BannerButtonTypeDto[]|Collection
     */
    private function getBannerButtonTypes()
    {
        return new Collection(BannerButtonTypeDto::all());
    }
}
