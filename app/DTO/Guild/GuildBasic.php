<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GuildBasic extends FlexibleDataTransferObject
{
    public int $achievement_points;
    public int $member_count;
    public int $created_timestamp;
    public string $faction;
}
