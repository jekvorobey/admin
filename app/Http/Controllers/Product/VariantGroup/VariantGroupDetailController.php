<?php

namespace App\Http\Controllers\Product\VariantGroup;


use App\Http\Controllers\Controller;
use Greensight\Marketing\Dto\Price\PriceOutDto;
use Greensight\Marketing\Dto\Price\PricesInDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Store\Services\StockService\StockService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Pim\Dto\Product\ProductApprovalStatus;
use Pim\Dto\Product\VariantGroupDto;
use Pim\Services\VariantGroupService\VariantGroupService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class VariantGroupDetailController
 * @package App\Http\Controllers\Product\VariantGroup
 */
class VariantGroupDetailController extends Controller
{
    /** @var VariantGroupService */
    protected $variantGroupService;
    /** @var StockService */
    protected $stockService;
    /** @var PriceService */
    protected $priceService;

    /**
     * VariantGroupDetailController constructor.
     */
    public function __construct()
    {
        $this->variantGroupService = resolve(VariantGroupService::class);
        $this->stockService = resolve(StockService::class);
        $this->priceService = resolve(PriceService::class);
    }

    /**
     * @param  int  $id
     * @return mixed
     * @throws \Exception
     */
    public function detail(int $id)
    {
        $variantGroup = $this->getVariantGroup($id);
        $this->title = 'Товарная группа "' . $variantGroup->name ? : 'Без названия' . '"';

        return $this->render('Product/VariantGroup/Detail', [
            'iVariantGroup' => $variantGroup,
        ]);
    }

    /**
     * @param  int  $id
     * @return VariantGroupDto
     * @throws \Exception
     */
    protected function getVariantGroup(int $id): VariantGroupDto
    {
        $restQuery = $this->variantGroupService
            ->newQuery()
            ->include('products.category', 'products.currentOffer', 'products.mainImage', 'products.brand', 'properties', 'mainProduct');
        $variantGroupDto = $this->variantGroupService->variantGroup($id, $restQuery);
        if (!$variantGroupDto) {
            throw new NotFoundHttpException();
        }

        $this->addVariantGroupCommonInfo($variantGroupDto);
        $this->addVariantGroupProductInfo($variantGroupDto);

        return $variantGroupDto;
    }

    /**
     * @param  VariantGroupDto  $variantGroupDto
     */
    protected function addVariantGroupCommonInfo(VariantGroupDto $variantGroupDto): void
    {
        $variantGroupDto->created_at = dateTime2str(new Carbon($variantGroupDto->created_at));
        $variantGroupDto->updated_at = dateTime2str(new Carbon($variantGroupDto->updated_at));
    }

    /**
     * @param  VariantGroupDto  $variantGroupDto
     */
    protected function addVariantGroupProductInfo(VariantGroupDto $variantGroupDto): void
    {
        if ($variantGroupDto->products->isNotEmpty()) {
            $offerIds = [];
            foreach ($variantGroupDto->products as $productDto) {
                $productDto->approval_status = ProductApprovalStatus::statusById($productDto->approval_status);
                $offerIds[] = $productDto->currentOffer ? $productDto->currentOffer->id : null;
            }
            $offerIds = array_filter($offerIds);

            if ($offerIds) {
                $stocks = $this->stockService->qtyByOffers($offerIds);
                $pricesIn = new PricesInDto();
                foreach ($offerIds as $offerId) {
                    $pricesIn->addOffer($offerId);
                }
                /** @var Collection|PriceOutDto[] $priceOutDtos */
                $priceOutDtos = $this->priceService->prices($pricesIn)->keyBy('offer_id');

                foreach ($variantGroupDto->products as $productDto) {
                    $offerId = $productDto->currentOffer->id;
                    $productDto['qty'] = $stocks[$offerId] ?? 0;
                    $productDto['price'] = $priceOutDtos->has($offerId) ? $priceOutDtos[$offerId]->price : 0;
                }
            }
        }
    }
}
