<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\PublicEventProfessionDto;
use Pim\Services\PublicEventProfessionService\PublicEventProfessionService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventProfessionController extends Controller
{
    /**
     * @throws GuzzleException
     */
    public function list(
        PublicEventProfessionService $publicEventPublicEventProfessionService,
        Client $client
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $response = $client->get(config('customer-lib.customerHost') . '/api/v1/activities');
        $response = collect(json_decode($response->getBody()->getContents(), true)['items'])->keyBy('id');

        $query = $publicEventPublicEventProfessionService->query();

        return response()->json([
            'professions' => $publicEventPublicEventProfessionService->find($query)->map(function ($item) use ($response) {
                $item['name'] = $response[$item['profession_id']]['name'] ?? null;
                return $item;
            })->all(),
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function names(Client $client): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $response = $client->get(config('customer-lib.customerHost') . '/api/v1/activities');

        return response()->json([
            'professions' => optional(json_decode($response->getBody()->getContents()))->items,
        ]);
    }

    public function page(
        Request $request,
        PublicEventProfessionService $publicEventPublicEventProfessionService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        [$total, $publicEventProfessions] = $this->loadPublicEventProfessions($publicEventPublicEventProfessionService, $page);

        return response()->json([
            'publicEventProfessions' => $publicEventProfessions,
            'total' => $total['total'],
        ]);
    }

    public function save(
        Request $request,
        PublicEventProfessionService $publicEventPublicEventProfessionService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');
        $publicEventProfession = $request->get('publicEventProfession');

        if (!$publicEventProfession) {
            throw new BadRequestHttpException('publicEventProfession required');
        }

        $publicEventProfession = new PublicEventProfessionDto($publicEventProfession);

        if ($id) {
            $publicEventPublicEventProfessionService->update($id, $publicEventProfession);
        } else {
            if (
                $publicEventPublicEventProfessionService->checkExistence(
                    $publicEventProfession->public_event_id,
                    $publicEventProfession->profession_id
                )['existence']
            ) {
                throw new BadRequestHttpException('Pair of public_event_id and profession_id already exists');
            }

            $publicEventPublicEventProfessionService->create($publicEventProfession);
        }

        return response()->json();
    }

    public function delete(
        Request $request,
        PublicEventProfessionService $publicEventPublicEventProfessionService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventPublicEventProfessionService->delete($id);
        }

        return response()->json();
    }

    /**
     * @throws GuzzleException
     */
    public function getByEvent(
        int $event_id,
        PublicEventProfessionService $publicEventPublicEventProfessionService,
        Client $client
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $response = $client->get(config('customer-lib.customerHost') . '/api/v1/activities');
        $response = collect(json_decode($response->getBody()->getContents(), true)['items'])->keyBy('id');

        $professions = $publicEventPublicEventProfessionService->getByEvent($event_id);

        return response()->json([
            'professions' => collect($professions['items'])->map(function ($item) use ($response) {
                $item['name'] = $response[$item['profession_id']]['name'] ?? null;
                return $item;
            })->all(),
        ]);
    }

    public function createByEvent(
        int $event_id,
        Request $request,
        PublicEventProfessionService $publicEventPublicEventProfessionService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $publicEventProfession = $request->get('publicEventProfession');

        if (!$publicEventProfession) {
            throw new BadRequestHttpException('publicEventProfession required');
        }

        $publicEventProfession = new PublicEventProfessionDto($publicEventProfession);

        $publicEventPublicEventProfessionService->createByEvent($event_id, $publicEventProfession);

        return response()->json();
    }

    private function loadPublicEventProfessions(
        PublicEventProfessionService $publicEventPublicEventProfessionService,
        $page
    ): array {
        $query = $publicEventPublicEventProfessionService->query()->pageNumber($page, 10);

        $total = $publicEventPublicEventProfessionService->count($query);
        $publicEventProfessions = $publicEventPublicEventProfessionService->find($query);

        return [$total, $publicEventProfessions];
    }
}
