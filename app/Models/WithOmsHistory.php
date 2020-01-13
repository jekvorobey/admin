<?php

namespace App\Models;

use Exception;
use Greensight\CommonMsa\Services\AuthService\UserService;
use Greensight\Oms\Dto\History\HistoryDto;
use Illuminate\Support\Collection;

/**
 * Trait WithOmsHistory
 * @package App\Models
 */
trait WithOmsHistory
{
    /** @var bool */
    private $historyLoaded = false;
    /** @var array */
    private $history;
    
    /**
     * Загрузить историю по сущности OMS
     * @return array
     */
    public function loadHistory(): array
    {
        if (!$this->historyLoaded) {
            /** @var UserService $userService */
            $userService = resolve(UserService::class);
            
            try {
                /** @var Collection $history */
                $history = $this->getHistory();
                
                if (!empty($history) && count($history) > 0) {
                    $users = $history->pluck('user_id')->toArray();
                    
                    $restQuery = $userService
                        ->newQuery()
                        ->setFilter('id', $users);
                    
                    $users = $userService->users($restQuery);
                    
                    $history = $history->map(function (HistoryDto $item) use ($users) {
                        $data = $item->toArray();
                        $data['type'] = $item->type()->toArray();
                        
                        if ($item->user_id) {
                            $data['user'] = $users->filter(function ($user) use ($item) {
                                return $user->id == $item->user_id;
                            })->first();
                        }
                        
                        return $data;
                    });
                }
                
                $this->history = array_values($history->sortByDesc('created_at')->toArray());
                
                $this->historyLoaded = true;
                
            } catch (Exception $e) {
                dump($e);
            }
        }
        
        return [
            'history' => $this->history,
        ];
    }
}
