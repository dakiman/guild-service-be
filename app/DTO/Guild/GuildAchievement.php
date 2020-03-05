<?php


namespace App\DTO\Guild;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class GuildAchievement extends FlexibleDataTransferObject
{
    public int $id;
    public string $name;
    public ?int $completedAt;

    public static function fromData(object $achievement): self
    {
        return new self([
            'id' => $achievement->id,
            'name' => $achievement->achievement->name,
            'completedAt' => $achievement->completed_timestamp ?? null
        ]);
    }

}
