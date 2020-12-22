<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Specialization extends FlexibleDataTransferObject
{
    public string $activeSpecialization;
    /** @var \App\DTO\Character\Talent[] */
    public $talents;

}
