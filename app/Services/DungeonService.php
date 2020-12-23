<?php


namespace App\Services;


use App\Models\Character;
use App\Models\DungeonRun;
use App\Services\Blizzard\BlizzardProfileClient;

class DungeonService
{
    private BlizzardProfileClient $profileClient;

    public function __construct(BlizzardProfileClient $profileClient)
    {
        $this->profileClient = $profileClient;
    }

    public function getMythicDungeonData(Character $character)
    {
        $responses = $this->profileClient->getBestMythicsInfo($character->region, $character->realm, $character->name);

        $this
            ->parseDungeonData($responses['best_mythics'], $character);
    }

    private function parseDungeonData($response, $character)
    {
        $data = json_decode($response->getBody());

        $season = $data->season->id;

        foreach ($data->best_runs as $dungeonRun) {
            $dungeonRun = DungeonRun::firstOrCreate([
                'season' => $season,
                'completedTimestamp' => $dungeonRun->completed_timestamp,
                'duration' => $dungeonRun->duration,
                'keystoneLevel' => $dungeonRun->keystone_level,
                'affixes' => $this->parseAffixes($dungeonRun->keystone_affixes),
            ]);

            if($dungeonRun->wasRecentlyCreated) {
                $this->parseMembers($dungeonRun->members, $character);
                $character->dungeonRuns()->create($dungeonRun->toArray());
            }

        }
    }

    private function parseAffixes($affixes)
    {
        return array_map(fn($affix) => ['name' => $affix->name, 'id' => $affix->id], $affixes);
    }

    /*TODO Create character jobs*/
    private function parseMembers($members, $character)
    {

//        return array_map(fn ($member) => ['name' => $member->character->name, 'realm' => $member->character->realm->slug], $members);
    }


}
