<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Pim\Dto\PublicEvent\OrganizerDto;
use Pim\Dto\PublicEvent\PublicEventDto;
use Pim\Dto\PublicEvent\PublicEventMediaDto;
use Pim\Services\PublicEventOrganizerService\PublicEventOrganizerService;
use Pim\Services\PublicEventService\PublicEventService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicEventDetailController extends Controller
{
    public function index($event_id, PublicEventService $publicEventService)
    {
        $this->loadPublicEventTypes = true;
        $this->loadPublicEventMediaTypes = true;
        $this->loadPublicEventMediaCollections = true;
        
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
    
    public function addOrganizerById($event_id, Request $request, PublicEventService $publicEventService)
    {
        $organizerId = $request->get('organizerId');
        $publicEventService->addOrganizerById($event_id, $organizerId);
        return response()->json();
    }
    
    public function addOrganizerByValue($event_id, Request $request, PublicEventService $publicEventService, RequestInitiator $user)
    {
        $organizerData = $request->all();
        $organizerDto = new OrganizerDto($organizerData);
        $organizerDto->owner_id = $user->userId();
        $publicEventService->addOrganizerByValue($event_id, $organizerDto);
        return response()->json();
    }
    
    public function saveMedia(int $event_id, Request $request, PublicEventService $publicEventService)
    {
        $data = $this->validate($request, [
            'type' => ['required', Rule::in([
                PublicEventMediaDto::TYPE_IMAGE,
                PublicEventMediaDto::TYPE_VIDEO,
                PublicEventMediaDto::TYPE_YOUTUBE,
            ])],
            'collection' => ['required', Rule::in([
                PublicEventDto::MEDIA_CATALOG,
                PublicEventDto::MEDIA_DETAIL,
                PublicEventDto::MEDIA_GALLERY,
            ])],
            'value' => 'required',
            'oldMedia' => 'nullable|integer'
        ]);
        
        $oldMediaId = $data['oldMedia'] ?? null;
        if ($oldMediaId) {
            $publicEventService->delMedia($event_id, $oldMediaId);
        }
        $publicEventService->addMedia($event_id, $data['collection'], $data['type'], $data['value']);
        
        return response()->json();
    }
    
    public function deleteMedia(int $event_id, Request $request, PublicEventService $publicEventService)
    {
        $data = $this->validate($request, [
            'mediaId' => 'required'
        ]);
        $publicEventService->delMedia($event_id, $data['mediaId']);
        return response()->json();
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
                ->withMedia()
                ->get()
                ->first();
        } catch (\Exception $e) {
            return null;
        }
    }
}