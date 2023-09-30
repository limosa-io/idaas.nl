<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRemoteServiceProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('remote_service_providers', function (Blueprint $table) {

            $table->uuid('id')->primary();

            $table->uuid('tenant_id')->index();

            $table->string('entityid')->index();

            $table->string('remoteMetadataUrl')->nullable();
            $table->boolean('sync')->default(false);

            // array
            $table->text('assertionConsumerService')->nullable();

            // array
            $table->text('singleLogoutService')->nullable();

            // array
            $table->text('keys')->nullable();

            $table->boolean('wantSignedAuthnResponse')->default(true);
            $table->boolean('wantSignedAssertions')->default(true);
            $table->boolean('wantSignedLogoutResponse')->default(true);
            $table->boolean('wantSignedLogoutRequest')->default(true);

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
        Schema::dropIfExists('remote_service_providers');
    }
}
