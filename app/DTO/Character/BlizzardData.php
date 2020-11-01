<?php


namespace App\DTO\Character;



use Spatie\DataTransferObject\FlexibleDataTransferObject;

class BlizzardData extends FlexibleDataTransferObject
{
    public Media $media;
    public Basic $basic;
//    /** @var Item[] */
//    public $items;
}
