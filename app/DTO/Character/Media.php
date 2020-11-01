<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Media extends FlexibleDataTransferObject
{
    public string $avatar;
    public string $inset;
    public string $main;
}
