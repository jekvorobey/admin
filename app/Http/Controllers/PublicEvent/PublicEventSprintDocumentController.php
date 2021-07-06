<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\PublicEventSprintDocumentDto;
use Pim\Services\PublicEventSprintDocumentService\PublicEventSprintDocumentService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PublicEventSprintDocumentController extends Controller
{
    public function list(
        Request $request,
        PublicEventSprintDocumentService $publicEventPublicEventSprintDocumentService
    ) {
        $sprintDocuments = $publicEventPublicEventSprintDocumentService->getBySprint($request->input('sprint_id'));

        return response()->json([
            'sprintDocuments' => $sprintDocuments['items'],
        ]);
    }

    public function page(
        Request $request,
        PublicEventSprintDocumentService $publicEventPublicEventSprintDocumentService
    ) {
        $page = $request->get('page', 1);
        [$total, $publicEventSprintDocuments] = $this->loadPublicEventSprintDocuments($publicEventPublicEventSprintDocumentService, $page);

        return response()->json([
            'publicEventSprintDocuments' => $publicEventSprintDocuments,
            'total' => $total['total'],
        ]);
    }

    public function save(
        Request $request,
        PublicEventSprintDocumentService $publicEventPublicEventSprintDocumentService
    ) {
        $id = $request->get('id');
        $publicEventSprintDocument = $request->get('sprintDocument');

        if (!$publicEventSprintDocument) {
            throw new BadRequestHttpException('publicEventSprintDocument required');
        }

        $publicEventSprintDocument = new PublicEventSprintDocumentDto($publicEventSprintDocument);

        if ($id) {
            $publicEventPublicEventSprintDocumentService->update($id, $publicEventSprintDocument);
        } else {
            $publicEventPublicEventSprintDocumentService->create($publicEventSprintDocument);
        }

        return response()->json();
    }

    public function delete(
        Request $request,
        PublicEventSprintDocumentService $publicEventPublicEventSprintDocumentService
    ) {
        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $publicEventPublicEventSprintDocumentService->delete($id);
        }

        return response()->json();
    }

    public function getBySprint(
        int $sprint_id,
        PublicEventSprintDocumentService $publicEventPublicEventSprintDocumentService
    ) {
        $sprintDocuments = $publicEventPublicEventSprintDocumentService->getBySprint($sprint_id);

        return response()->json([
            'sprintDocuments' => $sprintDocuments,
        ]);
    }

    /**
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadPublicEventSprintDocuments(
        PublicEventSprintDocumentService $publicEventPublicEventSprintDocumentService,
        $page
    ): array {
        $query = $publicEventPublicEventSprintDocumentService->query()->pageNumber($page, 10);

        $total = $publicEventPublicEventSprintDocumentService->count($query);
        $publicEventSprintDocuments = $publicEventPublicEventSprintDocumentService->find($query);
        return [$total, $publicEventSprintDocuments];
    }
}
