<?php

namespace App\Jobs;

use App\Services\BlizzardAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncBnetData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private string $region;
    private string $code;
    private string $redirectUri;
    private $user;

    public function __construct(string $region, string $code, string $redirectUri, $user)
    {
        $this->region = $region;
        $this->code = $code;
        $this->redirectUri = $redirectUri;
        $this->user = $user;
    }

    public function handle(BlizzardAuthService $authService)
    {
        $authService->syncBattleNetDetails($this->region, $this->code, $this->redirectUri, $this->user);
    }
}
