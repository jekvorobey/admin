<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Exception;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Pim\Dto\PublicEvent\PublicEventDto;
use Pim\Dto\PublicEvent\PublicEventQuery;
use Pim\Dto\PublicEvent\PublicEventSprintStatus;
use Pim\Dto\PublicEvent\PublicEventStatus;
use Pim\Services\PublicEventService\PublicEventService;
use Pim\Services\ShoppilotService\ShoppilotService;
use Throwable;

class PublicEventListController extends Controller
{
    public const ITEM_PER_PAGE = 20;

    /**
     * @throws ValidationException
     * @throws Exception
     */
    public function list(Request $request, PublicEventService $publicEventService, ShoppilotService $shoppilotService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $this->loadPublicEventStatus = true;
        $this->loadPublicEventSprintStatus = true;

        $this->title = 'Мероприятия';

        $page = $request->get('page', 1);
        $pager = $publicEventService->countPublicEvents($this->makeRestQuery($publicEventService));
        $publicEvents = $this->loadPublicEvents($publicEventService, $page);

        $publicEventIds = $publicEvents->pluck('id')->all();
        if ($publicEventIds) {
            try {
                $shoppilotPublicEventsExist = $shoppilotService->groupsExist($publicEventIds);
                $publicEvents->transform(function ($publicEvent) use ($shoppilotPublicEventsExist) {
                    $publicEvent['shoppilotExist'] = $shoppilotPublicEventsExist[$publicEvent['id']];
                    return $publicEvent;
                });
            } catch (Throwable $e) {
            }
        }

        return $this->render('PublicEvent/PublicEventList', [
            'iPublicEvents' => $publicEvents,
            'iCurrentPage' => $page,
            'iPager' => $pager,
            'iTotal' => $this->loadTotalCount($publicEventService),
            'iFilter' => $this->getFilter(true),
            'options' => [
                'eventStatuses' => PublicEventStatus::all(),
                'sprintStatuses' => PublicEventSprintStatus::all(),
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function page(
        Request $request,
        PublicEventService $publicEventService,
        ShoppilotService $shoppilotService
    ): JsonResponse {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        $page = $request->get('page', 1);
        $publicEvents = $this->loadPublicEvents($publicEventService, $page);

        $publicEventIds = $publicEvents->pluck('id')->all();
        if ($publicEventIds) {
            try {
                $shoppilotPublicEventsExist = $shoppilotService->groupsExist($publicEventIds);
                $publicEvents->transform(function ($publicEvent) use ($shoppilotPublicEventsExist) {
                    $publicEvent['shoppilotExist'] = $shoppilotPublicEventsExist[$publicEvent['id']];
                    return $publicEvent;
                });
            } catch (Throwable $e) {
            }
        }

        return response()->json([
            'publicEvents' => $publicEvents,
            'total' => $this->loadTotalCount($publicEventService),
            'pager' => $publicEventService->countPublicEvents($this->makeRestQuery($publicEventService)),
        ]);
    }

    /**
     * @throws Exception
     */
    public function load(PublicEventService $publicEventService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_PUBLIC_EVENTS);

        return response()->json([
            'events' => $publicEventService->query()->get(),
        ]);
    }

    /**
     * @return Collection|PublicEventDto[]
     * @throws Exception
     */
    private function loadPublicEvents(PublicEventService $publicEventService, $page)
    {
        return $this->makeRestQuery($publicEventService)
            ->pageNumber($page, self::ITEM_PER_PAGE)
            ->withActualSprint()
            ->withPlace()
            ->withSprintTicketsCount()
            ->get()
            ->sortByDesc('created_at');
    }

    private function loadTotalCount(PublicEventService $publicEventService): int
    {
        $result = $publicEventService
            ->query()
            ->count();

        return $result['total'] ?? 0;
    }

    /**
     * @throws ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'name' => 'string',
                'status_id' => Rule::in(array_keys(PublicEventStatus::all())),
            ]
        );
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(
        PublicEventService $publicEventService,
        bool $withDefaultFilter = false
    ): PublicEventQuery {
        $restQuery = $publicEventService->query();

        $filter = $this->getFilter($withDefaultFilter);

        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'name':
                        if ($value) {
                            $restQuery->setFilter($key, 'like', "{$value}%");
                        }
                        break;
                    case 'status_id':
                        $restQuery->setFilter('status_id', $value);
                        break;
                }
            }
        }
        $restQuery->addSort('created_at', 'desc');

        return $restQuery;
    }
}
