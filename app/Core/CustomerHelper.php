<?php

namespace App\Core;

use Greensight\Customer\Dto\CustomerDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Illuminate\Support\Collection;

class CustomerHelper
{
    /**
     * @return Collection|CustomerDto[]
     */
    public static function getCustomersByIds(array $ids, array $fields = [], array $include = []): Collection
    {
        if (!$ids) {
            return collect();
        }

        /** @var CustomerService $customerService */
        $customerService = resolve(CustomerService::class);

        $customersQuery = $customerService->newQuery()->setFilter('id', $ids);
        if ($fields) {
            $customersQuery->addFields(...$fields);
        }
        if ($include) {
            $customersQuery->include(...$include);
        }

        return $customerService->customers($customersQuery)->keyBy('id');
    }
}
