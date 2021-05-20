<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Store\Dto\StoreDto;
use Greensight\Store\Services\StoreService\StoreService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class TabStoreController extends Controller
{
    /**
     * AJAX пагинация списка складов
     *
     * @throws \Exception
     */
    public function page(int $merchantId, Request $request, StoreService $storeService): JsonResponse
    {
        $restQuery = $this->makeRestQuery($request, $merchantId);
        $stores = $this->loadStores($restQuery);
        $result = [
            'stores' => $stores,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $storeService->storesCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * @throws \Exception
     */
    protected function makeRestQuery(Request $request, int $merchantId): DataQuery
    {
        $page = $request->get('page', 1);
        $restQuery = (new RestQuery())->pageNumber($page, 10)
            ->setFilter('merchant_id', $merchantId);

        $filter = $this->getFilter();
        if ($filter) {
            foreach ($filter as $key => $value) {
                if ($value) {
                    switch ($key) {
                        case 'name':
                            $restQuery->setFilter($key, 'like', "%{$value}%");
                            break;
                        case 'address_string':
                            $field = 'address->' . $key;
                            $restQuery->setFilter($field, 'like', "%{$value}%");
                            break;
                        default:
                            if ($key === 'contact_phone') {
                                $value = phone_format($value);
                            }
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
        return Validator::make(
            request('filter') ?? [],
            [
                'id' => 'integer|someone',
                'name' => 'string|someone',
                'address_string' => 'string|someone',
                'contact_name' => 'string|someone',
                'contact_phone' => 'string|someone',
            ]
        )->attributes();
    }

    protected function loadStores(DataQuery $restQuery): Collection
    {
        /** @var StoreService $storeService */
        $storeService = resolve(StoreService::class);

        return $storeService->stores(
            $restQuery->addFields(StoreDto::entity(), 'id', 'name', 'address')
                ->include('storeContact')
        )->map(function (StoreDto $store) {
                $contact = $store->storeContact->first();
                return [
                    'id' => $store->id,
                    'name' => $store->name,
                    'address' => $store->address['address_string'],
                    'contact_name' => $contact ? $contact->name : 'N/A',
                    'contact_phone' => $contact ? $contact->phone : 'N/A',
                ];
        });
    }
}
