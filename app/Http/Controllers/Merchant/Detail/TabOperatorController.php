<?php

namespace App\Http\Controllers\Merchant\Detail;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Dto\Front;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use MerchantManagement\Dto\OperatorCommunicationMethod;
use MerchantManagement\Services\OperatorService\OperatorService;

class TabOperatorController extends Controller
{
    /**
     * AJAX подгрузка информации для фильтрации отправлений
     *
     * @param int $merchantId
     * @param Request $request
     * @return JsonResponse
     */
    public function loadData()
    {
        return response()->json([
            'communication_methods' => OperatorCommunicationMethod::allMethods(),
            'roles' => UserDto::rolesByFrontIds([Front::FRONT_MAS]),
        ]);
    }

    /**
     * AJAX пагинация списка операторов
     *
     * @param int $merchantId
     * @param Request $request
     * @param OperatorService $operatorService
     * @param UserService $userService
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function loadOperators(
        int $merchantId,
        Request $request,
        OperatorService $operatorService,
        UserService $userService
    ) {
        $page = $request->get('page', 1);
        $restQuery = (new RestQuery())->pageNumber($page, 10)
            ->setFilter('merchant_id', $merchantId);

        $selectKey = $this->getFilter($restQuery);

        if ($selectKey) {
            $operators = $operatorService->operators($restQuery);

            $users = $userService->users((new RestQuery())->setFilter('id', $operators->pluck('user_id')->all()))
                ->keyBy('id');

            $operators = $operators->map(function ($operator) use ($users) {
                return [
                    'id' => $operator->id,
                    'user_id' => $operator->user_id,
                    'full_name' => $users[$operator->user_id]->full_name,
                    'position' => $operator->position,
                    'email' => $users[$operator->user_id]->email,
                    'phone' => $users[$operator->user_id]->phone,
                    'communication_method' => OperatorCommunicationMethod::methodById($operator->communication_method)['name'],
                    'roles' => collect($users[$operator->user_id]->roles)->keys()->map(function ($roleId) {
                        return UserDto::roles()[$roleId];
                    })->all(),
                    'active' => $users[$operator->user_id]->active,
                    'login' => $users[$operator->user_id]->login,
                ];
            });
        } else {
            $operators = collect([]);
        }

        $result = [
            'operators' => $operators,
        ];
        if ($request->get('page') == 1) {
            $result['pager'] = $operatorService->operatorsCount($restQuery);
        }

        return response()->json($result);
    }

    /**
     * Возвращает
     * true если нужно сделать выборку
     * false если выборку делать не нужно (например если какой-то фильтр не дал результата)
     *
     * @return bool
     */
    protected function getFilter(RestQuery $restQuery): bool
    {
        /** UserService $userService */
        $userService = resolve(UserService::class);

        $filter = Validator::make(
            request('filter') ?? [],
            [
                'user_id' => 'integer|someone',
                'full_name' => 'string|someone',
                'email' => 'email|someone',
                'phone' => 'string|someone',
                'login' => 'string|someone',
                'communication_method' => [
                    'someone',
                    Rule::in(array_keys(OperatorCommunicationMethod::allMethods())),
                ],
                'roles' => 'array|someone',
                'roles.' => Rule::in(UserDto::rolesByFrontIds([Front::FRONT_MAS])),
                'active' => 'integer|someone',
            ]
        )->attributes();

        $queryUserKey = true;
        if ($filter) {
            $filterUserKey = true;
            $filterUserIds = collect([]);
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'full_name':
                    case 'email':
                    case 'phone':
                    case 'login':
                    case 'role':
                    case 'active':
                        if ($key === 'phone') {
                            $value = phone_format($value);
                        }
                        $userIds = $userService->users((new RestQuery())->setFilter($key, $value))
                            ->pluck('id');
                        if ($filterUserKey) {
                            $filterUserIds = $userIds;
                            $filterUserKey = false;
                        } else {
                            $filterUserIds = $filterUserIds->intersect($userIds);
                        }
                        break;
                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
            $filterUserIds = $filterUserIds->all();
            if ($filterUserIds) {
                $restQuery->setFilter('user_id', $filterUserIds);
            } else {
                $queryUserKey = false;
            }
        }

        return $queryUserKey;
    }
}
