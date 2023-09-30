<?php

namespace App\Console\Commands;

use App\AuthCode;
use App\ModuleResult;
use App\Subject;
use App\Token;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CollectGarbarge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:garbage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired module results and access tokens';

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
     * @return mixed
     */
    public function handle()
    {
        Token::withoutGlobalScopes()->where('expires_at', '<', Carbon::now())->delete();
        ModuleResult::withoutGlobalScopes()->where('expires_at', '<', Carbon::now())->delete();
        AuthCode::withoutGlobalScopes()->where('expires_at', '<', Carbon::now())->delete();
        Subject::withoutGlobalScopes()->doesntHave('tokens')->doesntHave('moduleResults')->delete();
    }
}
