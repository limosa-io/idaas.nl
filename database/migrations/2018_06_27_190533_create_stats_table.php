<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class CreateStatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {

            $table->timestampTz('time')->default(DB::raw('CURRENT_TIMESTAMP'));
            
            $table->uuid('statable_id');
            $table->string('statable_type');

            $table->string('key');
            $table->string('value')->nullable();

            $table->uuid('tenant_id')->index();

            $table->index(['statable_id','statable_type']);

            $table->index(['key', 'value']);
            
            // Do not enable the foreign key constraint on the stats table. It will slow down inserts
            // $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
        });

        if(config('database.default') == 'pgsql'){
            // DB::statement("CREATE EXTENSION IF NOT EXISTS timescaledb CASCADE;");
            DB::statement("SELECT create_hypertable('stats', 'time', chunk_time_interval => interval '7 days');");

            // for showing the number of logins per tenant
            DB::raw("
                CREATE VIEW hourly_logins 
                WITH (
                    timescaledb.continuous,
                    timescaledb.refresh_interval = '30m',
                    timescaledb.refresh_lag = '-7 days'
                )
                AS
                SELECT
                    count(key) as logins,
                    time_bucket('1 hour', time) as time,
                    tenant_id from stats
                where 
                    statable_type = 'App\User' and
                    key = 'login'
                group by 
                    time_bucket('1 hour', time),
                    tenant_id
            ");
            
            // for showing the number of logins per user, per week
            DB::raw("
                CREATE VIEW user_logins 
                WITH (
                    timescaledb.continuous,
                    timescaledb.refresh_interval = '30m',
                    timescaledb.refresh_lag = '-7 days'
                )
                AS
                SELECT 
                    count(key) as logins,
                    time_bucket('1 week', time) as time,
                    tenant_id,
                    statable_id
                from stats 
                where
                    statable_type = 'App\User' and
                    key = 'login' 
                group by
                    tenant_id,
                    statable_id,
                    time_bucket('1 week', time);
            ");

            // for showing the number of access tokens per OAuth client
            DB::raw("
                CREATE VIEW app_tokens 
                WITH (
                    timescaledb.continuous,
                    timescaledb.refresh_interval = '1m',
                    timescaledb.refresh_lag = '-7 days'
                ) AS
                SELECT
                    count(key) as tokens,
                    time_bucket('1 week', time) as time,
                    tenant_id,
                    statable_id
                from stats
                where
                    statable_type = 'App\Client' and
                    key = 'access_token'
                group by 
                    tenant_id,
                    statable_id,
                    time_bucket('1 week', time);
            ");
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("
            DROP TABLE stats CASCADE
        ");
    }
}
