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
use Carbon\Carbon;
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
            ->where('created_at', '>', $daysAgo)
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


        $results = DB::table('stats')
            ->select(['hours', DB::raw('count(*)')])
            ->where('hours', '>', intval((time() / 3600) - (30 * 24)))
            ->groupBy('hours')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::createFromTimestamp($item->hours)->format('Y-m-d')
                ];
            })->groupBy('date')->map(function ($item) {
                return count($item);
            });


        $values = $results->all();
        ksort($values);

        return array_values($values);
    }

    public function loginsPerHour1Day()
    {
    }
}
