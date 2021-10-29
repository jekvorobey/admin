<?php

namespace App\Http\Controllers\Customers\Detail;

use App\Http\Controllers\Controller;
use Greensight\CommonMsa\Dto\FileDto;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\CommonMsa\Rest\RestQuery;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\CommonMsa\Services\FileService\FileService;
use Greensight\Customer\Dto\CustomerCertificateDto;
use Greensight\Customer\Dto\ReferralContractDto;
use Greensight\Customer\Services\CustomerService\CustomerService;
use Greensight\Customer\Services\ReferralService\ReferralService;
use Greensight\Marketing\Services\MarketingService\MarketingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class TabMainController extends Controller
{
    public function load(
        int $id,
        CustomerService $customerService,
        FileService $fileService,
        UserService $userService,
        MarketingService $marketingService,
        ReferralService $referralService
    ) {
        $certificates = $customerService->certificates($id);
        $files = [];
        if ($certificates) {
            $files = $fileService->getFiles($certificates->pluck('file_id')->all())->keyBy('id');
        }

        $referralContracts = $referralService->referralContracts($id);
        if ($referralContracts) {
            $files = $files->merge($fileService->getFiles($referralContracts->pluck('file_id')->all()))->keyBy('id');
        }

        $managers = $userService->users((new RestQuery())->setFilter('role', UserDto::ADMIN__MANAGER_CLIENT));

        $activities = $customerService->activities()->setCustomerIds([$id])->load();
        $activitiesAll = $customerService->activities()->load();

        $personalDiscount = null;
        if (request('isReferral')) {
            $personalGlobalPercent = $marketingService->getPersonalGlobalPercent($id);
            if ($personalGlobalPercent['percent']) {
                $personalDiscount = $personalGlobalPercent['percent'] . '%';
                if ($personalGlobalPercent['promoCode']) {
                    $personalDiscount .= " ({$personalGlobalPercent['promoCode']})";
                }
            }
        }

        return response()->json([
            'certificates' => $certificates->map(function (CustomerCertificateDto $certificate) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($certificate->file_id);
                if (!$file) {
                    return false;
                }

                return [
                    'id' => $certificate->id,
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
            'referralContracts' => $referralContracts->map(function (ReferralContractDto $referralContract) use ($files) {
                /** @var FileDto $file */
                $file = $files->get($referralContract->file_id);
                if (!$file) {
                    return false;
                }

                return [
                    'id' => $referralContract->id,
                    'url' => $file->absoluteUrl(),
                    'name' => $file->original_name,
                ];
            })->filter(),
            'managers' => $managers->mapWithKeys(function (UserDto $user) {
                return [$user->id => $user->full_name];
            }),
            'activities' => $activities->pluck('id'),
            'activitiesAll' => $activitiesAll,
            'personalDiscount' => $personalDiscount,
        ]);
    }

    public function createCertificate(int $id, int $file_id, CustomerService $customerService)
    {
        $certificateDto = new CustomerCertificateDto();
        $certificateDto->file_id = $file_id;
        $id = $customerService->createCertificate($id, $certificateDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    public function deleteCertificate(int $id, int $certificate_id, CustomerService $customerService)
    {
        $customerService->deleteCertificate($id, $certificate_id);

        return response('', 204);
    }

    public function createReferralContract(int $id, int $file_id, ReferralService $referralService): JsonResponse
    {
        $referralContractDto = new ReferralContractDto();
        $referralContractDto->file_id = $file_id;
        $id = $referralService->createReferralContract($id, $referralContractDto);

        return response()->json([
            'id' => $id,
        ]);
    }

    public function deleteReferralContract(int $id, int $referralContractId, ReferralService $referralService): Response
    {
        $referralService->deleteReferralContract($id, $referralContractId);

        return response('', 204);
    }
}
