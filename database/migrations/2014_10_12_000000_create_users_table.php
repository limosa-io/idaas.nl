<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // $table->increments('id');
            $table->uuid('id')->primary();

            $table->string('name')->nullable();

            $table->boolean('active')->default(false);

            $table->string('formattedName')->nullable();
            $table->string('givenName')->nullable();
            $table->string('familyName')->nullable();
            $table->string('middleName')->nullable();

            $table->string('gender')->nullable();
            $table->date('birthDate')->nullable();

            $table->string('address')->nullable();

            $table->string('timezone')->nullable();

            $table->string('preferredLanguage')->nullable();

            $table->string('email')->nullable();
            $table->string('password')->nullable();

            $table->string('phoneNumber')->nullable();

            $table->string('displayName')->nullable();

            $table->text('picture')->nullable();

            $table->text('metadataUser')->nullable();
            $table->text('metadataApp')->nullable();

            $table->string('extraIdentifier1')->nullable();
            $table->string('extraIdentifier2')->nullable();
            $table->string('extraIdentifier3')->nullable();
            $table->string('extraIdentifier4')->nullable();

            $table->string('user_metadata')->nullable();
            $table->string('app_metadata')->nullable();

            $table->string('otp_secret')->nullable();

            $table->timestamp('last_successful_login_date', 0)->nullable();

            $table->uuid('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->index(['tenant_id', 'id']);
            $table->index(['tenant_id', 'email']);

            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
