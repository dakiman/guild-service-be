<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GuildDocument extends FlexibleDataTransferObject
{
    public string $name;
    public string $realm;
    public int $num_of_searches;
    public string $region;
    /** @var \App\DTO\Guild\GuildMember[] */
    public $roster;
    public GuildBasic $basic;
}
