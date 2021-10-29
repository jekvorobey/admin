<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Message\Dto\Communication\CommunicationTypeDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationTypeService;
use Illuminate\Http\JsonResponse;

class TypeController extends Controller
{
    public function index(
        CommunicationService $communicationService,
        CommunicationTypeService $communicationTypeService
    ) {
        $this->canView(BlockDto::ADMIN_BLOCK_COMMUNICATIONS);

        $this->title = 'Типы';
        $channels = $communicationService->channels()->keyBy('id');
        $types = $communicationTypeService->types()->keyBy('id');

        return $this->render('Communication/Type', [
            'iTypes' => $types,
            'channels' => $channels,
        ]);
    }

    public function save(CommunicationTypeService $communicationTypeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_COMMUNICATIONS);

        $rType = request('type');
        $type = new CommunicationTypeDto();
        $type->name = $rType['name'];
        $type->active = (bool) $rType['active'];
        $type->channel_id = $rType['channel_id'];

        if ($rType['id']) {
            $type->id = $rType['id'];
            $communicationTypeService->update($type);
        } else {
            $communicationTypeService->create($type);
        }
        $types = $communicationTypeService->types()->keyBy('id');

        return response()->json([
            'types' => $types,
        ]);
    }

    public function delete($id, CommunicationTypeService $communicationTypeService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_COMMUNICATIONS);

        $communicationTypeService->delete($id);
        $types = $communicationTypeService->types()->keyBy('id');

        return response()->json([
            'types' => $types,
        ]);
    }
}
