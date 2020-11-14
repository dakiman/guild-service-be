<?php

return [

    'client' => [
        'id' => env('BLIZZARD_CLIENT_ID', null),
        'secret' => env('BLIZZARD_CLIENT_SECRET', null)
    ],

    'oauth' => [
        'url' => "https://{region}.battle.net",
    ],

    'api' => [
        'url' => "https://{region}.api.blizzard.com"
    ],

    'regions' => [
        'EU', 'US', 'AU', 'CH'
    ],

    'character_min_seconds_update' => 0,

    'guild_min_seconds_update' => 0,

];
