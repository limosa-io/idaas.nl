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
        Schema::create('serviceprovider_group', function (Blueprint $table) {
            $table->increments('id');

            $table->uuid('tenant_id');

            $table->uuid('serviceprovider_id');
            $table->uuid('group_id');

            $table->foreign('serviceprovider_id')->references('id')->on('remote_service_providers')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');

            $table->unique(['serviceprovider_id', 'group_id']);

            $table->index('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

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
        Schema::dropIfExists('serviceprovider_group');
    }
};
