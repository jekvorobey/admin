<?php

namespace App\Http\Controllers\Customers\Detail;


use App\Http\Controllers\Controller;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterFactory;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerDocumentDto;
use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Message\Dto\Mail\SendReferralDocumentMailDto;
use Greensight\Message\Services\MailService;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class TabDocumentController
 * @package App\Http\Controllers\Customers\Detail
 */
class TabDocumentController extends Controller
{
    /**
     * @param int $customerId
     * @param CustomerService $customerService
     * @param FileService $fileService
     * @return \Illuminate\Http\JsonResponse
     */
    public function load(int $customerId, CustomerService $customerService, FileService $fileService)
    {
        $documentDto = new CustomerDocumentDto();
        $documents = $customerService->documents($customerId);
        $files = [];
        if ($documents) {
            $files = $fileService->getFiles($documents->pluck('file_id')->all())->keyBy('id');
        }

        return response()->json([
            'documents' => $documents->map(function (CustomerDocumentDto $document) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($document->file_id);
                if (!$file) {
                    return false;
                }
                return [
                    'id' => $document->id,
                    'typeId' => $document->type,
                    'period_since' => $document->period_since,
                    'period_to' => $document->period_to,
                    'date' => $document->updated_at,
                    'amount_reward' => $document->amount_reward,
                    'statusId' => $document->status,
                    'url' => $file->url,
                    'name' => $file->original_name,
                ];
            })->filter(),
            'statuses' => $documentDto->statusesNames(),
            'types' => $documentDto->typesNames(),
        ]);
    }

    /**
     * @param int $customerId
     * @param CustomerService $customerService
     * @param FileService $fileService
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function export(int $customerId, CustomerService $customerService, FileService $fileService)
    {
        $this->validate(request(), [
            'format' => [
                'required',
                'string',
                // Using available types in Box\Spout\Common\Type //
                Rule::in(['csv', 'xlsx', 'ods']),
            ]
        ]);
        $format = request('format');

        $writer = WriterFactory::createFromType($format);
        $writer->openToBrowser("Документы реферального партнера {$customerId}.{$format}");
        $writer->addRow(WriterEntityFactory::createRowFromArray([
            'Номер документа',
            'Тип документа',
            'Начало периода',
            'Окончание периода',
            'Дата документа',
            'Сумма вознаграждения',
            'Статус',
            'Файл',
        ], null));

        $documents = $customerService->documents($customerId);
        $files = [];
        if ($documents) {
            $files = $fileService->getFiles($documents->pluck('file_id')->all())->keyBy('id');
        }

        foreach ($documents as $document) {
            /** @var FileDto $file */
            $file = $files->get($document->file_id);
                $writer->addRow(WriterEntityFactory::createRowFromArray([
                    $document->id,
                    $document->typeName($document->type),
                    $document->period_since,
                    $document->period_to,
                    $document->updated_at,
                    $document->amount_reward,
                    $document->statusName($document->status),
                    $file->url,
                ], null));

        }

        $writer->close();
    }

    /**
     * @param int $customerId
     * @param int $documentId
     * @param MailService\MailService $mailService
     * @param CustomerService $customerService
     * @param UserService $userService
     * @param FileService $fileService
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail
    (int $customerId,
     int $documentId,
     MailService\MailService $mailService,
     CustomerService $customerService,
     UserService $userService,
     FileService $fileService)
    {
        /** @var CustomerDto $customer */
        $customer = $customerService->customers((new RestQuery())->setFilter('id', $customerId))->first();
        $userId = $customer->user_id;
        /** @var UserDto $user */
        $user = $userService->users((new RestQuery())->setFilter('id', $userId))->first();
        if (!$user->email)
        {
            throw new BadRequestHttpException('Ошибка: Не указан Email пользователя');
        }
        /** @var CustomerDocumentDto $document */
        $document = $customerService->documents($customerId)->where('id', $documentId)->first();
        /** @var FileDto $file */
        $file = $fileService->getFiles([$document->file_id])->keyBy('id')->get($document->file_id);
        // Проверка на отсутствие прикрепленного к акту файла //
        !$file ? $attachment = null : $attachment = $file->url;

        $arrayData = [
            "u_first_name" => $user->first_name,
            "u_mid_name" => $user->middle_name,
            "d_id" => $document->id,
            "d_period_since" => $document->period_since,
            "d_period_to" => $document->period_to,
            "d_creation_date" => $document->updated_at,
            "d_amount_reward" => $document->amount_reward,
            "d_type" => $document->typeName($document->type),
            "d_status" => $document->statusName($document->status),
            "file_url" => $attachment,
        ];

        $message = new SendReferralDocumentMailDto();
        $message->addTo($user->email, $user->full_name);
        $message->setData($arrayData);
        $mailService->send($message);

        return response()->json([
            'email' => $user->email,
        ], 200);
    }

    /**
     * @param int $customerId
     * @param CustomerService $customerService
     * @return \Illuminate\Http\JsonResponse
     */
    public function createDocument(int $customerId, CustomerService $customerService)
    {
        $this->validate(request(), [
            'type' => 'required|integer',
            'file' => 'required|max:4000',
            'period_since' => 'nullable|date',
            'period_to' => 'nullable|date',
            'amount_reward' => 'nullable|numeric',
            'status' => 'nullable|integer',
        ]);

        $documentFile = request('file');
        $documentDto = new CustomerDocumentDto();
        $documentDto->type = request('type');
        $documentDto->file_id = $documentFile['id'];
        $documentDto->period_since = request('period_since');
        $documentDto->period_to = request('period_to');
        $documentDto->amount_reward = request('amount_reward');
        $documentDto->status = request('status');

        $createdDocumentId = $customerService->createDocument($customerId, $documentDto);
        /** @var CustomerDocumentDto $createdDocument */
        $createdDocument = $customerService->documents($customerId)->where('id', $createdDocumentId)->first();

        return response()->json([
            'id' => $createdDocumentId,
            'typeId' => $createdDocument->type,
            'period_since' => $createdDocument->period_since,
            'period_to' => $createdDocument->period_to,
            'date' => $createdDocument->updated_at,
            'amount_reward' => $createdDocument->amount_reward,
            'statusId' => $createdDocument->status,
        ]);
    }

    /**
     * @param int $customerId
     * @param int $document_id
     * @param CustomerService $customerService
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function deleteDocument(int $customerId, int $document_id, CustomerService $customerService)
    {
        $customerService->deleteDocument($customerId, $document_id);

        return response('', 204);
    }
}