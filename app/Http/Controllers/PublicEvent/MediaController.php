<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\MediaDto;
use Pim\Services\PublicEventMediaService\PublicEventMediaService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MediaController extends Controller
{
    public function fullList(PublicEventMediaService $publicEventMediaService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $query = $publicEventMediaService->query();

        return response()->json([
            'medias' => $publicEventMediaService->find($query),
        ]);
    }

    public function save(Request $request, PublicEventMediaService $publicEventMediaService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');
        $media = $request->get('media');

        if (!$media) {
            throw new BadRequestHttpException('media required');
        }

        $media = new MediaDto($media);

        if ($id) {
            $publicEventMediaService->update($id, $media);
        } else {
            $publicEventMediaService->create($media);
        }

        return response()->json();
    }

    public function delete(Request $request, PublicEventMediaService $publicEventMediaService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');

        if (!$id) {
            throw new BadRequestHttpException('id required');
        }

        $publicEventMediaService->delete($id);

        return response()->json();
    }
}
