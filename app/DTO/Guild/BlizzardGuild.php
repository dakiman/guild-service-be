<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;
use Str;

class BlizzardGuild extends FlexibleDataTransferObject
{

    public int $id;
    public int $memberCount;
    public int $created;
    public int $achievementPoints;
    public string $name;
    public string $faction;
    public string $realm;
    /** @var RosterCharacter|null */
    public $roster;
    /** @var GuildAchievement|null */
    public $achievements;

    public static function fromData(object $guild): self
    {
        return new self([
            'id' => $guild->id,
            'name' => $guild->name,
            'faction' => ucfirst(strtolower($guild->faction->type)),
            'achievementPoints' => $guild->achievement_points,
            'memberCount' => $guild->member_count,
            'realm' => Str::deslug($guild->realm->slug),
            'created' => $guild->created_timestamp
        ]);
    }
}
