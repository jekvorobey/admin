<?php

namespace App\Http\Controllers\Product\VariantGroup;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Marketing\Services\PriceService\PriceService;
use Greensight\Store\Services\StockService\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use MerchantManagement\Dto\MerchantDto;
use Pim\Core\PimException;
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
     * @return mixed
     * @throws Exception
     */
    public function detail(int $id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $variantGroup = $this->getVariantGroup($id);
        $this->title = 'Товарная группа "' . ($variantGroup->name ? : 'Без названия') . '"';

        return $this->render('Product/VariantGroup/Detail', [
            'iVariantGroup' => $variantGroup,
        ]);
    }

    /**
     * @throws Exception
     */
    protected function getVariantGroup(int $id): VariantGroupDto
    {
        $restQuery = $this->variantGroupService
        ->newQuery()
        ->addFields(
            VariantGroupDto::entity(),
            'id',
            'name',
            'main_product_id',
            'merchant_id',
            'products_count',
            'properties_count',
            'created_at',
            'updated_at'
        );
        $variantGroupDto = $this->variantGroupService->variantGroup($id, $restQuery);
        if (!$variantGroupDto) {
            throw new NotFoundHttpException('VariantGroup not found');
        }

        $this->addVariantGroupCommonInfo($variantGroupDto);

        return $variantGroupDto;
    }

    /**
     * @throws Exception
     */
    protected function addVariantGroupCommonInfo(VariantGroupDto $variantGroupDto): void
    {
        $variantGroupDto->created_at = date_time2str(new Carbon($variantGroupDto->created_at));
        $variantGroupDto->updated_at = date_time2str(new Carbon($variantGroupDto->updated_at));
        /** @var MerchantDto $merchant */
        $merchant = $variantGroupDto->merchant_id ? $this->getMerchants([$variantGroupDto->merchant_id])->first() : null;
        $variantGroupDto['merchant'] = $merchant;
    }

    /**
     * @throws PimException
     */
    public function save(int $variantGroupId, Request $request): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PRODUCTS);

        $data = $this->validate($request, [
            'name' => ['nullable', 'string'],
        ]);
        $variantGroupDto = new VariantGroupDto();
        $variantGroupDto->name = $data['name'] ?? null;
        $this->variantGroupService->updateVariantGroup($variantGroupId, $variantGroupDto);

        return response('', 204);
    }
}
