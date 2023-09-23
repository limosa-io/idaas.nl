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
        Schema::create('fido_keys', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->uuid('tenant_id')->index();
            $table->uuid('user_id')->index();

            $table->string('credential_id')->index();
            $table->string('credential_public_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fido');
    }
};
