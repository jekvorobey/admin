<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\BlockDto;
use Greensight\Customer\Dto\CustomerWhitelistCreateAccountsDto;
use Greensight\Customer\Dto\CustomerWhitelistDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\CustomerWhitelistService\CustomerWhitelistService;
use Illuminate\Http\Request;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Services\FileService\FileService;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Greensight\CommonMsa\Dto\DataQuery;
use Illuminate\Http\JsonResponse;

class CustomerWhitelistController extends Controller
{
    protected function index(CustomerWhitelistService $customerWhitelistService)
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $this->title = 'Вайтлист';

        $restQuery = $this->makeRestQuery($customerWhitelistService, true);
        $pager = $customerWhitelistService->customerWhitelistItemsCount($restQuery);
        $customerWhitelistItems = $this->loadCustomerWhitelistItems($restQuery);

        return $this->render('Customer/Whitelist', [
            'iCustomerWhitelistItems' => $customerWhitelistItems,
            'iCurrentPage' => $this->getPage(),
            'iPager' => $pager,
            'iFilter' => $this->getFilter(true),
        ]);
    }

    /**
     * @throws Exception
     */
    public function page(CustomerWhitelistService $customerWhitelistService): JsonResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_BASKETS);

        $restQuery = $this->makeRestQuery($customerWhitelistService);
        $customerWhitelistItems = $this->loadCustomerWhitelistItems($restQuery);
        $result = [
            'customerWhitelistItems' => $customerWhitelistItems,
        ];
        if ($this->getPage() == 1) {
            $result['pager'] = $customerWhitelistService->customerWhitelistItemsCount($restQuery);
        }

        return response()->json($result);
    }

    protected function loadCustomerWhitelistItems(DataQuery $restQuery): Collection
    {
        /** @var CustomerWhitelistService $customerWhitelistService */
        $customerWhitelistService = resolve(CustomerWhitelistService::class);

        $customerWhitelistItems = $customerWhitelistService->customerWhitelistItems($restQuery);
        if ($customerWhitelistItems->isEmpty()) {
            return collect();
        }

        $customerWhitelistItems = $customerWhitelistItems->map(function (CustomerWhitelistDto $customerWhitelistDto) {
            return $customerWhitelistDto->toArray();
        });

        return $customerWhitelistItems;
    }

    /**
     * @throws Exception
     */
    protected function makeRestQuery(
        CustomerWhitelistService $customerWhitelistService,
        bool $withDefaultFilter = false
    ): DataQuery {
        $restQuery = $customerWhitelistService->newQuery();

        $page = $this->getPage();
        $restQuery->pageNumber($page, 100);

        $filter = $this->getFilter($withDefaultFilter);
        if ($filter) {
            foreach ($filter as $key => $value) {
                switch ($key) {
                    case 'is_user_exists':
                        $restQuery->setFilter('user_id', filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'notNull' : 'null');
                        break;
                    case 'is_possible_to_create_account':
                        $restQuery->setFilter('phone', 'notNull')->setFilter('user_id', 'null');
                        break;
                    case 'city':
                    case 'comment':
                        $restQuery->setFilter($key, 'like', "%$value%");
                        break;
                    case 'customer':
                        $restQuery->setFilter('link_phone_email_full_name', $value);
                        break;
                    default:
                        $restQuery->setFilter($key, $value);
                }
            }
        }

        $restQuery->addSort('updated_at', 'desc');

        return $restQuery;
    }

    protected function getPage(): int
    {
        return request()->get('page', 1);
    }

    /**
     * @throws ValidationException
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter
     */
    protected function getFilter(bool $withDefault = false): array
    {
        return Validator::validate(
            request('filter') ?? [],
            [
                'customer' => 'string|sometimes',
                'is_user_exists' => 'string|sometimes',
                'city' => 'string|sometimes',
                'comment' => 'string|sometimes',
                'is_possible_to_create_account' => 'string|sometimes',
            ]
        );
    }

    protected function import(Request $request, CustomerService $customerService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $request->validate([
            'file_id' => 'required|integer',
        ]);

        $customerService->importWhitelist($request->file_id);

        return response()->noContent();
    }

    protected function export(CustomerService $customerService, FileService $fileService): StreamedResponse
    {
        $this->canView(BlockDto::ADMIN_BLOCK_CLIENTS);

        $fileId = $customerService->exportWhitelist();
        /** @var FileDto $file */
        $file = $fileService->getFiles([$fileId])->first();

        return response()->streamDownload(function () use ($file) {
            echo file_get_contents($file->absoluteUrl());
        }, $file->original_name);
    }

    protected function createAccounts(Request $request, CustomerWhitelistService $customerWhitelistService): Response
    {
        $this->canUpdate(BlockDto::ADMIN_BLOCK_CLIENTS);

        $validatedData = $request->validate([
            'whitelistIds' => 'required|array',
            'whitelistIds.*' => 'integer',
        ]);

        $customerWhitelistService->createAccounts(new CustomerWhitelistCreateAccountsDto($validatedData));

        return response()->noContent();
    }
}
