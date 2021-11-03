<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Cms\Dto\ContentNewsletterDto;
use Cms\Services\ContentNewsletterService\ContentNewsletterService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Customer\Dto\CustomerNewsletterDto;
use Greensight\Customer\Services\CustomerNewsletterService\CustomerNewsletterService;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

/**
 * Class TabNewsletterController
 * @package App\Http\Controllers\Customers\Detail
 */
class TabNewsletterController extends Controller
{
    /**
     * Получить информацию о новостных подписках пользователя
     */
    public function load(
        $customerId,
        ContentNewsletterService $contentNewsletterService,
        CustomerNewsletterService $customerNewsletterService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        /** @var ContentNewsletterDto $topics */
        $topics = $contentNewsletterService->getTopics();
        /** @var CustomerNewsletterDto $subscriptions */
        $subscriptions = $customerNewsletterService->getNewsletterInfo($customerId)->first();

        $customer_topics = $subscriptions ? $subscriptions->topics : null;
        $customer_periodicity = $subscriptions ? $subscriptions->periodicity : null;
        $customer_channels = $subscriptions ? $subscriptions->channels : null;

        return response()->json([
            'topics' => $topics,
            'periods' => CustomerNewsletterDto::periods(),
            'channels' => CustomerNewsletterDto::channels(),
            'customer' => [
                'topics' => $customer_topics,
                'periodicity' => $customer_periodicity,
                'channels' => $customer_channels,
            ],
        ]);
    }

    /**
     * Редактировать параметры новостной подписки у пользователя
     * @return Application|ResponseFactory|Response
     */
    public function edit(
        $customerId,
        CustomerNewsletterService $customerNewsletterService,
        ContentNewsletterService $contentNewsletterService
    ) {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $topics = $contentNewsletterService->getTopics()
            ->keyBy('id')
            ->keys()
            ->toArray();

        $data = $this->validate(request(), [
            'topics.*' => ['nullable', 'integer', Rule::in($topics)],
            'periodicity' => [
                'required',
                'integer',
                Rule::in(
                    array_keys(CustomerNewsletterDto::periods())
                ),
            ],
            'channels.*' => [
                'nullable',
                'integer',
                Rule::in(
                    array_keys(CustomerNewsletterDto::periods())
                ),
            ],
        ]);

        if (isset($data['topics'])) {
            $data['topics'] = array_map(function ($item) {
                return (int) $item;
            }, $data['topics']);
        }

        if (isset($data['channels'])) {
            $data['channels'] = array_map(function ($item) {
                return (int) $item;
            }, $data['channels']);
        }

        $customerNewsletterService->editNewsletterInfo($customerId, $data);

        return response('', 204);
    }
}
