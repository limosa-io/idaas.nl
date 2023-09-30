<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthChainModuleResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        //if (!Schema::hasTable('authchain_ui_settings'))
        Schema::create('module_results', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('tenant_id');

            // Store module results in database? Altijd. With session_id, subject_id, user_id, module_id, user-agent (of link met session table), result.   With Session::getId()
            $table->uuid('session_or_cookie_id');

            $table->boolean('session');

            $table->uuid('subject_id');
            $table->uuid('user_id')->nullable();
            $table->uuid('module_id');

            $table->string('user_agent');

            // the module result
            $table->text('module_result');

            $table->index(['tenant_id', 'id']);
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->dateTime('expires_at')->nullable();

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
        Schema::drop('module_results');
    }
}
