<?php

namespace App\Http\Controllers\Product\VariantGroup;


use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\DataQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Pim\Dto\BrandDto;
use Pim\Dto\Product\VariantGroupDto;
use Pim\Services\BrandService\BrandService;
use Pim\Services\VariantGroupService\VariantGroupService;

/**
 * Class VariantGroupListController
 * @package App\Http\Controllers\Product\VariantGroup
 */
class VariantGroupListController extends Controller
{
    /**
     * @param  Request  $request
     * @param  VariantGroupService  $variantGroupService
     * @param  BrandService  $brandService
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Pim\Core\PimException
     */
    public function index(
        Request $request,
        VariantGroupService $variantGroupService,
        BrandService $brandService
    ) {
        $this->title = 'Список товарных групп';

        $restQuery = $this->makeRestQuery($variantGroupService, true);
        $pager = $variantGroupService->variantGroupsCount($restQuery);
        $variantGroups = $this->loadVariantGroups($variantGroupService, $restQuery);

        return $this->render('Product/VariantGroup/List', [
            'iVariantGroups' => $variantGroups,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
            'merchants' => $this->getMerchants(),
            'brands' => $brandService->newQuery()->addFields(BrandDto::entity(), 'id', 'name')->brands(),
            'iFilter' => $this->getFilter(true),
            'iSort' => $request->get('sort', 'created_at'),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  VariantGroupService  $variantGroupService
     * @return JsonResponse
     * @throws \Pim\Core\PimException
     */
    public function create(Request $request, VariantGroupService $variantGroupService): JsonResponse
    {
        $data = $this->validate($request, [
            'name' => ['nullable', 'string'],
            'merchant_id' => ['nullable', 'integer'],
        ]);

        $variantGroupDto = new VariantGroupDto();
        $variantGroupDto->name = $data['name'] ?? null;
        $variantGroupDto->merchant_id = $data['merchant_id'] ?? null;
        $id = $variantGroupService->createVariantGroup($variantGroupDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    /**
     * @param  VariantGroupService  $variantGroupService
     * @param  Request  $request
     * @return mixed
     */
    /*public function byOffers(VariantGroupService $variantGroupService, Request $request)
    {
        return $variantGroupService->ordersByOffers(['offersIds' => $request->input('offersIds'), 'page' => $request->input('page')]);
    }*/

    /**
     * @return int
     */
    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @param  VariantGroupService  $variantGroupService
     * @return JsonResponse
     * @throws \Exception
     */
    public function page(VariantGroupService $variantGroupService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($variantGroupService);
        $orders = $this->loadVariantGroups($variantGroupService, $restQuery);
        $result = [
            'variantGroups' => $orders,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $variantGroupService->variantGroupsCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * @param  bool  $withDefault
     * @return array
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'created_at' => 'string',
                'created_between' => 'array|sometimes',
                'created_between.*' => 'string',
                'merchants' => 'array|sometimes',
                'merchants.*' => 'integer',
            ]
        );
    }

    /**
     * @param  VariantGroupService  $variantGroupService
     * @param  DataQuery  $restQuery
     * @return Collection|VariantGroupDto[]
     * @throws \Pim\Core\PimException
     */
    protected function loadVariantGroups(VariantGroupService $variantGroupService, DataQuery $restQuery): Collection
    {
        $variantGroups = $variantGroupService->variantGroups($restQuery);
        $merchants = $this->getMerchants($variantGroups->pluck('merchant_id')->all());

        $variantGroups = $variantGroups->map(function (VariantGroupDto $variantGroupDto) use ($merchants) {
            $data = $variantGroupDto->toArray();

            $data['merchant'] = $variantGroupDto->merchant_id && $merchants->has($variantGroupDto->merchant_id)
                ? $merchants[$variantGroupDto->merchant_id] : null;
            $data['created_at'] = dateTime2str(new Carbon($variantGroupDto->created_at));
            $data['updated_at'] = dateTime2str(new Carbon($variantGroupDto->updated_at));

            return $data;
        });

        return $variantGroups;
    }

    /**
     * @param  VariantGroupService  $variantGroupService
     * @param  bool  $withDefaultFilter
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery(VariantGroupService $variantGroupService, bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $variantGroupService->newQuery()->include('properties', 'products', 'mainProduct');

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
