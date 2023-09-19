<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuthChainSubjectLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('authchain_subject_links', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('tenant_id')->index();

            $table->uuid('user_id')->index();

            $table->string('subject_type', 100);
            $table->uuid('subject_module')->nullable();

            // Refers to identifier in authchain_subjects
            $table->string('subject_id', 100);

            $table->unique(['subject_type', 'subject_module', 'subject_id']);

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
        Schema::drop('authchain_subject_links');
    }
}
