<?php


namespace App\DTO\Character;


use Spatie\DataTransferObject\FlexibleDataTransferObject;

class Media extends FlexibleDataTransferObject
{
    public string $avatar;
    public string $inset;
    public string $main;

    public static function fromResponse(object $data)
    {
        return new static(static::mapAssetsToPictures($data->assets));
    }

    private static function mapAssetsToPictures(array $assets)
    {
        $pictures = [];
        foreach ($assets as $asset) {
            $pictures[$asset->key] = $asset->value;
        };
        return $pictures;
    }
}
