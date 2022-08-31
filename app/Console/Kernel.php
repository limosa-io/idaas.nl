<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Http\Middleware\DetectTenant;
use App\Tenant;
use App\Token;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
        //SendEmail::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(
            function () {
                $now = date('Y-m-d');
                Token::where('expired_at', '<', $now)->all();
                DB::table('recent_users')->delete();
            }
        )->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $tenantEnv = getenv('TENANT');
        $tenant = null;

        try {
            if ($tenantEnv != null) {
                $tenant = Tenant::where(['subdomain' => $tenantEnv])->first();
            }

            if ($tenantEnv == null || $tenant == null) {
                $tenant = Tenant::where(['master' => true])->first();
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // this exception is thrown in case the application was not yet installed (migrated)
        }

        if ($tenant != null) {
            DetectTenant::activateTenant($tenant);
        }

        $this->load(__DIR__ . '/Commands');

        $now = date('Y-m-d');

        include base_path('routes/console.php');
    }
}
