<?php

namespace App\Http\Controllers\Referral;

use App\Http\Controllers\Controller;
use App\Managers\PromoProducts\PromoProductsManager;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Customer\Core\CustomerException;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pim\Core\PimException;

class MassPromoProductsController extends Controller
{
    /**
     * Список промо-товаров, которые назначаются на реф. партнеров массово
     * @return mixed
     * @throws PimException
     * @throws CustomerException
     */
    public function list(
        CustomerService $customerService,
        ReferralService $referralService,
        PromoProductsManager $promoProductsManager
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_REFERRALS);

        // Получить список массовых промо-товаров //
        $promoProducts = $promoProductsManager->fetch();

        // Получить все виды деятельности //
        $activities = $customerService->activities()->load();

        // Получить реферальные уровни и названия //
        $ref_levels = $referralService->getLevels();

        $this->title = 'Товары для продвижения';

        return $this->render('Referral/PromoProducts', [
            'iPromoProducts' => $promoProducts,
            'activities' => $activities,
            'ref_levels' => $ref_levels,
        ]);
    }

    /**
     * Обновить/Добавить промо-товар для массового назначения
     * @throws PimException
     */
    public function editProduct(Request $request, PromoProductsManager $promoProductsManager): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_REFERRALS);

        $data = $this->validate($request, [
            'product_id' => 'required|integer',
            'mass' => 'required|integer',
            'active' => 'integer',
            'files' => 'nullable|array',
            'description' => 'required',
        ]);

        $promoProductsManager->save(null, $data);

        return response()->json([
            'promoProducts' => $promoProductsManager->fetch(),
        ]);
    }

    /**
     * Назначить промо-товар реф. партнерам по критериям
     * @throws PimException
     */
    public function attachProduct(
        ReferralService $referralService,
        PromoProductsManager $promoProductsManager
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_REFERRALS);

        $data = $this->validate(request(), [
            'promo_products' => 'required|json',
            'segments' => 'nullable|array',
            'segments.all' => 'nullable|string',
            'segments.levels.*' => 'nullable|integer',
            'segments.brand' => 'nullable|string',
            'segments.category' => 'nullable|string',
            'segments.activities.*' => 'nullable|integer',
            'segments.user_ids.*' => 'nullable|integer',
        ]);

        $segments = $data['segments'] ?? null;
        $referralService->attachMassPromotions($data['promo_products'], $segments);

        return response()->json([
            'promoProducts' => $promoProductsManager->fetch(),
        ]);
    }

    /**
     * Удалить промо-товар
     */
    public function removeProduct(ReferralService $referralService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_REFERRALS);

        $data = $this->validate(request(), [
            'promo_id' => 'required|integer',
        ]);

        $referralService->deletePromoProduct($data['promo_id']);

        return response('', 204);
    }
}
