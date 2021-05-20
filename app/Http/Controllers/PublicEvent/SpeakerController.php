<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\PublicEventSpeakerDto;
use Pim\Services\PublicEventSpeakerService\PublicEventSpeakerService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SpeakerController extends Controller
{
    public function list(Request $request, PublicEventSpeakerService $publicEventSpeakerService)
    {
        $page = $request->get('page', 1);
        [$total, $speakers] = $this->loadSpeakers($publicEventSpeakerService, $page);

        return $this->render('PublicEvent/SpeakerList', [
            'iSpeakers' => $speakers,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, PublicEventSpeakerService $publicEventSpeakerService)
    {
        $page = $request->get('page', 1);
        [$total, $speakers] = $this->loadSpeakers($publicEventSpeakerService, $page);

        return response()->json([
            'speakers' => $speakers,
            'total' => $total['total'],
        ]);
    }

    public function fullPage(PublicEventSpeakerService $publicEventSpeakerService)
    {
        $query = $publicEventSpeakerService->query();

        return response()->json([
            'speakers' => $publicEventSpeakerService->find($query),
        ]);
    }

    public function save(Request $request, PublicEventSpeakerService $publicEventSpeakerService)
    {
        $id = $request->get('id');
        $speaker = $request->get('speaker');

        if (!$speaker) {
            throw new BadRequestHttpException('speaker required');
        }

        $speaker = new PublicEventSpeakerDto($speaker);

        if ($id) {
            $publicEventSpeakerService->update($id, $speaker);
        } else {
            $publicEventSpeakerService->create($speaker);
        }

        return response()->json();
    }

    public function delete(Request $request, PublicEventSpeakerService $publicEventSpeakerService)
    {
        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventSpeakerService->delete($id);
        }

        return response()->json();
    }

    public function getByStage(int $stage_id, PublicEventSpeakerService $publicEventSpeakerService)
    {
        $speakers = $publicEventSpeakerService->getByStage($stage_id);

        return response()->json([
            'speakers' => $speakers['items'],
        ]);
    }

    public function attachStage(Request $request, int $stage_id, PublicEventSpeakerService $publicEventSpeakerService)
    {
        if (!$request->has('id')) {
            throw new BadRequestHttpException('id is required');
        }

        $publicEventSpeakerService->attachStage($request->input('id'), $stage_id);

        return response()->json(['status' => 'ok']);
    }

    public function detachStage(Request $request, int $stage_id, PublicEventSpeakerService $publicEventSpeakerService)
    {
        if (!$request->has('id')) {
            throw new BadRequestHttpException('id is required');
        }

        $publicEventSpeakerService->detachStage($request->input('id'), $stage_id);

        return response()->json(['status' => 'ok']);
    }

    /**
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadSpeakers(PublicEventSpeakerService $publicEventSpeakerService, $page): array
    {
        $query = $publicEventSpeakerService->query()->pageNumber($page, 10);

        $total = $publicEventSpeakerService->count($query);
        $speakers = $publicEventSpeakerService->find($query);
        return [$total, $speakers];
    }
}
