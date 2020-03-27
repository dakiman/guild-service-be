<?php

namespace App\Http\Controllers;


use App\Rules\RegionRule;
use App\Services\Blizzard\BlizzardAuthClient;
use App\Services\Blizzard\BlizzardUserClient;

class BlizzardController extends Controller
{
    private BlizzardAuthClient $authClient;
    private BlizzardUserClient $userClient;

    public function __construct()
    {
        request()->validate([
            'locale' => ['required', new RegionRule]
        ]);

        $this->authClient = app(BlizzardAuthClient::class, ['locale' => request('locale')]);
        $this->userClient = app(BlizzardUserClient::class, ['locale' => request('locale')]);
    }

    public function code()
    {
        request()->validate([
            'code' => 'required',
            'redirectUri' => 'required'
        ]);

        $token = $this->authClient->retrieveUserToken(request('code'), request('redirectUri'));
        $blizzardUserInfo = $this->userClient->getUserInfo($token);

        $user = auth()->user();
        $user->blizzard_id = $blizzardUserInfo->id;
        $user->battle_tag = $blizzardUserInfo->battletag;
        $user->save();

        return response(['data' => $this->userClient->getUserInfo($token)]);
    }
}
