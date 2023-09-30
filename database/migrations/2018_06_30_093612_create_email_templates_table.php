<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->uuid('parent_id')->nullable();

            $table->string('type')->default('generic'); // default, password-forgotten,

            $table->string('name');

            $table->string('subject');

            // mustache template
            $table->text('body');

            $table->text('body_inlined');

            // mustache template
            $table->text('body_plain')->nullable();

            $table->boolean('default')->default(false);

            $table->uuid('tenant_id')->index();

            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // TODO: Insert defailt email templates. Base layout (benefit from main colors), Passwordless template ...

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_templates');
    }
}
