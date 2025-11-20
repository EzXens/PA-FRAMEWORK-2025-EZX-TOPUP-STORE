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
        Schema::create('game_currencies', function (Blueprint $table) {
            $table->bigIncrements('id_currency');
            $table->string('currency_name');
            $table->string('gambar_currency')->nullable();
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('id_game');
            $table->timestamps();

            $table->foreign('id_game')->references('id_game')->on('games')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_currencies');
    }
};
