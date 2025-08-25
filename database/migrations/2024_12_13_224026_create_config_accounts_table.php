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
        Schema::create('config_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->string('uuid')->index();
            $table->string('email')->nullable();
            $table->string('senha')->nullable();
            $table->string('audience')->nullable();
            $table->string('client_id')->nullable();
            $table->string('fees_id')->nullable();
            $table->string('bank')->nullable();
            $table->text('link_id')->nullable();
            $table->text('token');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('config_accounts');
    }
};
