<?php

namespace App\Http\Controllers\Content\Contacts;

use App\Http\Controllers\Controller;
use Cms\Dto\ContactDto;
use Cms\Services\ContactsService\ContactsService;
use Greensight\CommonMsa\Dto\BlockDto;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class ContactsController
 * @package App\Http\Controllers\Content\Contacts
 */
class ContactsController extends Controller
{
    /**
     * Список всех контактов и соц. сетей
     * @return mixed
     */
    public function list(ContactsService $contactsService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CONTENT);

        $contacts = $contactsService->getContacts()->keyBy('id');

        $this->title = 'Управление соц. сетями и контактами';

        return $this->render('Content/Contacts', [
            'iContacts' => $contacts,
            'iContactTypes' => ContactDto::contactTypes(),
        ]);
    }

    /**
     * Добавить контакт или соц. сеть
     */
    public function add(ContactsService $contactsService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'name' => 'required|string',
            'address' => 'nullable|string',
            'icon_file.id' => 'nullable|integer',
            'description' => 'nullable|string',
            'type' => 'required|integer',
        ]);

        $contactDto = $this->fulfillDto($data);

        $contactsService->addContact($contactDto);

        $contacts = $contactsService->getContacts()->keyBy('id');

        return response()->json([
            'contacts' => $contacts,
        ]);
    }

    /**
     * Изменить данные о контакте или соц. сети
     */
    public function edit(ContactsService $contactsService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'address' => 'nullable|string',
            'icon_file.id' => 'nullable|integer',
            'description' => 'nullable|string',
            'type' => 'required|integer',
        ]);

        $contactDto = $this->fulfillDto($data);

        $contactsService->updateContact($contactDto);

        $contacts = $contactsService->getContacts()->keyBy('id');

        return response()->json([
            'contacts' => $contacts,
        ]);
    }

    /**
     * Удалить контакт или соц. сеть
     */
    public function remove(ContactsService $contactsService): Response|Application|ResponseFactory
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CONTENT);

        $data = $this->validate(request(), [
            'id' => 'required|integer',
        ]);

        $contactsService->deleteContact($data['id']);

        return response('', 204);
    }

    /**
     * Вспомогательная функция, заполняющая Dto полями из Request
     */
    protected function fulfillDto(array $data): ContactDto
    {
        $contactDto = new ContactDto();
        $contactDto->id = $data['id'] ?? null;
        $contactDto->name = $data['name'] ?? null;
        $contactDto->address = $data['address'] ?? null;
        $contactDto->icon_file = $data['icon_file']['id'] ?? null;
        $contactDto->description = $data['description'] ?? null;
        $contactDto->type = $data['type'] ?? null;

        return $contactDto;
    }
}
