<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carteiras', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->boolean('is_campanha')->default(false);
            $table->string('id_campanha')->index();
            $table->string('user_id')->index();
            $table->string('cpf')->index();
            $table->string('nome')->index();
            $table->string('nasc');
            $table->string('telefone')->index();
            $table->string('saldo_total')->nullable();
            $table->string('saldo_lib')->nullable();
            $table->text('payload_sim')->nullable();
            $table->integer('sit')->default(0);
            $table->integer('sit_sim')->default(0);
            $table->integer('sit_prop')->default(0);
            $table->string('log')->nullable();
            $table->string('log_sim')->nullable();
            $table->string('log_prop')->nullable();
            $table->string('id_proposal')->nullable();
            $table->integer('qnt_proposta')->default(0)->index();
            $table->string('id_config')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carteiras');
    }
};
