<?php

namespace App\Http\Controllers\ServiceNotification;

use App\Http\Controllers\Controller;
use Greensight\Message\Dto\ServiceNotification\TemplateDto;
use Greensight\Message\Services\TemplateService\TemplateService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class TemplateController extends Controller
{
    public function list(Request $request, TemplateService $templateService)
    {
        return response()->json([
            'templates' => $templateService->templates($templateService->newQuery()->pageNumber($request->input('page', 1), 10))
        ]);
    }
    
    public function save(Request $request, TemplateService $templateService)
    {
        $id = $request->get('id');
        $template = $request->get('template');
        
        if (!$template) {
            throw new BadRequestHttpException('template required');
        }
        
        $templateDto = new TemplateDto($template);
        
        if ($id) {
            $templateService->update($id, $templateDto);
        } else {
            $templateService->create($templateDto);
        }
        
        return response()->json();
    }
    
    public function delete(Request $request, TemplateService $templateService)
    {
        $ids = $request->get('ids');
        
        if (!$ids || !is_array($ids)) {
            throw new BadRequestHttpException('ids required');
        }
        
        foreach($ids as $id) {
            $templateService->delete($id);
        }
        
        return response()->json();
    }
}