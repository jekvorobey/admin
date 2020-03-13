<?php

namespace App\Http\Controllers;

use App\Core\Menu;
use Greensight\CommonMsa\Dto\UserDto;
use Greensight\Message\Dto\Communication\CommunicationChannelDto;
use Greensight\CommonMsa\Services\RequestInitiator\RequestInitiator;
use Greensight\CommonMsa\Services\TokenStore\TokenStore;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use MerchantManagement\Dto\MerchantDto;
use MerchantManagement\Services\MerchantService\MerchantService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Controller extends BaseController
{
    protected $title = '';
    protected $loadChannelTypes = false;

    public function render($componentName, $props = [])
    {
        $channelTypes = [];
        if ($this->loadChannelTypes) {
            $channelTypes = [
                'internal_message' => CommunicationChannelDto::CHANNEL_INTERNAL_MESSAGE,
                'infinity' => CommunicationChannelDto::CHANNEL_INFINITY,
                'smsc' => CommunicationChannelDto::CHANNEL_SMSC,
                'livetex_viber' => CommunicationChannelDto::CHANNEL_LIVETEX_VIBER,
                'livetex_telegram' => CommunicationChannelDto::CHANNEL_LIVETEX_TELEGRAM,
                'livetex_fb' => CommunicationChannelDto::CHANNEL_LIVETEX_FB,
                'livetex_vk' => CommunicationChannelDto::CHANNEL_LIVETEX_VK,
                'internal_email' => CommunicationChannelDto::CHANNEL_INTERNAL_EMAIL,
            ];
        }

        return View::component(
            $componentName,
            $props,
            [
                'menu' => Menu::getMenuItems(),
                'user' => [
                    'isGuest' => resolve(TokenStore::class)->token() == null,
                    'isSuper' => resolve(RequestInitiator::class)->hasRole(UserDto::ADMIN__SUPER),
                ],
                'channelTypes' => $channelTypes,
            ],
            [
                'title' => $this->title,
                'assets' => $this->getAssets($componentName),
            ]
        );
    }

    private function getAssets($componentName)
    {
        if (frontend()->isInDevMode()) {
            return [
                'js' => [
                    "scripts/{$componentName}.js",
                ],
                'css' => [],
            ];
        } else {
            $webPack = json_decode(file_get_contents(public_path('/assets/webpack-assets.json')), true);
            $js = [];
            $css = [];

            if (isset($webPack[$componentName])) {
                if (isset($webPack[$componentName]['js'])) {
                    $js = array_filter((array)$webPack[$componentName]['js']);
                }
                if (isset($webPack[$componentName]['css'])) {
                    $css = array_filter((array)$webPack[$componentName]['css']);
                }
            }
            return [
                'js' => $js,
                'css' => $css,
            ];
        }
    }
    
    protected function validate(Request $request, array $rules): array
    {
        $data = $request->all();
        $validator = Validator::make($data, $rules, [
            'required' => 'required :attribute'
        ]);
        if ($validator->fails()) {
            throw new BadRequestHttpException($validator->errors()->first());
        }
        return $data;
    }
    
    /**
     * @param  array  $merchantIds
     * @return Collection|MerchantDto[]
     */
    protected function getMerchants(array $merchantIds): Collection
    {
        $merchants = collect();
        
        if ($merchantIds) {
            /** @var MerchantService $merchantService */
            $merchantService = resolve(MerchantService::class);
            $merchantQuery = $merchantService->newQuery()
                ->setFilter('id', $merchantIds)
                ->addFields(MerchantDto::entity(), 'id', 'display_name');
            $merchants = $merchantService->merchants($merchantQuery)->keyBy('id');
        }
        
        return $merchants;
    }
}
