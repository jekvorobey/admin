<?php

namespace App\Http\Controllers\Communications;


use App\Http\Controllers\Controller;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationStatusService;

class StatusController extends Controller
{
    public function index(CommunicationService $communicationService, CommunicationStatusService $communicationStatusService)
    {
        $this->title = 'Статусы';
        $channels = $communicationService->channels()->keyBy('id');
        $statuses = $communicationStatusService->statuses()->keyBy('id');
        return $this->render('Communication/Status', [
            'iStatuses' => $statuses,
            'channels' => $channels,
        ]);
    }
}