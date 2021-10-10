<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlockPermissionRequest;
use App\Http\Requests\RoleRequest;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RolesController extends Controller
{
    /**
     * @return mixed
     */
    public function index(Request $request, RoleService $roleService)
    {
        $this->title = 'Список ролей';

        $query = $this->makeQuery($request);

        return $this->render('Settings/RoleList', [
            'iRoles' => $this->loadItems($query, $roleService),
            'iPager' => $roleService->count($query),
            'iCurrentPage' => $request->get('page', 1),
            'options' => [
                'fronts' => Front::allFronts(),
            ],
        ]);
    }

    public function page(Request $request, RoleService $roleService): JsonResponse
    {
        $query = $this->makeQuery($request);
        $data = [
            'items' => $this->loadItems($query, $roleService),
        ];
        if ($request->get('page', 1) == 1) {
            $data['pager'] = $roleService->count($query);
        }

        return response()->json($data);
    }

    /**
     * @return mixed
     */
    public function detail(int $id, RoleService $roleService)
    {
        $roleQuery = new RestQuery();
        $roleQuery->setFilter('id', $id);
        /** @var RoleDto $role */
        $role = $roleService->roles($roleQuery)->first();

        if (!$role) {
            throw new NotFoundHttpException('role not found');
        }

        $this->title = "Роль - {$role->name}";

        return $this->render('Settings/RoleDetail', [
            'iRole' => $role,
            'options' => [
                'fronts' => Front::allFronts(),
                'blocks' => BlockDto::allBlocks(),
                'permissions' => PermissionDto::allPermissions(),
            ],
        ]);
    }

    public function saveRole(RoleRequest $request, RoleService $roleService): JsonResponse
    {
        $newRole = new RoleDto([
            'id' => $request->name,
            'name' => $request->name,
            'front' => $request->front,
        ]);
        $roleService->upsert($newRole);

        return response()->json([]);
    }

    public function deleteRole(int $id, RoleService $roleService): JsonResponse
    {
        $roleService->deleteRole($id);

        return response()->json(['status' => 'ok']);
    }

    public function addBlock(int $id, BlockPermissionRequest $request, RoleService $roleService): JsonResponse
    {
        $roleService->addRoles($id, $request->block_id, $request->permission_id);

        return response()->json([
            'blocks' => $roleService->roleBlocks($id),
        ]);
    }

    public function deleteBlock(int $id, BlockPermissionRequest $request, RoleService $roleService): JsonResponse
    {
        $roleService->deleteRole($request->block_id);
        return response()->json([
            'blocks' => $roleService->roleBlocks($id),
        ]);
    }

    protected function makeQuery(Request $request): RestQuery
    {
        $restQuery = new RestQuery();
        $restQuery->pageNumber($request->get('page', 1), 10);

        return $restQuery;
    }

    protected function loadItems(RestQuery $query, RoleService $roleService)
    {
        return $roleService->roles($query);
    }
}
