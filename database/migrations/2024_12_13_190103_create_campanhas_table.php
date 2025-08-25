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
        Schema::create('campanhas', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->index();
            $table->string('user_id')->index();
            $table->string('name');
            $table->string('provider');
            $table->integer('registros')->default(0);
            $table->integer('success_saldo')->default(0);
            $table->integer('success_sim')->default(0);
            $table->integer('processados')->default(0);
            $table->integer('sit')->default(0);
            $table->string('id_wa');
            $table->text('id_config');
            $table->integer('del')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campanhas');
    }
};
