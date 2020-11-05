<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use MerchantManagement\Dto\MerchantSettingDto;
use MerchantManagement\Services\MerchantService\MerchantService;

class TabBillingController extends Controller
{
    /**
     * @param int             $merchantId
     * @param MerchantService $merchantService
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $merchantId, MerchantService $merchantService)
    {
        $settings = $merchantService->getSetting($merchantId, MerchantSettingDto::BILLING_CYCLE)->first();
        $billingCycle = $settings ? $settings->value : MerchantSettingDto::DEFAULT_BILLING_CYCLE;
        return response()->json([
            'billing_cycle' => (int)$billingCycle,
        ]);
    }

    /**
     * Сохранить биллинговый период
     * @param int $merchantId
     * @param MerchantService $merchantService
     * @return Application|ResponseFactory|Response
     */
    public function billingCycle(int $merchantId, MerchantService $merchantService)
    {
        $data = $this->validate(request(),[
            'billing_cycle' => 'integer|gt:0'
        ]);
        $merchantService->setSetting($merchantId, MerchantSettingDto::BILLING_CYCLE, $data['billing_cycle']);
        return response('', 204);
    }

     /**
     * AJAX пагинация списка операций биллинга
     *
     * @param int $merchantId
     * @param Request $request
     * @param  MerchantService $merchantService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function billingList(int $merchantId, Request $request, MerchantService $merchantService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($request, $merchantId);
        $billingList = $this->loadBillingList($merchantId, $restQuery);
        $result = [
            'billingList' => $billingList,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $merchantService->merchantBillingListCount($merchantId, $restQuery);
        }

        return response()->json($result);
    }

    /**
     * @param $merchantId
     * @param DataQuery $restQuery
     * @return array
     */
    protected function loadBillingList($merchantId, DataQuery $restQuery): array
    {
        /** @var MerchantService $merchantService */
        $merchantService = resolve(MerchantService::class);

        return $merchantService->merchantBillingList($restQuery, $merchantId);
    }

    /**
     * @param Request $request
     * @param int $merchantId
     * @return DataQuery
     * @throws \Exception
     */
    protected function makeRestQuery( Request $request, int $merchantId ): DataQuery
    {
        $page = $request->get('page', 1);
        $restQuery = (new RestQuery())
            ->pageNumber($page, 15)
            ->setFilter('merchant_id', $merchantId);

        $filter = $this->getFilter();
        if ($filter) {
            foreach ($filter as $key => $value) {
                if ($value) {
                    switch ($key) {
                        case 'commission_from':
                            $restQuery->setFilter('commission', '>=', $value);
                            break;
                        case 'commission_to':
                            $restQuery->setFilter('commission', '<=', $value);
                            break;
                        case 'discount_from':
                            $restQuery->setFilter('discount', '>=', $value);
                            break;
                        case 'discount_to':
                            $restQuery->setFilter('discount', '<=', $value);
                            break;
                        case 'price_from':
                            $restQuery->setFilter('price', '>=', $value);
                            break;
                        case 'price_to':
                            $restQuery->setFilter('price', '<=', $value);
                            break;
                        case 'name':
                            $restQuery->setFilter($key, 'like', "%{$value}%");
                            break;
                        case 'status_at':
                            $value = array_filter($value);
                            if ($value) {
                                $restQuery->setFilter($key, '>=', $value[0]);
                                $restQuery->setFilter($key, '<=', (new \Illuminate\Support\Carbon($value[1]))->modify('+1 day')->format('Y-m-d'));
                            }
                            break;
                        default:
                            $restQuery->setFilter($key, $value);
                    }
                }
            }
        }

        return $restQuery;
    }

    /**
     * @return array
     */
    protected function getFilter(): array
    {
        return Validator::make(request('filter') ?? [],
            [
                'offer_id' => 'integer|someone',
                'name' => 'string|someone',
                'address_string' => 'string|someone',
                'contact_name' => 'string|someone',
                'contact_phone' => 'string|someone',
            ]
        )->attributes();
    }

}
