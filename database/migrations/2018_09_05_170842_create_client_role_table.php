<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientRoleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_role', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('tenant_id');

            $table->uuid('role_id');
            $table->uuid('client_id');

            $table->unique(['client_id', 'role_id']);

            $table->index('tenant_id');

            $table->timestamps();

            $table->foreign('client_id')->references('client_id')->on('oidc_clients')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
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
        Schema::dropIfExists('client_role');
    }
}
