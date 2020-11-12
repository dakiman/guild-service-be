<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CharacterDocument extends FlexibleDataTransferObject
{
    public string $name;
    public string $realm;
    public string $region;
    public ?string $user_id;
    public ?int $num_of_searches;
    public ?bool $recruitment;
    public Media $media;
    public CharacterBasic $basic;
    /** @var \App\DTO\Character\Item[] */
    public $equipment;
}
