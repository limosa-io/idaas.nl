<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCloudFunctionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cloud_functions', function (Blueprint $table) {
            
            $table->uuid('id')->primary();
            
            $table->string('type')->index();

            $table->string('display_name');

            $table->boolean('active')->default(true);

            $table->integer('order')->default(0);

            $table->text('code')->nullable();

            $table->text('variables')->nullable();
            
            $table->uuid('tenant_id')->index();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');

            $table->timestamp('run_at', 0)->nullable();

            $table->boolean('is_sequence')->default(false)->index();

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
        Schema::dropIfExists('cloud_functions');
    }
}
