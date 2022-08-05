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
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BannerDetailController extends Controller
{
    /**
     * @return mixed
     * @throws CmsException
     */
    public function updatePage(
        int $id,
        BannerService $bannerService,
        BannerTypeService $bannerTypeService,
        FileService $fileService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

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
    public function createPage(BannerTypeService $bannerTypeService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

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
     * @throws CmsException
     */
    public function bannerInitialDate(
        Request $request,
        BannerService $bannerService,
        BannerTypeService $bannerTypeService,
        FileService $fileService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

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
     * @throws CmsException
     */
    public function create(Request $request, BannerService $bannerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'name' => 'string|required',
            'url' => 'string|nullable',
            'active' => 'boolean|required',
            'desktop_image_id' => 'integer|required',
            'tablet_image_id' => 'integer|nullable',
            'mobile_image_id' => 'integer|nullable',
            'type_id' => 'integer|required',
            'button_id' => 'integer|nullable',
            'button' => 'array|nullable',

            'date_from' => 'string|nullable',
            'date_to' => 'string|nullable',
            'color' => 'string|nullable',
            'controls_color' => 'string|nullable',
            'path_templates' => 'string|nullable',
            'additional_text' => 'string|nullable',
            'sort' => 'integer|nullable',
            'banner_size' => 'integer|nullable',
            'banner_page' => 'integer|nullable',
            'banner_min_products' => 'integer|nullable',
        ]);

        $id = $bannerService->createBanner(new BannerDto($validatedData));

        return response()->json($id, 200);
    }

    /**
     * @throws CmsException
     */
    public function update(int $id, Request $request, BannerService $bannerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'id' => 'integer|required',
            'name' => 'string|required',
            'url' => 'string|nullable',
            'active' => 'boolean|required',
            'desktop_image_id' => 'integer|required',
            'tablet_image_id' => 'integer|nullable',
            'mobile_image_id' => 'integer|nullable',
            'type_id' => 'integer|required',
            'button_id' => 'integer|nullable',
            'button' => 'array|nullable',

            'date_from' => 'string|nullable',
            'date_to' => 'string|nullable',
            'color' => 'string|nullable',
            'controls_color' => 'string|nullable',
            'path_templates' => 'string|nullable',
            'additional_text' => 'string|nullable',
            'sort' => 'integer|nullable',
            'banner_size' => 'integer|nullable',
            'banner_page' => 'integer|nullable',
            'banner_min_products' => 'integer|nullable',
        ]);

        $validatedData['id'] = $id;

        $bannerService->updateBanner($validatedData['id'], new BannerDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function delete(int $id, BannerService $bannerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $bannerService->deleteBanner($id);

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    private function getBanner(int $id, BannerService $bannerService): ?BannerDto
    {
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
    private function getBannerTypes(BannerTypeService $bannerTypeService): Collection
    {
        return $bannerTypeService->bannerTypes(
            $bannerTypeService
                ->newQuery()
                ->setFilter(
                    'code',
                    '=',
                    [
                        // BannerTypeDto::WIDGET_CODE,
                        BannerTypeDto::CATALOG_TOP_CODE,
                        BannerTypeDto::CATALOG_ITEM_CODE,
                        // BannerTypeDto::PRODUCT_GROUP_CODE,
                        // BannerTypeDto::HEADER_CODE,
                        BannerTypeDto::MAIN_TOP_CODE,
                        BannerTypeDto::MAIN_NEW_CODE,
                        BannerTypeDto::MAIN_MIDDLE_CODE,
                        BannerTypeDto::MAIN_BEST_CODE,
                        BannerTypeDto::MENU_CODE,
                        // BannerTypeDto::MK_TOP_CODE,
                        BannerTypeDto::MK_ITEM_CODE,
                    ]
                )
        );
    }

    /**
     * @return Collection|FileDto[]
     */
    private function getBannerImages(array $ids, FileService $fileService): Collection
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

    private function getBannerButtonLocations(): Collection
    {
        return new Collection(BannerButtonLocationDto::all());
    }

    private function getBannerButtonTypes(): Collection
    {
        return new Collection(BannerButtonTypeDto::all());
    }
}
