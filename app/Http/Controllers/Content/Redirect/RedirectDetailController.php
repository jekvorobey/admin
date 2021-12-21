<?php

namespace App\Http\Controllers\Content\Redirect;

use App\Http\Controllers\Controller;
use App\Http\Requests\RedirectRequest;
use Cms\Core\CmsException;
use Cms\Dto\RedirectDto;
use Cms\Services\RedirectService\RedirectService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Http\JsonResponse;

class RedirectDetailController extends Controller
{
    /**
     * @throws CmsException
     */
    public function create(RedirectRequest $request, RedirectService $redirectService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $redirectService->createRedirect(new RedirectDto($request->validated()));

        return response()->json([], 204);
    }

    /**
     * @throws CmsException
     */
    public function update(int $id, RedirectRequest $request, RedirectService $redirectService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $redirectService->updateRedirect($id, new RedirectDto($request->validated()));

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
}
