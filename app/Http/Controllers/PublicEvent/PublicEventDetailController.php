<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Pim\Dto\PublicEvent\OrganizerDto;
use Pim\Dto\PublicEvent\PublicEventDto;
use Pim\Services\PublicEventOrganizerService\PublicEventOrganizerService;
use Pim\Services\PublicEventService\PublicEventService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicEventDetailController extends Controller
{
    public function index($event_id, PublicEventService $publicEventService)
    {
        $this->loadPublicEventsTypes = true;
        $publicEvent = $this->loadEvent($event_id, $publicEventService);
        if (!$publicEvent) {
            throw new NotFoundHttpException('public event not found');
        }
        
        return $this->render('PublicEvent/PublicEventDetail', [
            'iPublicEvent' => $publicEvent
        ]);
    }
    
    public function load($event_id, PublicEventService $publicEventService)
    {
        $publicEvent = $this->loadEvent($event_id, $publicEventService);
        if (!$publicEvent) {
            throw new NotFoundHttpException('public event not found');
        }
    
        return response()->json($publicEvent->toArray());
    }
    
    public function isCodeUnique(Request $request, PublicEventService $publicEventService)
    {
        $code = $request->get('code');
        if (!$code) {
            throw new BadRequestHttpException('code is required');
        }
        $id = $request->get('id');
        if (!$id) {
            throw new BadRequestHttpException('id is required');
        }
        
        $countData = $publicEventService
            ->query()
            ->setFilter('code', $code)
            ->setFilter('id', '!=', $id)
            ->count();
        return response()->json([
            'unique' => $countData['total'] == 0,
        ]);
    }
    
    public function save(Request $request, PublicEventService $publicEventService)
    {
        $id = $request->get('id');
        $data = $request->get('data');
        if (!$data) {
            throw new BadRequestHttpException('data is required');
        }
        if ($id) {
            $publicEventService->updatePublicEvent($id, new PublicEventDto($data));
        } else {
            $id = $publicEventService->createPublicEvent(new PublicEventDto($data));
        }
        return response()->json([
            'id' => $id
        ]);
    }
    
    public function availableOrganizers(PublicEventOrganizerService $publicEventOrganizerService)
    {
        /** @var Collection|OrganizerDto[] $organizers */
        $organizers = $publicEventOrganizerService->query()
            ->setFilter('owner_id', 0)
            ->addSort('name', 'asc')
            ->get();
        
        return response()->json($organizers);
    }
    
    protected function loadEvent(int $id, PublicEventService $publicEventService): ?PublicEventDto
    {
        try {
            return $publicEvent = $publicEventService
                ->query()
                ->setFilter('id', $id)
                ->withOrganizer()
                ->withActualSprint()
                ->withSprintTicketsCount()
                ->withPlace()
                ->get()
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}