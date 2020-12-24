<?php

namespace App\Jobs;

use App\Models\Character;
use App\Models\DungeonRun;
use App\Services\CharacterService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncRunWithCharacter implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $region;
    private string $realmName;
    private string $characterName;
    private DungeonRun $dungeonRun;

    public function __construct(string $region, string $realmName, string $characterName, DungeonRun $dungeonRun)
    {
        //
        $this->region = $region;
        $this->realmName = $realmName;
        $this->characterName = $characterName;
        $this->dungeonRun = $dungeonRun;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $character = Character::where([
            'name' => $this->characterName,
            'realm' => $this->realmName,
            'region' => $this->region
        ])->first();

        if($character) {
            $character->dungeonRuns()->create($this->dungeonRun->toArray());
        } else {
            $character = app(CharacterService::class)->getCharacter($this->region, $this->realmName, $this->characterName);
            $character->dungeonRuns()->create($this->dungeonRun->toArray());
        }

    }
}
