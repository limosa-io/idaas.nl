<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stats', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->timestamps();

            // Save the number of hours since 1970. For easy grouping/reporting
            $table->integer('hours');

            $table->uuid('statable_id');
            $table->string('statable_type');

            $table->string('key');
            $table->string('value')->nullable();

            $table->uuid('tenant_id')->index();

            $table->index(['statable_id','statable_type']);

            $table->index(['key', 'value']);
        });
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
};
