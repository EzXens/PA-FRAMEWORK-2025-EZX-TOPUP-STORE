<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_topups', function (Blueprint $table) {
            $table->bigIncrements('id_game_topup');
            $table->string('transaction_code')->unique();
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_game');
            $table->unsignedBigInteger('id_currency');
            $table->unsignedBigInteger('id_package');
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->unsignedBigInteger('price_idr');
            $table->string('payment_method');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('account_data');
            $table->string('contact_email')->nullable();
            $table->string('contact_whatsapp')->nullable();
            $table->json('payment_meta')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('id_game')->references('id_game')->on('games')->cascadeOnDelete();
            $table->foreign('id_currency')->references('id_currency')->on('game_currencies')->cascadeOnDelete();
            $table->foreign('id_package')->references('id_package')->on('game_packages')->cascadeOnDelete();
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_topups');
    }
};
