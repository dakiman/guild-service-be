<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Specialization extends FlexibleDataTransferObject
{
    public string $activeSpecialization;
    /** @var array*/
    public $talents;

}
