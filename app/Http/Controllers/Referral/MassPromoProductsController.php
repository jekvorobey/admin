<?php

namespace App\Http\Controllers\Referral;


use App\Http\Controllers\Controller;
use App\Http\Controllers\Customers\Detail\TabPromoProductController;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pim\Core\PimException;
use Pim\Services\ProductService\ProductService;

class MassPromoProductsController extends Controller
{
    /**
     * Список промо-товаров, которые назначаются на реф. партнеров массово
     * @return mixed
     * @throws PimException
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
     */
    public function attachProduct() {

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