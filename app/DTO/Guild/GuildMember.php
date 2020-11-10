<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GuildMember extends FlexibleDataTransferObject
{
    public string $name;
    public string $realm;
    public int $level;
    public int $class;
    public int $race;
    public int $rank;
}
