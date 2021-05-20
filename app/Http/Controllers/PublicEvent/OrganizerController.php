<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\OrganizerDto;
use Pim\Services\PublicEventOrganizerService\PublicEventOrganizerService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OrganizerController extends Controller
{
    public function list(Request $request, PublicEventOrganizerService $publicEventOrganizerService)
    {
        $page = $request->get('page', 1);
        [$total, $organizers] = $this->loadOrganizers($publicEventOrganizerService, $page);

        return $this->render('PublicEvent/OrganizerList', [
            'iMerchants' => $this->getMerchants(),
            'iOrganizers' => $organizers,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }

    public function page(Request $request, PublicEventOrganizerService $publicEventOrganizerService)
    {
        $page = $request->get('page', 1);
        [$total, $organizers] = $this->loadOrganizers($publicEventOrganizerService, $page);

        return response()->json([
            'organizers' => $organizers,
            'total' => $total['total'],
        ]);
    }

    public function save(Request $request, PublicEventOrganizerService $publicEventOrganizerService)
    {
        $id = $request->get('id');
        $organizer = $request->get('organizer');

        if (!$organizer) {
            throw new BadRequestHttpException('organizer required');
        }

        $organizer = new OrganizerDto($organizer);

        if ($id) {
            $publicEventOrganizerService->update($id, $organizer);
        } else {
            $publicEventOrganizerService->create($organizer);
        }

        return response()->json();
    }

    public function delete(Request $request, PublicEventOrganizerService $publicEventOrganizerService)
    {
        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventOrganizerService->delete($id);
        }

        return response()->json();
    }

    /**
     * @param PublicEventOrganizerService $publicEventOrganizerService
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadOrganizers(PublicEventOrganizerService $publicEventOrganizerService, $page): array
    {
        $query = $publicEventOrganizerService->query()->pageNumber($page, 10);

        $total = $publicEventOrganizerService->count($query);
        $organizers = $publicEventOrganizerService->find($query);
        return [$total, $organizers];
    }
}
