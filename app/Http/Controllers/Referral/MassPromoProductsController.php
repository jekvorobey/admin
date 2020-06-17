<?php

namespace App\Http\Controllers\Referral;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Detail\TabPromoProductController;
use Greensight\Customer\Core\CustomerException;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pim\Core\PimException;
use Pim\Services\ProductService\ProductService;

class MassPromoProductsController extends Controller
{
    /**
     * Список промо-товаров, которые назначаются на реф. партнеров массово
     * @param CustomerService $customerService
     * @param ReferralService $referralService
     * @return mixed
     * @throws PimException
     * @throws CustomerException
     */
    public function list(
        CustomerService $customerService,
        ReferralService $referralService)
    {
        // Получить список массовых промо-товаров //
        $helper = resolve(TabPromoProductController::class);
        $promoProducts = $helper->loadPromotionProducts(null);

        // Получить все виды деятельности //
        $activities = $customerService->activities()->load();

        // Получить реферальные уровни и названия //
        $ref_levels = $referralService->getLevels();

        $this->title = 'Товары для продвижения';
        return $this->render('Referral/PromoProducts', [
            'iPromoProducts' => $promoProducts,
            'activities' => $activities,
            'ref_levels' => $ref_levels
        ]);
    }

    /**
     * Обновить/Добавить промо-товар для массового назначения
     * @param Request $request
     * @param ProductService $productService
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws PimException
     */
    public function editProduct(
        Request $request,
        ProductService $productService,
        ReferralService $referralService)
    {
        $helper = resolve(TabPromoProductController::class);
        return $helper
            ->save(null,$request,$productService,$referralService);
    }

    /**
     * Назначить промо-товар реф. партнерам по критериям
     * @param ReferralService $referralService
     * @return JsonResponse
     * @throws PimException
     */
    public function attachProduct(ReferralService $referralService) {
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

        $segments = isset($data['segments']) ? $data['segments'] : null;
        $referralService->attachMassPromotions($data['promo_products'], $segments);

        $helper = resolve(TabPromoProductController::class);
        $promoProducts = $helper->loadPromotionProducts(null);
        return response()->json([
            'promoProducts' => $promoProducts,
        ]);
    }

    /**
     * Удалить промо-товар
     * @param ReferralService $referralService
     * @return Application|ResponseFactory|Response
     */
    public function removeProduct(ReferralService $referralService) {
        $data = $this->validate(request(), [
            'promo_id' => 'required|integer'
        ]);

        $referralService->deletePromoProduct($data['promo_id']);
        return response('', 204);
    }

}