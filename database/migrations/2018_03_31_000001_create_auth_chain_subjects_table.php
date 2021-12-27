<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthChainSubjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /**
         * Storage is needed for OIDC because of the UserInfo endpoint. Storing all information in the access token is sub optimal
         */
        Schema::create('authchain_subjects', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Identifier is something like 'password|123'
            $table->string('identifier',100);
            $table->text('subject')->nullable();

            $table->uuid('user_id')->nullable();

            $table->string('levels')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('identifier');

            $table->uuid('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['tenant_id','id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('authchain_subjects');
    }
}
