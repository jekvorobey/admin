<?php

namespace App\Http\Controllers\Content\Redirect;

use App\Http\Controllers\Controller;
use Cms\Core\CmsException;
use Cms\Dto\RedirectDto;
use Cms\Services\RedirectService\RedirectService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RedirectDetailController extends Controller
{
    /**
     * @param int $id
     * @return mixed
     * @throws CmsException
     */
    public function updatePage($id, RedirectService $redirectService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $redirect = $this->getRedirect($id, $redirectService);

        return $this->render('Content/RedirectDetail', [
            'iRedirect' => $redirect,
            'options' => [],
        ]);
    }

    /**
     * @return mixed
     * @throws CmsException
     */
    public function createPage()
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        return $this->render('Content/RedirectDetail', [
            'iRedirect' => [],
            'options' => [],
        ]);
    }

    /**
     * @throws CmsException
     */
    public function create(Request $request, RedirectService $redirectService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'from' => 'string|required',
            'to' => 'string|required',
        ]);

        $redirectService->createRedirect(new RedirectDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function update(int $id, Request $request, RedirectService $redirectService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $validatedData = $request->validate([
            'from' => 'string|required',
            'to' => 'string|required',
        ]);

        $validatedData['id'] = $id;

        $redirectService->updateRedirect($validatedData['id'], new RedirectDto($validatedData));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function delete(int $id, RedirectService $redirectService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $redirectService->deleteRedirect($id);

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    private function getRedirect(int $id, RedirectService $redirectService): ?RedirectDto
    {
        $query = $redirectService->newQuery()->setFilter('id', $id);
        return $redirectService->redirects($query)->first();
    }
}
