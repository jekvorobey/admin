<?php

namespace App\Http\Controllers\Basket;

use App\Core\CustomerHelper;
use App\Core\UserHelper;
use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\DataQuery;
use Greensight\Oms\Dto\BasketDto;
use Greensight\Oms\Dto\Order\OrderType;
use Greensight\Oms\Services\BasketService\BasketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Pim\Dto\BrandDto;
use Pim\Services\BrandService\BrandService;

/**
 * Class BasketListController
 * @package App\Http\Controllers\Basket
 */
class BasketListController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function index(BasketService $basketService, BrandService $brandService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_BASKETS);

        $this->title = 'Брошенные корзины';
        $this->loadBasketTypes = true;

        $restQuery = $this->makeRestQuery($basketService, true);
        $pager = $basketService->basketsCount($restQuery);
        $baskets = $this->loadBaskets($restQuery);

        return $this->render('Basket/List', [
            'iBaskets' => $baskets,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
            'merchants' => $this->getMerchants(),
            'orderTypes' => OrderType::allTypes(),
            'brands' => $brandService->newQuery()->addFields(BrandDto::entity(), 'id', 'name')->brands(),
            'iFilter' => $this->getFilter(true),
        ]);
    }

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @throws Exception
     */
    public function page(BasketService $basketService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_BASKETS);

        $restQuery = $this->makeRestQuery($basketService);
        $baskets = $this->loadBaskets($restQuery);
        $result = [
            'baskets' => $baskets,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $basketService->basketsCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * @throws ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'customer' => 'string|sometimes',
                'created_at' => 'string',
                'created_between' => 'array|sometimes',
                'created_between.*' => 'string',
                'updated_at' => 'string',
                'updated_between' => 'array|sometimes',
                'updated_between.*' => 'string',
                'price_from' => 'numeric|sometimes',
                'price_to' => 'numeric|sometimes',
                'product_vendor_code' => 'string|sometimes',
                'brands' => 'array|sometimes',
                'brands.*' => 'integer',
                'merchants' => 'array|sometimes',
                'merchants.*' => 'integer',
                'type.*' => Rule::in(array_keys(OrderType::allTypes())),
            ]
        );
    }

    protected function loadBaskets(DataQuery $restQuery): Collection
    {
        /** @var BasketService $basketService */
        $basketService = resolve(BasketService::class);

        $baskets = $basketService->baskets($restQuery);
        if ($baskets->isEmpty()) {
            return collect();
        }

        // Получаем покупателей корзин
        $customerIds = $baskets->pluck('customer_id')->unique()->all();
        $customers = CustomerHelper::getCustomersByIds($customerIds);

        // Получаем самих пользователей
        $userIds = $customers->pluck('user_id')->all();
        $users = UserHelper::getUsersByIds($userIds);

        $baskets = $baskets->map(function (BasketDto $basket) use ($users, $customers) {
            $data = $basket->toArray();

            $data['customer'] = $users[$customers[$basket->customer_id]->user_id] ?? null;

            $data['created_at'] = date_time2str(new Carbon($basket->created_at));
            $data['updated_at'] = date_time2str(new Carbon($basket->updated_at));

            $data['price'] = $basket->items->sum('price');

            return $data;
        });

        return $baskets;
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(BasketService $basketService, bool $withDefaultFilter = false): DataQuery
    {
        $restQuery = $basketService->newQuery()->include('items');

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
                            $restQuery->setFilter('created_at', '<=', end_of_day_filter($value[1]));
                        }
                        break;
                    case 'created_at':
                        if ($value) {
                            $restQuery->setFilter($key, 'like', "{$value}%");
                        }
                        break;
                    case 'updated_between':
                        $value = array_filter($value);
                        if ($value) {
                            $restQuery->setFilter('updated_at', '>=', $value[0]);
                            $restQuery->setFilter('updated_at', '<=', end_of_day_filter($value[1]));
                        }
                        break;
                    case 'updated_at':
                        if ($value) {
                            $restQuery->setFilter($key, 'like', "{$value}%");
                        }
                        break;
                    case 'price_from':
                        if ($value) {
                            $restQuery->setFilter('price', '>=', $value);
                        }
                        break;
                    case 'price_to':
                        if ($value) {
                            $restQuery->setFilter('price', '<=', $value);
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

        $restQuery
            ->setFilter('is_belongs_to_order', false)
            ->addSort('created_at', 'desc');

        return $restQuery;
    }
}
