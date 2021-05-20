<?php

namespace App\Http\Controllers\Communications;

use App\Http\Controllers\Controller;
use Greensight\Message\Dto\Communication\CommunicationStatusDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;

class StatusController extends Controller
{
    public function index(
        CommunicationService $communicationService,
        CommunicationStatusService $communicationStatusService
    ) {
        $this->title = 'Статусы';
        $channels = $communicationService->channels()->keyBy('id');
        $statuses = $communicationStatusService->statuses()->keyBy('id');

        return $this->render('Communication/Status', [
            'iStatuses' => $statuses,
            'channels' => $channels,
        ]);
    }

    public function save(CommunicationStatusService $communicationStatusService)
    {
        $rStatus = request('status');
        $status = new CommunicationStatusDto();
        $status->name = $rStatus['name'];
        $status->active = (bool)$rStatus['active'];
        $status->default = (bool)$rStatus['default'];
        $status->channel_id = $rStatus['channel_id'];

        if ($rStatus['id']) {
            $status->id = $rStatus['id'];
            $communicationStatusService->update($status);
        } else {
            $communicationStatusService->create($status);
        }

        $statuses = $communicationStatusService->statuses()->keyBy('id');
        return response()->json([
            'statuses' => $statuses,
        ]);
    }

    public function delete($id, CommunicationStatusService $communicationStatusService)
    {
        $communicationStatusService->delete($id);

        $statuses = $communicationStatusService->statuses()->keyBy('id');
        return response()->json([
            'statuses' => $statuses,
        ]);
    }
}
