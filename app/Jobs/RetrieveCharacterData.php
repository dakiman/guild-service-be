<?php

namespace App\Jobs;

use App\Models\Guild;
use App\Services\CharacterService;
use App\Services\GuildService;
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
    private $ownerId;

    public function __construct(string $region, string $realmName, string $characterName, $ownerId = null)
    {
        $this->region = $region;
        $this->realmName = $realmName;
        $this->characterName = $characterName;
        $this->ownerId = $ownerId;
    }

    public function handle(CharacterService $characterService, GuildService $guildService)
    {
        try {
            $character = $characterService->getCharacter($this->region, $this->realmName, $this->characterName);

            if($this->ownerId) {
                $character->user_id = $this->ownerId;
                $character->save();
            }

            if(isset($character->basic->guild) && $character->basic->guild != null) {
                $guild = $guildService->getGuild(
                    $character->region,
                    $character->basic->guild->realm,
                    $character->basic->guild->name,
                );

                if(!isset($guild->roster_synced_at) ||
                    $guild->roster_synced_at->diffInSeconds() > config('blizzard.guild_min_seconds_update')) {
                    RetrieveGuildRoster::dispatch($guild);
                }
            }

            
            RetrieveMythicDungeonData::dispatch($character);

        } catch (Exception $e) {
            Log::error('Exception encountered while retrieving Character data', ['realm' => $this->realmName, 'character' => $this->characterName, 'region' => $this->region, 'exception' => $e->getMessage()]);
        }
    }
}
