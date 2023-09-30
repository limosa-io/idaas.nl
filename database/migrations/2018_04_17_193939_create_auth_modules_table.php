<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('auth_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->index();

            //TODO: rename this to type
            $table->string('type', 200);

            $table->text('config')->nullable();

            $table->boolean('skippable')->default(true);
            $table->boolean('enabled')->default(true);

            $table->string('group')->nullable();

            // null, cookie or session
            $table->string('remember')->nullable();
            $table->integer('remember_lifetime')->nullable(); // default(3600)

            // Use a name that is display in the admin user interface
            $table->string('name', 200);

            $table->boolean('system')->default(false);

            $table->timestamps();

            $table->boolean('hide_if_not_requested')->default(false);

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
        Schema::dropIfExists('auth_modules');
    }
}
