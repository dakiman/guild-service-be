<?php

namespace App\Jobs;

use App\Services\CharacterService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class RetrieveCharacterData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $region;
    private string $realmName;
    private string $characterName;

    public function __construct(string $region, string $realmName, string $characterName)
    {
        $this->region = $region;
        $this->realmName = $realmName;
        $this->characterName = $characterName;
    }

    public function handle(CharacterService $characterService)
    {
        try {
            $characterService->getCharacter($this->region, $this->realmName, $this->characterName);
        } catch (Exception $e) {
            Log::error('Exception encountered while retrieving Character data', ['realm' => $this->realmName, 'character' => $this->characterName, 'region' => $this->region, 'exception' => $e->getMessage()]);
        }
    }
}
