<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthChainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_chains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->uuid('tenant_id')->index();

            $table->uuid('from');

            $table->uuid('to');

            $table->integer('position')->nullable();

            $table->timestamps();

            $table->foreign('from')->references('id')->on('auth_modules')->onDelete('cascade');
            $table->foreign('to')->references('id')->on('auth_modules')->onDelete('cascade');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('auth_chains');
    }
}
