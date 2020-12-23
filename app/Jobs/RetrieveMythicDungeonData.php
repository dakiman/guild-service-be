<?php

namespace App\Jobs;

use App\Models\Character;
use App\Services\DungeonService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class RetrieveMythicDungeonData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Character $character;

    public function __construct(Character $character)
    {
        $this->character = $character;
    }

    public function handle(DungeonService $dungeonService)
    {
        try {
            $dungeonService->getMythicDungeonData($this->character);
        } catch (\Exception $e) {
            Log::error('Exception encountered while retrieving Mythic data', ['realm' => $this->character->realm, 'character' => $this->character->name, 'region' => $this->character->region, 'exception' => $e->getMessage()]);
        }
    }
}
