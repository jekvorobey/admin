<?php

namespace App\Core;

use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Illuminate\Support\Collection;

class UserHelper
{
    /**
     * @return Collection|UserDto[]
     */
    public static function getUsersByIds(array $ids, array $fields = [], array $include = []): array|Collection
    {
        if (!$ids) {
            return collect();
        }

        /** @var UserService $userService */
        $userService = resolve(UserService::class);

        $usersQuery = $userService->newQuery()->setFilter('id', $ids);
        if ($fields) {
            $usersQuery->addFields(...$fields);
        }
        if ($include) {
            $usersQuery->include(...$include);
        }

        return $userService->users($usersQuery)->keyBy('id');
    }
}
