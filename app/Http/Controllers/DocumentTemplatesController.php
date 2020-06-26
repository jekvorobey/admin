<?php

namespace App\Http\Controllers;

use Greensight\Oms\Dto\Document\DocumentTemplateDto;
use Greensight\Oms\Services\DocumentTemplateService\DocumentTemplateService;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DocumentTemplatesController
 * @package App\Http\Controllers
 */
class DocumentTemplatesController extends Controller
{
    /**
     * Получить шаблон "Акт-претензия по отправлению"
     * @param  DocumentTemplateService  $documentTemplateService
     * @return StreamedResponse
     */
    public function claimAct(DocumentTemplateService $documentTemplateService): StreamedResponse
    {
        return $this->getDocumentResponse($documentTemplateService->claimAct());
    }

    /**
     * Получить шаблон "Акт приема-передачи по отправлению/грузу"
     * @param  DocumentTemplateService  $documentTemplateService
     * @return StreamedResponse
     */
    public function acceptanceAct(DocumentTemplateService $documentTemplateService): StreamedResponse
    {
        return $this->getDocumentResponse($documentTemplateService->acceptanceAct());
    }

    /**
     * Получить шаблон "Опись отправления заказа"
     * @param  DocumentTemplateService  $documentTemplateService
     * @return StreamedResponse
     */
    public function inventory(DocumentTemplateService $documentTemplateService): StreamedResponse
    {
        return $this->getDocumentResponse($documentTemplateService->inventory());
    }

    /**
     * Получить шаблон "Карточка сборки отправления"
     * @param  DocumentTemplateService  $documentTemplateService
     * @return StreamedResponse
     */
    public function assemblingCard(DocumentTemplateService $documentTemplateService): StreamedResponse
    {
        return $this->getDocumentResponse($documentTemplateService->assemblingCard());
    }

    /**
     * @param  DocumentTemplateDto  $documentTemplateDto
     * @return StreamedResponse
     */
    protected function getDocumentResponse(DocumentTemplateDto $documentTemplateDto): StreamedResponse
    {
        return response()->streamDownload(function () use ($documentTemplateDto) {
            echo file_get_contents($documentTemplateDto->absolute_url);
        }, $documentTemplateDto->original_name);
    }
}