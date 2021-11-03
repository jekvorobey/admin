<?php

namespace App\Http\Controllers\Content\SearchSynonym;

use App\Http\Controllers\Controller;
use Cms\Dto\SearchSynonymDto;
use Cms\Services\SearchSynonymService\SearchSynonymService;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

/**
 * Class SearchSynonymController
 * @package App\Http\Controllers\Content\SearchSynonym
 */
class SearchSynonymController extends Controller
{
    /**
     * Список всех поисковых запросов
     * @return mixed
     */
    public function list(SearchSynonymService $searchSynonymService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $query = $this->makeQuery();
        $this->title = 'Поисковые синонимы';

        return $this->render('Content/SearchSynonyms', [
            'iSearchSynonyms' => $this->loadItems($query, $searchSynonymService),
            'iPager' => $searchSynonymService->synonymsCount($query),
            'iCurrentPage' => $this->getPage(),
        ]);
    }

    public function page(SearchSynonymService $searchSynonymService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $query = $this->makeQuery();
        $data = [
            'searchSynonyms' => $this->loadItems($query, $searchSynonymService),
        ];
        if ($this->getPage() == 1) {
            $data['pager'] = $searchSynonymService->synonymsCount($query);
        }

        return response()->json($data);
    }

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    protected function makeQuery(): RestQuery
    {
        $query = new RestQuery();
        $page = $this->getPage();
        $query->pageNumber($page, 10);

        return $query->addSort('id', 'desc');
    }

    /**
     * @return Collection|SearchSynonymDto[]
     */
    protected function loadItems(RestQuery $query, SearchSynonymService $searchSynonymService)
    {
        $synonyms = $searchSynonymService->synonyms($query);

        return $synonyms->map(function (SearchSynonymDto $synonymDto) {
            return [
                'id' => $synonymDto->id,
                'synonyms' => $synonymDto->synonyms,
            ];
        });
    }

    /**
     * @throws \Exception
     */
    public function create(SearchSynonymService $searchSynonymService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'synonyms' => 'required|string',
        ]);

        $searchRequest = new SearchSynonymDto();
        $searchRequest->synonyms = $data['synonyms'];

        $id = $searchSynonymService->createSynonym($searchRequest);

        return response()->json([
            'search_synonym' => $searchSynonymService->synonym($id),
        ], 201);
    }

    /**
     * @return Application|Response|ResponseFactory
     * @throws \Exception
     */
    public function update(SearchSynonymService $searchSynonymService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'synonyms' => 'required|string',
        ]);

        $searchRequest = new SearchSynonymDto();
        $searchRequest->id = $data['id'];
        $searchRequest->synonyms = $data['synonyms'];

        $searchSynonymService->updateSynonym($data['id'], $searchRequest);

        return response('', 204);
    }

    /**
     * @return Application|ResponseFactory|Response
     */
    public function delete(SearchSynonymService $searchSynonymService)
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
        ]);

        // todo Сделать endpoint массового удаления в api
        foreach ($data['ids'] as $id) {
            $searchSynonymService->deleteSynonym($id);
        }

        return response('', 204);
    }
}
