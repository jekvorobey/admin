<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\MediaDto;
use Pim\Services\PublicEventMediaService\PublicEventMediaService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MediaController extends Controller
{
    public function fullList(PublicEventMediaService $publicEventMediaService)
    {
        $query = $publicEventMediaService->query();

        return response()->json([
            'medias' => $publicEventMediaService->find($query),
        ]);
    }

    public function save(Request $request, PublicEventMediaService $publicEventMediaService)
    {
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

    public function delete(Request $request, PublicEventMediaService $publicEventMediaService)
    {
        $id = $request->get('id');

        if (!$id) {
            throw new BadRequestHttpException('id required');
        }

        $publicEventMediaService->delete($id);

        return response()->json();
    }
}
