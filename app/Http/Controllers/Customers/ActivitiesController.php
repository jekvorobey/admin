<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Customer\Core\CustomerException;
use Greensight\Customer\Dto\ActivityDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Http\JsonResponse;

class ActivitiesController extends Controller
{
    /**
     * @throws CustomerException
     */
    public function list(CustomerService $customerService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $this->title = 'Виды деятельности';

        return $this->render('Customer/Activity/List', [
            'iActivities' => $customerService->activities()->load(),
        ]);
    }

    /**
     * @throws CustomerException
     */
    public function save(CustomerService $customerService): JsonResponse
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

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
