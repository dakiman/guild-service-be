<?php

namespace App\Console\Commands;

use App\Services\BlizzardAuthService;
use Illuminate\Console\Command;

class RefreshBlizzardToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blizzard:token';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh blizzard access token';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $blizzardAuthService = app(BlizzardAuthService::class);
        $token = $blizzardAuthService->refreshAndCacheAccessToken();
        $this->info("Token retrieved successfully $token");
    }

}
