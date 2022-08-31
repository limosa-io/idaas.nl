<?php

/**
 * Generates some stats.
 *
 * In use by the admin ui dashboard.
 */

namespace App\Http\Controllers;

use App\Client;
use App\User;
use App\Token;
use Illuminate\Support\Facades\DB;

class StatController extends Controller
{
    public function dashboard()
    {
        $now = \Carbon\Carbon::now();

        // results in selecting today + last 29 days
        $daysAgo = $now->addDays(-29);

        $creations = DB::table('stats')
            ->where('tenant_id', resolve('App\Tenant')->id)
            ->where('key', 'operation')
            ->where('value', 'ArieTimmerman\Laravel\SCIMServer\Events\Create')
            ->where('time', '>', $daysAgo)
            ->count();

        return [
            'applications' => Client::count(),
            'users' => User::count(),
            'tokens' => Token::count(),
            'user_creations' => $creations
        ];
    }

    public function loginsPerDay30Days()
    {
        $results = DB::table('hourly_logins')
            ->select(DB::raw("to_char(date_trunc('day',time),'YYYY-MM-DD') as d"), DB::raw('count(*) as total'))
            ->where('tenant_id', resolve('App\Tenant')->id)
            ->whereRaw("time > (now() - INTERVAL '30 days')")
            ->groupBy('d')
            ->orderBy('d', 'asc')
            ->get();

        $results = $results->mapWithKeys(
            function ($item) {
                return [$item->d => intVal($item->total)];
            }
        );

        for ($i = -29; $i <= 0; $i++) {
            $date = date('Y-m-d', strtotime(sprintf("%d days", $i)));
            if (!isset($results[$date])) {
                $results[$date] = 0;
            }
        }

        $values = $results->all();
        ksort($values);

        return array_values($values);
    }

    public function loginsPerHour1Day()
    {
    }
}
