<?php

namespace App\Jobs;

use App\Models\Guild;
use App\Services\CharacterService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

//TODO Implement ShouldBeUnique
class RetrieveGuildRoster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Guild $guild;

    public function __construct(Guild $guild)
    {
        $this->guild = $guild;
    }

    public function handle(CharacterService $characterService)
    {
        $guild = $this->guild->toDTO();

        foreach ($guild->roster as $member) {
            try {
                if ($member->level >= config('blizzard.min_level_for_character_lookup')) {
                    $characterService->getCharacter($guild->region, $member->realm, $member->name);
                }
            } catch (Exception $e) {
                Log::error('Exception encountered while syncing guild roster', ['guild' => $guild->name, 'character' => $member, 'exception' => $e->getMessage()]);
            }
        }
    }
}
