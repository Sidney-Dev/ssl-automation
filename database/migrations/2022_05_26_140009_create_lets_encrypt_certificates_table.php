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
        Schema::create('lets_encrypt_certificates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->softDeletes();
            $table->string('domain')->unique();
            $table->timestamp('last_renewed_at')->nullable();
            $table->boolean('created')->default(false);
            $table->string('fullchain_path')->nullable();
            $table->string('chain_path')->nullable();
            $table->string('cert_path')->nullable();
            $table->string('privkey_path')->nullable();
            $table->enum('status',['error','pending','success']);
            $table->bigInteger('slug')->nullable();
            $table->string('environmentID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lets_encrypt_certificates');
    }
};
