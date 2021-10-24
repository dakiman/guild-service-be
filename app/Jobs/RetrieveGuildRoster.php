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


    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 1200;

    public function __construct(Guild $guild)
    {
        $this->guild = $guild;
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->guild->id;
    }

    public function handle()
    {
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
