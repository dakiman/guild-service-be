<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GuildDocument extends FlexibleDataTransferObject
{
    public string $name;
    public string $realm;
    public string $region;
    /** @var \App\DTO\Guild\GuildMember[] */
    public $roster;
    public GuildBasic $basic;
}
