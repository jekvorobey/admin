<?php

namespace App\Http\Controllers\Content\SearchSynonym;

use App\Http\Controllers\Controller;
use Cms\Dto\SearchSynonymDto;
use Cms\Services\SearchSynonymService\SearchSynonymService;
use Greensight\CommonMsa\Rest\RestQuery;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
     * @param SearchSynonymService $searchSynonymService
     * @return mixed
     */
    public function list(SearchSynonymService $searchSynonymService)
    {
        $query = $this->makeQuery();
        $this->title = 'Поисковые синонимы';

        return $this->render('Content/SearchSynonyms', [
            'iSearchSynonyms' => $this->loadItems($query, $searchSynonymService),
            'iPager' => $searchSynonymService->synonymsCount($query),
            'iCurrentPage' => $this->getPage(),
        ]);
    }

    /**
     * @param  Request  $request
     * @param  SearchSynonymService  $searchSynonymService
     * @return JsonResponse
     */
    public function page(SearchSynonymService $searchSynonymService)
    {
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

    /**
     * @param Request $request
     * @return RestQuery
     */
    protected function makeQuery()
    {
        $query = new RestQuery();
        $page = $this->getPage();
        $query->pageNumber($page, 10);

        return $query->addSort('id', 'desc');
    }

    /**
     * @param  RestQuery  $query
     * @param  SearchSynonymService  $searchSynonymService
     * @return Collection|SearchSynonymDto[]
     */
    protected function loadItems(
        RestQuery $query,
        SearchSynonymService $searchSynonymService
    ) {
        $synonyms = $searchSynonymService->synonyms($query);

        return $synonyms->map(function (SearchSynonymDto $synonymDto) {
            return [
                'id' => $synonymDto->id,
                'synonyms' => $synonymDto->synonyms,
            ];
        });
    }

    /**
     * @param  SearchSynonymService  $searchSynonymService
     * @return JsonResponse
     * @throws \Exception
     */
    public function create(SearchSynonymService $searchSynonymService)
    {
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
     * @param  SearchSynonymService  $searchSynonymService
     * @return Application|ResponseFactory|JsonResponse|Response
     * @throws \Exception
     */
    public function update(SearchSynonymService $searchSynonymService)
    {
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
     * @param  SearchSynonymService  $searchSynonymService
     * @return Application|ResponseFactory|Response
     */
    public function delete(SearchSynonymService $searchSynonymService)
    {
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
