<?php

namespace App\Http\Controllers\Content\Contacts;

use App\Http\Controllers\Controller;
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
        $contacts = $contactsService->getContacts();

        return response()->json([
            'contacts' => $contacts
        ]);

        // TODO: Proceed with Vue integration //
        /*$this->title = 'Социальные сети и контакты';
        return $this->render('Content/Contacts', [
            'contacts' => $contacts
        ]);*/
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
            'file_id' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $contactsService->addContact($data);

        $contacts = $contactsService->getContacts();

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
            'file_id' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $contactsService->updateContact($data);

        $contacts = $contactsService->getContacts();

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
}