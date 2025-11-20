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
        Schema::create('game_packages', function (Blueprint $table) {
            $table->bigIncrements('id_package');
            $table->unsignedInteger('amount');
            $table->decimal('price', 15, 2);
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('id_currency');
            $table->timestamps();

            $table->foreign('id_currency')->references('id_currency')->on('game_currencies')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_packages');
    }
};
