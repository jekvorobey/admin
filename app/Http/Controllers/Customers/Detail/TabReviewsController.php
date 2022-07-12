<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\JsonResponse;
use Pim\Core\PimException;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Pim\Services\PublicEventService\PublicEventService;
use Pim\Services\ReviewService\ReviewService;

class TabReviewsController extends Controller
{
    private const PER_PAGE = 10;

    /**
     * AJAX подгрузка информации для вывода списка отзывов
     */
    public function load(): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        return response()->json([
            'perPage' => self::PER_PAGE,
        ]);
    }

    /**
     * Постраничный вывод отзывов
     *
     * @throws PimException
     * @throws Exception
     */
    public function page(
        $customerId,
        CustomerService $customerService,
        ReviewService $reviewService,
        ProductService $productService,
        PublicEventService $publicEventService,
        FileService $fileService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $data = $this->validate(request(), [
            'page' => 'required|integer',
        ]);

        $userId = $customerService->customers(
            (new RestQuery())
                ->addFields(CustomerDto::entity(), 'user_id')
                ->setFilter('id', $customerId)
        )
            ->first()
            ->user_id;

        // Получаем отзывы
        $reviewsData = $reviewService->reviews(
            (new RestQuery())
                ->setFilter('user_id', $userId)
                ->setFilter('state', 'published')
                ->addSort('created_at', 'desc')
                ->pageNumber($data['page'], self::PER_PAGE)
        );

        $reviews = $reviewsData->reviews->groupBy('object_type');

        if (isset($reviews['product'])) {
            $productIds = $reviews['product']->pluck('object_id')->all();
            $products = $productService->products(
                (new RestQuery())
                    ->addFields(ProductDto::entity(), 'id', 'name')
                    ->setFilter('id', $productIds)
            )
                ->keyBy('id');
        } else {
            $products = [];
        }

        if (isset($reviews['masterclass'])) {
            $publicEventOfferIds = $reviews['masterclass']->pluck('object_id')->all();
            $publicEvents = $publicEventService
                ->query()
                ->include('sprints', 'sprints.ticketTypes', 'sprints.ticketTypes.offer')
                ->setFilter('offer_id', $publicEventOfferIds)
                ->get();

            foreach ($publicEvents as $publicEvent) {
                foreach ($publicEvent->sprints as $sprint) {
                    foreach ($sprint['ticketTypes'] as $ticketType) {
                        $publicEventOffers[$ticketType['offer']['id']] = [
                            'id' => $publicEvent->id,
                            'name' => $publicEvent->name,
                        ];
                    }
                }
            }
        } else {
            $publicEventOffers = [];
        }

        $fileIds = $reviewsData->reviews->pluck('files')->collapse()->all();
        if ($fileIds) {
            $files = $fileService
                ->getFiles($fileIds)
                ->keyBy('id')
                ->map(function (FileDto $file) {
                    return [
                        'name' => $file->original_name,
                        'url' => $file->absoluteUrl(),
                    ];
                });
        } else {
            $files = [];
        }

        $finalReviews = [];
        foreach ($reviewsData->reviews as $review) {
            switch ($review['object_type']) {
                case 'product':
                    if (isset($products[$review['object_id']])) {
                        $object = $products[$review['object_id']];
                    } else {
                        continue 2;
                    }
                    break;
                case 'masterclass':
                    if (isset($publicEventOffers[$review['object_id']])) {
                        $object = $publicEventOffers[$review['object_id']];
                    } else {
                        continue 2;
                    }
                    break;
                default:
                    $object = null;
                    break;
            }
            $finalReviews[] = [
                'id' => $review['id'],
                'object_type' => $review['object_type'],
                'object' => $object ?? [],
                'rating' => $review['rating'],
                'body' => $review['body'],
                'pros' => $review['pros'],
                'cons' => $review['cons'],
                'files' => collect($review['files'])->map(function ($fileId) use ($files) {
                    return $files[$fileId];
                }),
                'likes' => $review['likes'],
                'dislikes' => $review['dislikes'],
                'created_at' => $review['created_at'],
            ];
        }

        return response()->json([
            'reviews' => $finalReviews,
            'total' => $reviewsData->total,
        ]);
    }
}
