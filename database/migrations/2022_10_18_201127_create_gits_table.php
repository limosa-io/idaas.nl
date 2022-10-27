<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gits', function (Blueprint $table) {
            $table->id();

            $table->uuid('tenant_id');

            $table->string('type');
            $table->string('settings')->nullable();

            // when null, pull is done
            $table->dateTime('pull_start_time')->nullable();

            // when null, push is done
            $table->dateTime('push_start_time')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gits');
    }
};
