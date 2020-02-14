<?php

namespace App\Http\Controllers\Customers;


use App\Http\Controllers\Controller;
use Greensight\Customer\Dto\ActivityDto;
use Greensight\Customer\Services\CustomerService\CustomerService;

class ActivitiesController extends Controller
{
    public function list(
        CustomerService $customerService
    )
    {
        $this->title = 'Виды деятельности';
        return $this->render('Customer/Activity/List', [
            'iActivities' => $customerService->activities()->load(),
        ]);
    }

    public function save(
        CustomerService $customerService
    )
    {
        $data = $this->validate(request(), [
            'id' => 'nullable',
            'name' => 'required',
            'active' => 'boolean',
        ]);

        $activityDto = new ActivityDto();
        $activityDto->name = $data['name'];
        $activityDto->active = $data['active'];
        if ($data['id']) {
            $customerService->updateActivity($data['id'], $activityDto);
        } else {
            $customerService->createActivity($activityDto);
        }

        return response()->json([
            'data' => $data,
            'activities' => $customerService->activities()->load(),
        ]);
    }
}