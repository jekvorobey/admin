<?php

namespace App\Http\Controllers\Customers\Detail;

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

class TabReviewsController
{
    public function load(
        $customerId,
        CustomerService $customerService,
        UserService $userService,
        ReviewService $reviewService,
        ProductService $productService,
        FileService $fileService
    ) {
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
                ->pageNumber(1, 10)
        );

        $userIds = $reviews->pluck('user_id')->all();
        $users = $userService->users(
            (new RestQuery())
                ->include('profile')
                ->addFields(UserDto::entity(), 'id')
                ->addFields('profile', 'id', 'user_id', 'first_name', 'last_name', 'middle_name', 'phone')
                ->setFilter('id', $userIds)
        )
            ->keyBy('id');
        $customers = $customerService->customers(
            (new RestQuery())
                ->addFields(CustomerDto::entity(), 'id', 'user_id')
                ->setFilter('user_id', $userIds)
        )
            ->keyBy('user_id')
            ->map(function (CustomerDto $customer) use ($users) {
                return [
                    'id' => $customer->id,
                    'full_name' => $users[$customer->user_id]->getTitle(),
                ];
            });

        $productIds = $reviews->pluck('product_id')->all();
        $products = $productService->products(
            (new RestQuery())
                ->addFields(ProductDto::entity(), 'id', 'name')
                ->setFilter('id', $productIds)
        )
            ->keyBy('id');

        $fileIds = $reviews->pluck('files')->collapse()->all();
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
            'reviews' => $reviews->map(function ($review) use ($customers, $products, $files) {
                return [
                    'id' => $review['id'],
                    'user' => $customers[$review['user_id']],
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
        ]);
    }
}