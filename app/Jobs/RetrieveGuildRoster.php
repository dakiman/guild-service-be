<?php

namespace App\Jobs;

use App\Models\Guild;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

//TODO Implement ShouldBeUnique
class RetrieveGuildRoster implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Guild $guild;

    public function __construct(Guild $guild)
    {
        $this->guild = $guild;
    }

    public function uniqueId()
    {
        return $this->guild->region . ' ' . $this->guild->realm . ' ' . $this->guild->name;
    }

    public function handle()
    {
        if(isset($this->guild->roster_synced_at) &&
            $this->guild->roster_synced_at->diffInSeconds() > config('blizzard.guild_min_seconds_update')) {
            \Log::info("Guild already synced...");
            return;
        }

        $this->guild->roster_synced_at = now()->toDateTimeString();;
        $this->guild->save();

        $guild = $this->guild->toDTO();

        foreach ($guild->roster as $member) {
            if ($member->level >= config('blizzard.min_level_for_character_lookup')) {
                RetrieveCharacterData::dispatch($guild->region, $member->realm, $member->name);
            }
        }
    }
}
