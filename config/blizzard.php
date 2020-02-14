<?php

return [

    'client' => [
        'id' => env('BLIZZARD_CLIENT_ID', null),
        'secret' => env('BLIZZARD_CLIENT_SECRET', null)
    ],

    'oauth' => [
        'url' => "https://eu.battle.net/oauth/token"
    ],

    'api' => [
        'url' => "https://{locale}.api.blizzard.com"
    ],

    'regions' => [
        'EU', 'US', 'AU', 'CH'
    ]

];
