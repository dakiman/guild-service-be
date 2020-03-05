<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;
use Str;

class RosterCharacter extends FlexibleDataTransferObject
{
    public string $name;
    public int $level;
    public string $realm;
    public int $class;
    public int $race;
    public int $rank;
    public string $region;

    public static function fromData(object $member, string $locale)
    {
        $character = $member->character;
        return new self([
            'name' => $character->name,
            'level' => $character->level,
            'realm' => Str::deslug($character->realm->slug),
            'class' => $character->playable_class->id,
            'race' => $character->playable_race->id,
            'rank' => $member->rank,
            'region' => $locale
        ]);
    }
}
