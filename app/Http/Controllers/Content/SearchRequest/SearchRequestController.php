<?php

namespace App\Http\Controllers\Content\SearchRequest;

use App\Http\Controllers\Controller;
use Cms\Dto\SearchRequestDto;
use Cms\Services\SearchRequestService\SearchRequestService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class SearchRequestController extends Controller
{
    /**
     * Список всех поисковых запросов
     * @param SearchRequestService $searchRequestService
     * @return mixed
     */
    public function list(SearchRequestService $searchRequestService)
    {
        $searchRequests = $searchRequestService->searchRequests(
            (new RestQuery())
                ->addSort('order_num')
        );

        $this->title = 'Поисковые запросы';

        return $this->render('Content/SearchRequests', [
            'iSearchRequests' => $searchRequests,
        ]);
    }

    /**
     * Добавить новый продуктовый ярлык
     * @param SearchRequestService $searchRequestService
     * @return JsonResponse
     */
    public function create(SearchRequestService $searchRequestService)
    {
        $data = $this->validate(request(), [
            'text' => 'required|string',
        ]);

        $searchRequest = new SearchRequestDto();
        $searchRequest->text = $data['text'];

        try {
            $newSearchRequest = $searchRequestService->create($searchRequest);
        } catch (\Exception $e) {
            if ($e->getCode() == 406) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 406);
            } else {
                throw $e;
            }
        }

        return response()->json([
            'search_request' => $newSearchRequest->toArray(),
        ], 201);

    }

    /**
     * Редактировать продуктовый ярлык
     * @param SearchRequestService $searchRequestService
     * @return Response|JsonResponse
     */
    public function update(SearchRequestService $searchRequestService)
    {
        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'text' => 'required|string',
        ]);

        $searchRequest = new SearchRequestDto();
        $searchRequest->id = $data['id'];
        $searchRequest->text = $data['text'];

        try {
            $searchRequestService->update($searchRequest);
        } catch (\Exception $e) {
            if ($e->getCode() == 406) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 406);
            } else {
                throw $e;
            }
        }

        return response('', 204);

    }

    /**
     * Удалить продуктовый ярлык
     * @param SearchRequestService $searchRequestService
     * @return Application|ResponseFactory|Response
     */
    public function delete(SearchRequestService $searchRequestService)
    {
        $data = $this->validate(request(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $searchRequestService->delete($data['ids']);

        return response('', 204);
    }

    /**
     * Изменить порядок продуктовых ярлыков
     * @param SearchRequestService $searchRequestService
     * @return Response
     */
    public function reorder(SearchRequestService $searchRequestService)
    {
        $data = $this->validate(request(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order_num' => 'required|integer',
        ]);

        $searchRequestService->reorder($data);

        return response('', 204);
    }
}