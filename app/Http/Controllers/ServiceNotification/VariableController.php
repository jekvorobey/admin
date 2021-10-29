<?php

namespace App\Http\Controllers\ServiceNotification;

use App\Http\Controllers\Controller;
use Greensight\Message\Dto\ServiceNotification\VariableDto;
use Greensight\Message\Services\VariableService\VariableService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class VariableController extends Controller
{
    public function page(Request $request, VariableService $VariableService)
    {
        $page = $request->input('page') ?? 1;

        $variables = $VariableService
            ->variables($VariableService->newQuery()->pageNumber($page, 10)->include('templates'))
            ->map(function ($variable) {
                $variable['channels'] = collect($variable->templates)->pluck('channel');
                return $variable;
            });

        return $this->render('Communication/Variable', [
            'iVariables' => $variables,
            'iTotal' => $VariableService->count($VariableService->newQuery())['total'],
            'iCurrentPage' => $request->input('page'),
        ]);
    }

    public function list(Request $request, VariableService $VariableService): JsonResponse
    {
        $page = $request->input('page') ?? 1;

        $variables = $VariableService
            ->variables($VariableService->newQuery()->pageNumber($page, 10)->include('templates'))
            ->map(function ($variable) {
                $variable['channels'] = collect($variable->templates)->pluck('channel');
                return $variable;
            });

        return response()->json([
            'variables' => $variables,
            'total' => $VariableService->count($VariableService->newQuery())['total'],
        ]);
    }

    public function save(Request $request, VariableService $VariableService): JsonResponse
    {
        $id = $request->get('id');
        $variable = $request->get('variable');

        if (!$variable) {
            throw new BadRequestHttpException('variable required');
        }

        $variableDto = new VariableDto($variable);

        if ($id) {
            $VariableService->update($id, $variableDto);
        } else {
            $VariableService->create($variableDto);
        }

        return response()->json();
    }

    public function delete(Request $request, VariableService $VariableService): JsonResponse
    {
        $ids = $request->get('ids');

        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }

        foreach ($ids as $id) {
            $VariableService->delete($id);
        }

        return response()->json();
    }
}
