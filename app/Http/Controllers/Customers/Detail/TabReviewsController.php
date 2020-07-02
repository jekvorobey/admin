<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Pim\Dto\Product\ProductDto;
use Pim\Services\ProductService\ProductService;
use Pim\Services\ReviewService\ReviewService;
use Illuminate\Http\JsonResponse;

class TabReviewsController extends Controller
{
    private const PER_PAGE = 10;

    /**
     * AJAX подгрузка информации для вывода списка отзывов
     *
     * @return JsonResponse
     */
    public function load() {
        return response()->json([
            'perPage' => self::PER_PAGE,
        ]);
    }

    public function page(
        $customerId,
        CustomerService $customerService,
        UserService $userService,
        ReviewService $reviewService,
        ProductService $productService,
        FileService $fileService
    ) {
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

        $userEmail = $userService->users(
            (new RestQuery())
                ->include('profile')
                ->addFields(UserDto::entity(), 'id')
                ->addFields('profile', 'id', 'user_id', 'email')
                ->setFilter('id', $userId)
        )
            ->first()
            ->email;

        // Получаем отзывы
        $reviews = $reviewService->reviews(
            (new RestQuery())
                ->setFilter('author_email', $userEmail)
                ->setFilter('state', 'published')
                ->addSort('created_at', 'desc')
                ->pageNumber($data['page'], self::PER_PAGE)
        );

        $productIds = $reviews->reviews->pluck('product_id')->all();
        $products = $productService->products(
            (new RestQuery())
                ->addFields(ProductDto::entity(), 'id', 'name')
                ->setFilter('id', $productIds)
        )
            ->keyBy('id');

        $fileIds = $reviews->reviews->pluck('files')->collapse()->all();
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

        return response()->json([
            'reviews' => $reviews->reviews->map(function ($review) use ($products, $files) {
                return [
                    'id' => $review['id'],
                    'product' => $products[$review['product_id']],
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
            }),
            'total' => $reviews->total,
        ]);
    }
}