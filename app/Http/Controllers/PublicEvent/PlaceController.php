<?php

namespace App\Http\Controllers\PublicEvent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Pim\Core\PimException;
use Pim\Dto\PublicEvent\PlaceDto;
use Pim\Services\PublicEventPlaceService\PublicEventPlaceService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class PlaceController extends Controller
{
    public function list(Request $request, PublicEventPlaceService $publicEventPlaceService)
    {
        $page = $request->get('page', 1);
        [$total, $places]= $this->loadPlaces($publicEventPlaceService, $page);
        
        return $this->render('PublicEvent/PlaceList', [
            'iPlaces' => $places,
            'iTotal' => $total['total'],
            'iCurrentPage' => $page,
        ]);
    }
    
    public function page(Request $request, PublicEventPlaceService $publicEventPlaceService)
    {
        $page = $request->get('page', 1);
        [$total, $places] = $this->loadPlaces($publicEventPlaceService, $page);
        
        return response()->json([
            'places' => $places,
            'total' => $total['total'],
        ]);
    }

    public function fullList(PublicEventPlaceService $publicEventPlaceService)
    {
        $query = $publicEventPlaceService->query();

        return response()->json([
            'places' => $publicEventPlaceService->find($query),
        ]);
    }
    
    public function save(Request $request, PublicEventPlaceService $publicEventPlaceService)
    {
        $id = $request->get('id');
        $place = $request->get('place');
        
        if (!$place) {
            throw new BadRequestHttpException('place required');
        }
        
        $place = new PlaceDto($place);
        
        if ($id) {
            $publicEventPlaceService->update($id, $place);
        } else {
            $publicEventPlaceService->create($place);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, PublicEventPlaceService $publicEventPlaceService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $publicEventPlaceService->delete($id);
        }
        
        return response()->json();
    }
    
    /**
     * @param PublicEventPlaceService $publicEventPlaceService
     * @param $page
     * @return array
     * @throws PimException
     */
    private function loadPlaces(PublicEventPlaceService $publicEventPlaceService, $page): array
    {
        $query = $publicEventPlaceService->query()->pageNumber($page, 10);
        
        $total = $publicEventPlaceService->count($query);
        $places = $publicEventPlaceService->find($query);
        return [$total, $places];
    }
}