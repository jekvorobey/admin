<?php

namespace App\Http\Controllers\Basket;

use App\Http\Controllers\Controller;
use Pim\Services\PublicEventService\PublicEventService;
use Greensight\Oms\Services\BasketService\BasketService;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Oms\Dto\BasketDto;
use Greensight\Oms\Dto\BasketItemDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Pim\Dto\BrandDto;
use Pim\Dto\CategoryDto;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PublicEventSprintService\PublicEventSprintService;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BasketDetailController
 * @package App\Http\Controllers\Basket
 */
class BasketDetailController extends Controller
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function detail(int $id)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_BASKETS);

        $this->loadBasketTypes = true;

        $basket = $this->getBasket($id);
        $this->title = 'Корзина ' . $basket->id . ' от ' . $basket->created_at;

        return $this->render('Basket/Detail', [
            'iBasket' => $basket,
        ]);
    }

    /**
     * @throws Exception
     */
    protected function getBasket(int $id): BasketDto
    {
        /** @var BasketService $basketService */
        $basketService = resolve(BasketService::class);

        $restQuery = $basketService
            ->newQuery()
            ->setFilter('id', $id)
            ->include('items');

        $baskets = $basketService->baskets($restQuery);
        if ($baskets->isEmpty()) {
            throw new NotFoundHttpException();
        }

        /** @var BasketDto $basket */
        $basket = $baskets->first();

        $this->addBasketUserInfo($basket);
        $this->addBasketCommonInfo($basket);
        $this->addBasketProductInfo($basket);

        if ($basket->type == BasketDto::TYPE_MASTER) {
            $this->addPublicEventInfo($basket);
        }

        return $basket;
    }

    protected function addBasketUserInfo(BasketDto $basket): void
    {
        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);
        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        $customerQuery = $customerService->newQuery()
            ->setFilter('id', $basket->customer_id);

        /** @var CustomerDto $customer */
        $customer = $customerService->customers($customerQuery)->first();

        if ($customer) {
            $userQuery = $userService->newQuery()
                ->setFilter('id', $customer->user_id);
            /** @var UserDto $user */
            $user = $userService->users($userQuery)->first();
        }

        if ($customer instanceof CustomerDto && isset($user) && $user instanceof UserDto) {
            $customer['user'] = $user;
            $basket['customer'] = $customer;
        }
    }

    protected function addBasketCommonInfo(BasketDto $basket): void
    {
        $basket->created_at = date_time2str(new Carbon($basket->created_at));
        $basket->updated_at = date_time2str(new Carbon($basket->updated_at));

        $basket['weight'] = $basket->items->sum(fn(BasketItemDto $item) => $item->getProductWeight());
        $basket['total_qty'] = $basket->items->sum('qty');
        $basket['total_price'] = $basket->items->sum('price');
    }

    protected function addBasketProductInfo(BasketDto $basket): void
    {
        /** @var ProductService $productService */
        $productService = resolve(ProductService::class);

        if ($basket->items->isNotEmpty()) {
            $offersIds = $basket->items->pluck('offer_id')->toArray();

            $restQuery = $productService->newQuery()
                ->addFields(ProductDto::entity(), 'vendor_code')
                ->include(CategoryDto::entity(), BrandDto::entity(), 'mainImage');

            $productsByOffers = $productService->productsByOffers($restQuery, $offersIds);

            $basket->items = $basket->items->map(function (BasketItemDto $basketItemDto) use ($productsByOffers) {
                $product = $basketItemDto->product;
                $productPim = $productsByOffers->has($basketItemDto->offer_id) ?
                    $productsByOffers[$basketItemDto->offer_id]->product : [];

                foreach ($product as $key => $value) {
                    $productPim[$key] = $value;
                }
                $basketItemDto['product'] = $productPim;

                return $basketItemDto;
            });
        }
    }

    private function addPublicEventInfo(BasketDto $basket): void
    {
        if ($basket->items->isEmpty()) {
            return;
        }

        $eventSprintIds = $basket->items->map(function ($basketItem) {
            return $basketItem->product['sprint_id'] ?? null;
        })->toArray();
        if (empty($eventSprintIds)) {
            return;
        }

        $publicEventSprintService = resolve(PublicEventSprintService::class);
        $eventSprints = $publicEventSprintService->query()
            ->setFilter('id', $eventSprintIds)
            ->get()
            ->keyBy('id');

        $publicEventService = resolve(PublicEventService::class);
        $publicEvents = $publicEventService->query()
            ->setFilter('id', $eventSprints->pluck('public_event_id')->unique()->all())
            ->withType()
            ->get()
            ->keyBy('id');
        if (empty($publicEvents)) {
            return;
        }

        $basket->items
            ->filter(fn($basketItem) => $basketItem->type == BasketDto::TYPE_MASTER)
            ->map(function (&$basketItem) use ($eventSprints, $publicEvents) {
                $publicEvent = $publicEvents->get($eventSprints->get($basketItem->product['sprint_id'])->public_event_id);
                if (!$publicEvent) {
                    return;
                }
                $publicEventDto = clone $publicEvent;
                $publicEventDto['ticket_type_name'] = $basketItem->product['ticket_type_name'] ?? null;

                $basketItem['event_info'] = $publicEventDto;
            });
    }

    /**
     * @throws Exception
     */
    public function save(int $id, BasketService $basketService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_BASKETS);

        $data = $this->validate(request(), [
            'manager_comment' => ['sometimes', 'string', 'nullable'],
        ]);

        $newBasketDto = new BasketDto();
        $newBasketDto->manager_comment = $data['manager_comment'];

        $basketService->updateBasket($id, $newBasketDto);

        return response()->json([
            'basket' => $this->getBasket($id),
        ]);
    }
}
