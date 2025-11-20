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
        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->bigIncrements('id_transaksi_detail');
            $table->enum('jenis_transaksi', ['topup', 'purchase', 'withdraw'])->default('topup');
            $table->unsignedInteger('jumlah');
            $table->timestamp('tanggal_transaksi');
            $table->decimal('harga', 15, 2);
            $table->unsignedBigInteger('id_transaksi');
            $table->unsignedBigInteger('id_package');
            $table->timestamps();

            $table->foreign('id_transaksi')->references('id_transaksi')->on('transaksi')->cascadeOnDelete();
            $table->foreign('id_package')->references('id_package')->on('game_packages')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_detail');
    }
};
