<?php

namespace App\Http\Controllers\Content\SearchRequest;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\SearchRequestDto;
use Cms\Services\SearchRequestService\SearchRequestService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class SearchRequestController extends Controller
{
    /**
     * Список всех поисковых запросов
     * @return mixed
     * @throws CmsException
     */
    public function list(SearchRequestService $searchRequestService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

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
     * @throws CmsException
     * @throws Throwable
     */
    public function create(SearchRequestService $searchRequestService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'text' => 'required|string',
        ]);

        $searchRequest = new SearchRequestDto();
        $searchRequest->text = $data['text'];

        try {
            $newSearchRequest = $searchRequestService->create($searchRequest);
        } catch (Throwable $e) {
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
     * @throws CmsException
     * @throws Throwable
     */
    public function update(SearchRequestService $searchRequestService): Response|JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'text' => 'required|string',
        ]);

        $searchRequest = new SearchRequestDto();
        $searchRequest->id = $data['id'];
        $searchRequest->text = $data['text'];

        try {
            $searchRequestService->update($searchRequest);
        } catch (Throwable $e) {
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
     * @throws CmsException
     */
    public function delete(SearchRequestService $searchRequestService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        $searchRequestService->delete($data['ids']);

        return response('', 204);
    }

    /**
     * Изменить порядок продуктовых ярлыков
     */
    public function reorder(SearchRequestService $searchRequestService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'items' => 'required|array',
            'items.*.id' => 'required|integer',
            'items.*.order_num' => 'required|integer',
        ]);

        $searchRequestService->reorder($data);

        return response('', 204);
    }
}
