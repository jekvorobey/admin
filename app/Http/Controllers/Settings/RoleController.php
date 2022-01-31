<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlockPermissionRequest;
use App\Http\Requests\RoleRequest;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\PermissionDto;
use Greensight\CommonMsa\Dto\RoleDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\RoleService\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RoleController extends Controller
{
    /**
     * @return mixed
     */
    public function index(Request $request, RoleService $roleService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

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
        $this->canView(BlockDto::ADMIN_BLOCK_SETTINGS);

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
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

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
            'iBlockPermissions' => $roleService->roleBlocks($id),
            'options' => [
                'fronts' => Front::allFronts(),
                'blocks' => BlockDto::allBlocks(),
                'permissions' => PermissionDto::allPermissions(),
            ],
        ]);
    }

    /**
     * Добавить/обновить роль.
     */
    public function upsert(RoleRequest $request, RoleService $roleService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $data = $request->all();
        $newRole = new RoleDto($data);
        if (isset($data['id'])) {
            $roleService->update($data['id'], $newRole);
        } else {
            $roleService->create($newRole);
        }

        return response()->json(['role' => $newRole, 'iRoles' => $roleService->roles(), 'iPager' => $roleService->count()]);
    }

    /**
     * Удалить роль.
     */
    public function deleteRole(int $id, RoleService $roleService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $roleService->delete($id);

        return response()->json(['status' => 'ok', 'iRoles' => $roleService->roles(), 'iPager' => $roleService->count()]);
    }

    /**
     * Обновить блок у роли.
     */
    public function updateBlockPermissions(
        int $id,
        BlockPermissionRequest $request,
        RoleService $roleService
    ): JsonResponse {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_SETTINGS);

        $roleService->updateRoleBlocksPermissions($id, $request->blockPermissions);

        return response()->json([
            'blockPermissions' => $roleService->roleBlocks($id),
        ]);
    }

    /**
     * @throws ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'name' => 'string',
                'front_id' => Rule::in(array_keys(Front::allFronts())),
            ]
        );
    }

    /**
     * @throws ValidationException
     */
    protected function makeQuery(Request $request, bool $withDefaultFilter = false): RestQuery
    {
        $restQuery = new RestQuery();
        $restQuery->pageNumber($request->get('page', 1), 10);
        $filter = $this->getFilter($withDefaultFilter);
        foreach ($filter as $key => $value) {
            switch ($key) {
                case 'name':
                    if ($value) {
                        $restQuery->setFilter($key, 'like', "%{$value}%");
                    }
                    break;
                case 'front_id':
                    $restQuery->setFilter('front', $value);
                    break;
            }
        }

        return $restQuery;
    }

    protected function loadItems(RestQuery $query, RoleService $roleService)
    {
        return $roleService->roles($query);
    }
}
