<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nama_lengkap')->nullable()->after('username');
            $table->string('nomor_telepon')->nullable()->after('nama_lengkap');
            $table->date('tanggal_lahir')->nullable()->after('nomor_telepon');
            $table->text('bio')->nullable()->after('tanggal_lahir');
            $table->boolean('two_factor_enabled')->default(false)->after('background_profil');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'nama_lengkap',
                'nomor_telepon',
                'tanggal_lahir',
                'bio',
                'two_factor_enabled',
            ]);
        });
    }
};
