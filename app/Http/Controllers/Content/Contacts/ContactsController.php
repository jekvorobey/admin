<?php

namespace App\Http\Controllers\Content\Contacts;

use App\Http\Controllers\Controller;
use Cms\Dto\ContactDto;
use Cms\Services\ContactsService\ContactsService;
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
     * @param ContactsService $contactsService
     * @return JsonResponse
     */
    public function list(ContactsService $contactsService)
    {
        $contacts = $contactsService->getContacts()->keyBy('id');

        $this->title = 'Управление соц. сетями и контактами';
        return $this->render('Content/Contacts', [
            'iContacts' => $contacts,
            'iContactTypes' => ContactDto::contactTypes()
        ]);
    }

    /**
     * Добавить контакт или соц. сеть
     * @param ContactsService $contactsService
     * @return JsonResponse
     */
    public function add(ContactsService $contactsService)
    {
        $data = $this->validate(request(), [
            'name' => 'required|string',
            'address' => 'nullable|string',
            'icon_file.id' => 'nullable|integer',
            'description' => 'nullable|string',
            'type' => 'required|integer'
        ]);

        $contactDto = $this->fulfillDto($data);

        $contactsService->addContact($contactDto);

        $contacts = $contactsService->getContacts()->keyBy('id');

        return response()->json([
            'contacts' => $contacts
        ]);
    }

    /**
     * Изменить данные о контакте или соц. сети
     * @param ContactsService $contactsService
     * @return JsonResponse
     */
    public function edit(ContactsService $contactsService)
    {
        $data = $this->validate(request(), [
            'id' => 'required|integer',
            'name' => 'required|string',
            'address' => 'nullable|string',
            'icon_file.id' => 'nullable|integer',
            'description' => 'nullable|string',
            'type' => 'required|integer'
        ]);

        $contactDto = $this->fulfillDto($data);

        $contactsService->updateContact($contactDto);

        $contacts = $contactsService->getContacts()->keyBy('id');

        return response()->json([
            'contacts' => $contacts
        ]);
    }

    /**
     * Удалить контакт или соц. сеть
     * @param ContactsService $contactsService
     * @return Application|ResponseFactory|Response
     */
    public function remove(ContactsService $contactsService)
    {
        $data = $this->validate(request(), [
            'id' => 'required|integer',
        ]);

        $contactsService->deleteContact($data['id']);

        return response('', 204);
    }

    /**
     * Вспомогательная функция, заполняющая Dto полями из Request
     * @param array $data
     * @return ContactDto
     */
    protected function fulfillDto(array $data)
    {
        $contactDto = new ContactDto();
        $contactDto->id = isset($data['id']) ? $data['id'] : null;
        $contactDto->name = isset($data['name']) ? $data['name'] : null;
        $contactDto->address = isset($data['address']) ? $data['address'] : null;
        $contactDto->icon_file = isset($data['icon_file']['id']) ? $data['icon_file']['id'] : null;
        $contactDto->description = isset($data['description']) ? $data['description'] : null;
        $contactDto->type = isset($data['type']) ? $data['type'] : null;

        return $contactDto;
    }
}