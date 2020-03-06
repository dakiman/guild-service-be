<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class CharacterMedia extends FlexibleDataTransferObject
{
    public string $avatar;
    public string $bust;
    public string $render;

    public static function fromData(object $media): self
    {
        return new self([
            'avatar' => $media->avatar_url,
            'bust' => $media->bust_url,
            'render' => $media->render_url
        ]);
    }
}
