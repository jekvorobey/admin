<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Marketing\Builder\Bonus\BonusBuilder;
use Greensight\Marketing\Core\MarketingException;
use Greensight\Marketing\Dto\Bonus\BonusDto;
use Greensight\Marketing\Dto\Bonus\BonusInDto;
use Greensight\Marketing\Dto\Bonus\ProductBonusOption\ProductBonusOptionDto;
use Greensight\Marketing\Services\BonusService\BonusService;
use Greensight\Marketing\Services\ProductBonusOptionService\ProductBonusOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Pim\Services\BrandService\BrandService;
use Pim\Services\CategoryService\CategoryService;

/**
 * Class BonusController
 * @package App\Http\Controllers\Marketing
 */
class BonusController extends Controller
{
    public function index()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Правила начисления бонусов';
        $bonusService = resolve(BonusService::class);
        $params = new BonusInDto();
        $bonuses = $bonusService->bonuses($params)->map(function (BonusDto $bonus) {
            $bonus['validityPeriod'] = $bonus->validityPeriod();
            return $bonus;
        });

        return $this->render('Marketing/Bonus/List', [
            'iBonuses' => $bonuses,
            'statuses' => BonusDto::allStatuses(),
            'types' => BonusDto::allTypes(),
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $data = $request->validate([
            'name' => 'string|required',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'status' => Rule::in(BonusDto::availableStatuses()),
            'type' => Rule::in(BonusDto::availableTypes()),
            'value' => 'numeric|gte:0|required',
            'value_type' => Rule::in([BonusDto::VALUE_TYPE_PERCENT, BonusDto::VALUE_TYPE_ABSOLUTE]),
            'valid_period' => 'numeric|gt:0|nullable',
            'promo_code_only' => 'boolean|nullable',
            'offers' => 'array',
            'offers.*' => 'integer',
            'brands' => 'array',
            'brands.*' => 'integer',
            'categories' => 'array',
            'categories.*' => 'integer',
        ]);

        $data['start_date'] = $data['start_date']
            ? Carbon::createFromFormat('Y-m-d', $data['start_date'])
            : null;

        $data['end_date'] = $data['end_date']
            ? Carbon::createFromFormat('Y-m-d', $data['end_date'])
            : null;

        $builder = new BonusBuilder($data);
        /** @var BonusService $bonusService */
        $bonusService = resolve(BonusService::class);
        $result = $bonusService->create($builder);
        return response()->json(['status' => $result ? 'ok' : 'fail']);
    }

    /**
     * @return mixed
     */
    public function createPage()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_MARKETING);

        $this->title = 'Создание правила начисления бонуса';
        $this->loadBonusValueTypes = true;
        $this->loadBonusTypes = true;

        $brandService = resolve(BrandService::class);
        $categoryService = resolve(CategoryService::class);

        $types = BonusDto::allTypes();
        unset($types[BonusDto::TYPE_SERVICE]);
        unset($types[BonusDto::TYPE_ANY_SERVICE]);

        return $this->render('Marketing/Bonus/Create', [
            'statuses' => BonusDto::allStatuses(),
            'types' => $types,
            'brands' => $brandService->brands($brandService->newQuery()),
            'categories' => $categoryService->categories($categoryService->newQuery()),
        ]);
    }

    public function status(): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        /** @var BonusService $bonusService */
        $bonusService = resolve(BonusService::class);

        $ids = request('ids');
        $status = request('status');

        foreach ($ids as $id) {
            $bonusService->update($id, (new BonusBuilder())->status($status));
        }

        return response('', 204);
    }

    public function delete(): Response|JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        /** @var BonusService $bonusService */
        $bonusService = resolve(BonusService::class);
        $ids = request('ids');
        if (!is_array($ids)) {
            return response()->json(['error' => ['message' => 'Пустой запрос']], 400);
        }

        $failed = [];
        foreach ($ids as $id) {
            try {
                $bonusService->delete($id);
            } catch (MarketingException $ex) {
                if ($ex->getCode() === BonusService::FAILED_DEPENDENCY_CODE) {
                    $failed[$id] = (int) $id;
                }
            }
        }

        if (empty($failed)) {
            return response('', 204);
        }

        return response()->json([
            'error' => [
                'items' => array_values($failed),
                'code' => BonusService::FAILED_DEPENDENCY_CODE,
                'message' => 'Ошибка при удалении бонусов',
            ],
        ], BonusService::FAILED_DEPENDENCY_CODE);
    }

    public function changeProductLimit(Request $request): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_MARKETING);

        $data = $request->validate([
            'product_id' => 'integer|required',
            'value' => 'integer|nullable',
        ]);

        $key = ProductBonusOptionDto::MAX_PERCENTAGE_PAYMENT;
        $productBonusOptionService = resolve(ProductBonusOptionService::class);
        if (isset($data['value'])) {
            $productBonusOptionService->put($data['product_id'], $key, $data['value']);
        } else {
            $productBonusOptionService->delete($data['product_id'], $key);
        }

        return response('', 204);
    }
}
