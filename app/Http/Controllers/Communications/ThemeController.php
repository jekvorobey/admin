<?php

namespace App\Http\Controllers\Communications;


use App\Http\Controllers\Controller;
use Greensight\Message\Dto\Communication\CommunicationThemeDto;
use Greensight\Message\Services\CommunicationService\CommunicationService;
use Greensight\Message\Services\CommunicationService\CommunicationThemeService;

class ThemeController extends Controller
{
    public function index(CommunicationService $communicationService, CommunicationThemeService $communicationThemeService)
    {
        $this->title = 'Темы';
        $channels = $communicationService->channels()->keyBy('id');
        $themes = $communicationThemeService->themes()->keyBy('id');

        return $this->render('Communication/Theme', [
            'iThemes' => $themes,
            'channels' => $channels,
            'iTypes' => CommunicationThemeDto::allThemeTypes(),
        ]);
    }

    public function save(CommunicationThemeService $communicationThemeService)
    {
        $rTheme = request('theme');
        $theme = new CommunicationThemeDto();
        $theme->name = $rTheme['name'];
        $theme->active = (bool)$rTheme['active'];
        $theme->type = $rTheme['type'];
        $theme->channel_id = $rTheme['channel_id'];

        if ($rTheme['id']) {
            $theme->id = $rTheme['id'];
            $communicationThemeService->update($theme);
        } else {
            $communicationThemeService->create($theme);
        }

        $themes = $communicationThemeService->themes()->keyBy('id');
        return response()->json([
            'themes' => $themes,
        ]);
    }

    public function delete($id, CommunicationThemeService $communicationThemeService)
    {
        $communicationThemeService->delete($id);

        $themes = $communicationThemeService->themes()->keyBy('id');
        return response()->json([
            'themes' => $themes,
        ]);
    }
}