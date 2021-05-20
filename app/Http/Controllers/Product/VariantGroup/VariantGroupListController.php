<?php

namespace App\Http\Controllers\Product\VariantGroup;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\DataQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Pim\Dto\BrandDto;
use Pim\Dto\Product\VariantGroupDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;
use Pim\Services\VariantGroupService\VariantGroupService;

/**
 * Class VariantGroupListController
 * @package App\Http\Controllers\Product\VariantGroup
 */
class VariantGroupListController extends Controller
{
    /** @var VariantGroupService */
    protected $variantGroupService;
    /** @var BrandService */
    protected $brandService;
    /** @var CategoryService */
    protected $categoryService;

    /**
     * VariantGroupListController constructor.
     */
    public function __construct()
    {
        $this->variantGroupService = resolve(VariantGroupService::class);
        $this->brandService = resolve(BrandService::class);
        $this->categoryService = resolve(CategoryService::class);
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Pim\Core\PimException
     */
    public function index(Request $request)
    {
        $this->title = 'Список товарных групп';

        $restQuery = $this->makeRestQuery(true);
        $pager = $this->variantGroupService->variantGroupsCount($restQuery);
        $variantGroups = $this->loadVariantGroups($restQuery);

        return $this->render('Product/VariantGroup/List', [
            'iVariantGroups' => $variantGroups,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
            'merchants' => $this->getMerchants(),
            'brands' => $this->brandService->newQuery()->addFields(BrandDto::entity(), 'id', 'name')->brands(),
            'categories' => $this->categoryService->categories($this->categoryService->newQuery())->values()->toArray(),
            'iFilter' => $this->getFilter(true),
            'iSort' => $request->get('sort', 'created_at'),
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function create(Request $request): JsonResponse
    {
        $data = $this->validate($request, [
            'name' => ['nullable', 'string'],
            'merchant_id' => ['nullable', 'integer'],
        ]);

        $variantGroupDto = new VariantGroupDto();
        $variantGroupDto->name = $data['name'] ?? null;
        $variantGroupDto->merchant_id = $data['merchant_id'] ?? null;
        $id = $this->variantGroupService->createVariantGroup($variantGroupDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    public function delete(Request $request): Response
    {
        $data = $this->validate($request, [
            'ids' => ['array', 'required'],
            'ids.*' => ['integer'],
        ]);

        try {
            $this->variantGroupService->deleteVariantGroups($data['ids']);
        } catch (\Throwable $exception) {
            if (strpos($exception->getMessage(), 'Cannot delete or update a parent row') !== false) {
                return response('', 424);
            }
            throw $exception;
        }

        return response('', 204);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    /*public function byOffers(Request $request)
    {
        return $this->variantGroupService->ordersByOffers(['offersIds' => $request->input('offersIds'), 'page' => $request->input('page')]);
    }*/

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @return JsonResponse
     * @throws \Exception
     */
    public function page(): JsonResponse
    {
        $restQuery = $this->makeRestQuery();
        $orders = $this->loadVariantGroups($restQuery);
        $result = [
            'variantGroups' => $orders,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $this->variantGroupService->variantGroupsCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * @param bool $withDefault
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'id' => 'integer|sometimes',
                'merchants' => 'array|sometimes',
                'merchants.*' => 'integer',
                'created_at' => 'string|sometimes',
                'created_between' => 'array|sometimes',
                'created_between.*' => 'string',
                'offer_xml_id' => 'string|sometimes',
                'product_vendor_code' => 'string|sometimes',
                'brands' => 'array|sometimes',
                'brands.*' => 'integer',
                'categories' => 'array|sometimes',
                'categories.*' => 'integer',
            ]
        );
    }

    /**
     * @param DataQuery $restQuery
     * @return Collection|VariantGroupDto[]
     * @throws \Pim\Core\PimException
     */
    protected function loadVariantGroups(DataQuery $restQuery): Collection
    {
        $variantGroups = $this->variantGroupService->variantGroups($restQuery);
        $merchants = $this->getMerchants($variantGroups->pluck('merchant_id')->all());

        $variantGroups = $variantGroups->map(function (VariantGroupDto $variantGroupDto) use ($merchants) {
            $variantGroupDto->name = $variantGroupDto->name ? : 'Нет названия';
            $variantGroupDto['merchant'] = $variantGroupDto->merchant_id && $merchants->has($variantGroupDto->merchant_id)
                ? $merchants[$variantGroupDto->merchant_id] : null;
            $variantGroupDto->created_at = date_time2str(new Carbon($variantGroupDto->created_at));
            $variantGroupDto->updated_at = date_time2str(new Carbon($variantGroupDto->updated_at));

            return $variantGroupDto;
        });

        return $variantGroups;
    }

    /**
     * @param bool $withDefaultFilter
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $this->variantGroupService->newQuery()
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
            )
            ->include('properties', 'products', 'mainProduct');

        $page = $this->getPage();
        $restQuery->pageNumber($page, 20);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'created_between':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter('created_at', '>=', $value[0]);
                            $restQuery->setFilter('created_at', '<=', $value[1]);
                        }
                        break;
                    case 'created_at':
                        if ($value) {
                            $restQuery->setFilter($key, 'like', "{$value}%");
                        }
                        break;
                    case 'merchants':
                        $restQuery->setFilter('merchant_id', $value);
                        break;

                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }
}
