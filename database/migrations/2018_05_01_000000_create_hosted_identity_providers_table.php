<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHostedIdentityProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hosted_identity_providers', function (Blueprint $table) {
            
            $table->uuid('id')->primary();

            $table->uuid('tenant_id')->index();

            $table->string('previousSession')->nullable();

            $table->boolean('signAuthnrequest')->default(false);
            $table->boolean('metadataSignEnable')->default(true);
            $table->boolean('redirectSign')->default(true);
            $table->boolean('ssoHttpPostEnabled')->default(true);
            $table->boolean('ssoHttpRedirectEnabled')->default(true);
            $table->boolean('sloHttpPostEnabled')->default(true);
            $table->boolean('sloHttpRedirectEnabled')->default(true);

            $table->text('keys')->nullable();
            $table->text('supportedNameIDFormat')->nullable();
            $table->text('contacts')->nullable();
            $table->text('organization')->nullable();

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
        Schema::dropIfExists('hosted_identity_providers');
    }
}
