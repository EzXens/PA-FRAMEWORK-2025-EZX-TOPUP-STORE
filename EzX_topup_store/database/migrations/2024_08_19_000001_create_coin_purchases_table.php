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
        Schema::create('coin_purchases', function (Blueprint $table) {
            $table->bigIncrements('id_coin_purchase');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_transaksi')->nullable();
            $table->string('transaction_code')->unique();
            $table->string('package_key');
            $table->unsignedInteger('coin_amount');
            $table->unsignedBigInteger('price_idr');
            $table->string('payment_method');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('payment_meta')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->cascadeOnDelete();
            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_purchases');
    }
};
