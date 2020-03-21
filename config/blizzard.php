<?php

return [

    'client' => [
        'id' => env('BLIZZARD_CLIENT_ID', null),
        'secret' => env('BLIZZARD_CLIENT_SECRET', null)
    ],

    /* TODO replace locale with region (naming) */
    'oauth' => [
        'url' => "https://{locale}.battle.net",
    ],

    'api' => [
        'url' => "https://{locale}.api.blizzard.com"
    ],

    'regions' => [
        'EU', 'US', 'AU', 'CH'
    ]

];
