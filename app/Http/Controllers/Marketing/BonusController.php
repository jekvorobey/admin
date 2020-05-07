<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Greensight\Marketing\Builder\Bonus\BonusBuilder;
use Greensight\Marketing\Core\MarketingException;
use Greensight\Marketing\Dto\Bonus\BonusDto;
use Greensight\Marketing\Dto\Bonus\BonusInDto;
use Greensight\Marketing\Services\BonusService\BonusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Class BonusController
 * @package App\Http\Controllers\Marketing
 */
class BonusController extends Controller
{
    public function index()
    {
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

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = $request->validate([
            'name' => 'string|required',
            'start_date' => 'date|nullable',
            'end_date' => 'date|nullable',
            'status' => Rule::in(BonusDto::availableStatuses()),
            'type' => Rule::in(BonusDto::availableTypes()),
            'value' => 'numeric|gte:0|required',
            'value_type' => Rule::in([BonusDto::VALUE_TYPE_PERCENT, BonusDto::VALUE_TYPE_RUB]),
            'valid_period' => 'numeric|gt:0|nullable',
            'promo_code_only' => 'boolean|nullable',
        ]);

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
        $this->title = 'Создание правила начисления бонуса';
        $this->loadBonusValueTypes = true;

        return $this->render('Marketing/Bonus/Create', [
            'statuses' => BonusDto::allStatuses(),
            'types' => BonusDto::allTypes(),
        ]);
    }

    /**
     * @return \Illuminate\Http\Response
     */
    public function status()
    {
        /** @var BonusService $bonusService */
        $bonusService = resolve(BonusService::class);

        $ids = request('ids');
        $status = request('status');

        foreach ($ids as $id) {
            $bonusService->update($id, (new BonusBuilder())->status($status));
        }

        return response('', 204);
    }

    public function delete()
    {
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
            ]
        ], BonusService::FAILED_DEPENDENCY_CODE);
    }
}
