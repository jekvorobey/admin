<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Controller;
use Greensight\Marketing\Builder\Bonus\BonusBuilder;
use Greensight\Marketing\Dto\Bonus\BonusDto;
use Greensight\Marketing\Dto\Bonus\BonusInDto;
use Greensight\Marketing\Services\BonusService\BonusService;

/**
 * Class BonusController
 * @package App\Http\Controllers\Marketing
 */
class BonusController extends Controller
{
    public function index()
    {
        $this->title = 'Бонусы';
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
            'creators' => [],
        ]);
    }

    public function createPage()
    {
        // todo
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

    /**
     * @return \Illuminate\Http\Response
     */
    public function delete()
    {
        /** @var BonusService $bonusService */
        $bonusService = resolve(BonusService::class);
        $ids = request('ids');
        foreach ($ids as $id) {
            $bonusService->delete($id);
        }

        return response('', 204);
    }
}
