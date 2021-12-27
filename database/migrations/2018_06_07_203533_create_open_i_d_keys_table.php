<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\OpenIDProvider;
use App\OpenIDKey;
use App\Repository\KeyRepository;

class CreateOpenIDKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('open_i_d_keys', function (Blueprint $table) {
            
            $table->uuid('id')->primary();

            $table->uuid('tenant_id')->index();

            $table->unsignedInteger('provider_id');

            $table->text('public_key');
            $table->text('private_key');
            $table->text('x509')->nullable();

            $table->boolean('active')->default(true);
            
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
        Schema::dropIfExists('open_i_d_keys');
    }
}
