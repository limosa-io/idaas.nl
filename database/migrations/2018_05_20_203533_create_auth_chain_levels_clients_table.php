<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\OpenIDProvider;

class CreateAuthChainLevelsClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_authlevel', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('tenant_id')->index();
            
            $table->uuid('client_id');
            $table->uuid('auth_level_id');
            
            $table->unique(['tenant_id', 'client_id', 'auth_level_id']);

            $table->timestamps();

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
        Schema::dropIfExists('client_authlevel');
    }
}
