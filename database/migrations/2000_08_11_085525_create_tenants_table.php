<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('subdomain')->unique();

            $table->uuid('client_id')->nullable(); // The tenant is an OAuth client itself
            $table->string('authorization_endpoint')->nullable();
            $table->string('token_endpoint')->nullable();
            $table->string('userinfo_endpoint')->nullable();

            $table->integer('session_lifetime')->nullable();
            $table->integer('session_expire_on_close')->nullable();
            
            $table->integer('cookie_lifetime')->nullable();
            $table->integer('cookie_expire_on_close')->nullable();
            
            // a master tenant manages other tenants
            $table->boolean('master')->default(false);

            $table->string('resources_version', 10)->nullable();

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
        Schema::dropIfExists('tenants');
    }
}
