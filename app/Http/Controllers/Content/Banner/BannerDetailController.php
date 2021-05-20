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
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BannerDetailController extends Controller
{
    /**
     * @param int $id
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
            $banner['mobile_image_id'],
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
     * @return mixed
     * @throws CmsException
     */
    public function createPage(
        BannerTypeService $bannerTypeService
    ) {
        $bannerTypes = $this->getBannerTypes($bannerTypeService);
        $bannerButtonTypes = $this->getBannerButtonTypes();
        $bannerButtonLocations = $this->getBannerButtonLocations();

        return $this->render('Content/BannerDetail', [
            'iBanner' => [],
            'iBannerTypes' => $bannerTypes,
            'iBannerButtonTypes' => $bannerButtonTypes,
            'iBannerButtonLocations' => $bannerButtonLocations,
            'iBannerImages' => [],
            'options' => [],
        ]);
    }

    /**
     * @return mixed
     * @throws CmsException
     */
    public function bannerInitialDate(
        Request $request,
        BannerService $bannerService,
        BannerTypeService $bannerTypeService,
        FileService $fileService
    ) {
        $validatedData = $request->validate([
            'id' => 'integer|nullable',
        ]);
        $id = $validatedData['id'] ?? null;

        if (!is_null($id)) {
            $banner = $this->getBanner($id, $bannerService);
            $bannerImages = $this->getBannerImages([
                $banner['desktop_image_id'],
                $banner['tablet_image_id'],
                $banner['mobile_image_id'],
            ], $fileService);
        } else {
            $banner = null;
            $bannerImages = [];
        }
        $bannerTypes = $this->getBannerTypes($bannerTypeService);
        $bannerButtonTypes = $this->getBannerButtonTypes();
        $bannerButtonLocations = $this->getBannerButtonLocations();

        return response()->json([
            'banner' => $banner,
            'bannerTypes' => $bannerTypes,
            'bannerButtonTypes' => $bannerButtonTypes,
            'bannerButtonLocations' => $bannerButtonLocations,
            'bannerImages' => $bannerImages,
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws CmsException
     */
    public function create(Request $request, BannerService $bannerService)
    {
        $validatedData = $request->validate([
            'name' => 'string|required',
            'url' => 'string|required',
            'active' => 'boolean|required',
            'desktop_image_id' => 'integer|required',
            'tablet_image_id' => 'integer|nullable',
            'mobile_image_id' => 'integer|nullable',
            'type_id' => 'integer|required',
            'button_id' => 'integer|nullable',
            'button' => 'array|nullable',
        ]);

        $id = $bannerService->createBanner(new BannerDto($validatedData));

        return response()->json($id, 200);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws CmsException
     */
    public function update(int $id, Request $request, BannerService $bannerService)
    {
        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'url' => 'string|required',
            'active' => 'boolean|required',
            'desktop_image_id' => 'integer|required',
            'tablet_image_id' => 'integer|nullable',
            'mobile_image_id' => 'integer|nullable',
            'type_id' => 'integer|required',
            'button_id' => 'integer|nullable',
            'button' => 'array|nullable',
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
     * @return BannerTypeDto[]|Collection
     * @throws CmsException
     */
    private function getBannerTypes(BannerTypeService $bannerTypeService)
    {
        return $bannerTypeService->bannerTypes($bannerTypeService->newQuery());
    }

    /**
     * @param array $ids
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
