<?php

namespace App\Http\Controllers\Content\Banner;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Cms\Core\CmsException;
use Cms\Dto\BannerButtonLocationDto;
use Cms\Dto\BannerButtonTypeDto;
use Cms\Dto\BannerCountdownDto;
use Cms\Dto\BannerDto;
use Cms\Dto\BannerTypeDto;
use Cms\Services\BannerCountdownService\BannerCountdownService;
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
        int                    $id,
        BannerService          $bannerService,
        BannerTypeService      $bannerTypeService,
        BannerCountdownService $bannerCountdownService,
        FileService            $fileService
    )
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $banner = $this->getBanner($id, $bannerService);
        $bannerImages = $this->getImages([
            $banner['desktop_image_id'],
            $banner['tablet_image_id'],
            $banner['mobile_image_id'],
        ], $fileService);
        $bannerTypes = $this->getBannerTypes($bannerTypeService);

        $bannerCountdown = $this->getBannerCountdown($bannerCountdownService, $banner->countdown_id);
        $bannerCountdownImages = $this->getImages([
            $bannerCountdown['desktop_image_id'],
            $bannerCountdown['tablet_image_id'],
            $bannerCountdown['mobile_image_id'],
        ], $fileService);
        $banner->isAddCountdown = (bool)$bannerCountdown->date_to;

        $bannerButtonTypes = $this->getBannerButtonTypes();
        $bannerButtonLocations = $this->getBannerButtonLocations();

        return $this->render('Content/BannerDetail', [
            'iBanner' => $banner,
            'iBannerTypes' => $bannerTypes,
            'iBannerCountdown' => $bannerCountdown ?? null,
            'iBannerCountdownImages' => $bannerCountdownImages ?? null,
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
            'iBannerCountdown' => null,
            'iBannerCountdownImages' => null,
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
        Request           $request,
        BannerService     $bannerService,
        BannerTypeService $bannerTypeService,
        FileService       $fileService
    ): JsonResponse
    {
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
    public function create(Request $request, BannerService $bannerService, BannerCountdownService $bannerCountdownService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedCountdownData = $request->validate([
            'bannerCountdown.date_from' => 'string|nullable',
            'bannerCountdown.date_to' => 'string|nullable',
            'bannerCountdown.desktop_image_id' => 'integer|nullable',
            'bannerCountdown.tablet_image_id' => 'integer|nullable',
            'bannerCountdown.mobile_image_id' => 'integer|nullable',
            'bannerCountdown.text' => 'string|max:50|nullable',
            'bannerCountdown.text_color' => 'string|max:100|nullable',
            'bannerCountdown.num_color' => 'string|max:100|nullable',
            'bannerCountdown.bg_numbers_top' => 'string|max:100|nullable',
            'bannerCountdown.bg_numbers_bottom' => 'string|max:100|nullable',
        ]);

        $bannerCountdownId = $bannerCountdownService->createBannerCountdown(
            new BannerCountdownDto($validatedCountdownData ? $validatedCountdownData['bannerCountdown'] : $validatedCountdownData)
        );


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

        $validatedData['countdown_id'] = $bannerCountdownId;

        $id = $bannerService->createBanner(new BannerDto($validatedData));

        return response()->json($id, 200);
    }

    /**
     * @throws CmsException
     */
    public function update(int $id, Request $request, BannerService $bannerService, BannerCountdownService $bannerCountdownService): JsonResponse
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

        $validatedCountdownData = $request->validate([
            'bannerCountdown.id' => 'integer|required',
            'bannerCountdown.date_from' => 'string|nullable',
            'bannerCountdown.date_to' => 'string|nullable',
            'bannerCountdown.desktop_image_id' => 'integer|nullable',
            'bannerCountdown.tablet_image_id' => 'integer|nullable',
            'bannerCountdown.mobile_image_id' => 'integer|nullable',
            'bannerCountdown.text' => 'string|max:50|nullable',
            'bannerCountdown.text_color' => 'string|max:100|nullable',
            'bannerCountdown.num_color' => 'string|max:100|nullable',
            'bannerCountdown.bg_numbers_top' => 'string|max:100|nullable',
            'bannerCountdown.bg_numbers_bottom' => 'string|max:100|nullable',
        ]);

        $bannerCountdownService->updateBannerCountdown(
            $validatedCountdownData['bannerCountdown']['id'],
            new BannerCountdownDto($validatedCountdownData['bannerCountdown']
            ));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function delete(int $id, BannerService $bannerService, BannerCountdownService $bannerCountdownService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $bannerCountdownId = $this->getBanner($id, $bannerService)->countdown_id;

        $bannerService->deleteBanner($id);

        $bannerCountdownService->deleteBannerCountdown($bannerCountdownId);

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
     * @return BannerCountdownDto
     * @throws CmsException
     */
    private function getBannerCountdown(BannerCountdownService $bannerCountdownService, $countdownId): BannerCountdownDto
    {
        $countdowns = $bannerCountdownService->bannerCountdowns($bannerCountdownService->newQuery())->keyBy('id');
        return $countdowns->get($countdownId);
    }

    /**
     * @return Collection|FileDto[]
     */
    private function getImages(array $ids, FileService $fileService): Collection
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
