<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Item extends FlexibleDataTransferObject
{
    public int $id;
    public int $itemLevel;
    public string $quality;
    public string $slot;
}
