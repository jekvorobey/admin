<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\Request;
use Pim\Dto\PublicEvent\PublicEventSprintSellStatusDto;
use Pim\Services\PublicEventSprintSellStatusService\PublicEventSprintSellStatusService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Illuminate\Http\JsonResponse;

class PublicEventSprintSellStatusController extends Controller
{
    public function save(
        Request $request,
        PublicEventSprintSellStatusService $publicEventPublicEventTicketTypeService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $id = $request->get('id');
        $publicEventSprintSellStatus = ['status_id' => $request->input('status_id'), 'sprint_id' => $request->input('sprint_id')];

        if (!$publicEventSprintSellStatus) {
            throw new BadRequestHttpException('sellStatus required');
        }

        $publicEventSprintSellStatus = new PublicEventSprintSellStatusDto($publicEventSprintSellStatus);

        if ($id) {
            $publicEventPublicEventTicketTypeService->update($id, $publicEventSprintSellStatus);
        } else {
            $publicEventPublicEventTicketTypeService->create($publicEventSprintSellStatus);
        }

        return response()->json();
    }

    public function delete(
        Request $request,
        PublicEventSprintSellStatusService $publicEventPublicEventTicketTypeService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $sprint_id = $request->input('sprint_id');
        $status_id = $request->input('status_id');

        $model = $publicEventPublicEventTicketTypeService->find(
            $publicEventPublicEventTicketTypeService
                ->query()
                ->setFilter('sprint_id', '=', $sprint_id)
                ->setFilter('status_id', '=', $status_id)
        )->first()->id;

        $publicEventPublicEventTicketTypeService->delete($model);

        return response()->json();
    }
}
