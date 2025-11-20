<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->makeColumnsNullableSqlite();
            return;
        }

        Schema::table('game_topups', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        DB::statement('ALTER TABLE game_topups MODIFY id_user BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE transaksi MODIFY id_user BIGINT UNSIGNED NULL');

        Schema::table('game_topups', function (Blueprint $table) {
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $this->makeColumnsNotNullSqlite();
            return;
        }

        Schema::table('game_topups', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->dropForeign(['id_user']);
        });

        DB::statement('ALTER TABLE game_topups MODIFY id_user BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE transaksi MODIFY id_user BIGINT UNSIGNED NOT NULL');

        Schema::table('game_topups', function (Blueprint $table) {
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete();
        });

        Schema::table('transaksi', function (Blueprint $table) {
            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    protected function makeColumnsNullableSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys=off');

        DB::statement(<<<'SQL'
CREATE TABLE __tmp_transaksi (
    id_transaksi INTEGER PRIMARY KEY AUTOINCREMENT,
    jenis_transaksi VARCHAR NOT NULL DEFAULT 'topup',
    jumlah INTEGER NOT NULL,
    tanggal_transaksi DATETIME NOT NULL,
    harga NUMERIC(15, 2) NOT NULL,
    status VARCHAR NOT NULL DEFAULT 'pending',
    id_user INTEGER NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL
)
SQL);

        DB::statement('INSERT INTO __tmp_transaksi (id_transaksi, jenis_transaksi, jumlah, tanggal_transaksi, harga, status, id_user, created_at, updated_at) SELECT id_transaksi, jenis_transaksi, jumlah, tanggal_transaksi, harga, status, id_user, created_at, updated_at FROM transaksi');
        DB::statement('DROP TABLE transaksi');
        DB::statement('ALTER TABLE __tmp_transaksi RENAME TO transaksi');

        DB::statement(<<<'SQL'
CREATE TABLE __tmp_game_topups (
    id_game_topup INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_code VARCHAR NOT NULL,
    id_user INTEGER NULL,
    id_game INTEGER NOT NULL,
    id_currency INTEGER NOT NULL,
    id_package INTEGER NOT NULL,
    id_transaksi INTEGER NULL,
    price_idr INTEGER NOT NULL,
    payment_method VARCHAR NOT NULL,
    status VARCHAR NOT NULL DEFAULT 'pending',
    account_data TEXT NOT NULL,
    contact_email VARCHAR NULL,
    contact_whatsapp VARCHAR NULL,
    payment_meta TEXT NULL,
    approved_at DATETIME NULL,
    rejected_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE SET NULL,
    FOREIGN KEY (id_game) REFERENCES games(id_game) ON DELETE CASCADE,
    FOREIGN KEY (id_currency) REFERENCES game_currencies(id_currency) ON DELETE CASCADE,
    FOREIGN KEY (id_package) REFERENCES game_packages(id_package) ON DELETE CASCADE,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE SET NULL
)
SQL);

        DB::statement('INSERT INTO __tmp_game_topups (id_game_topup, transaction_code, id_user, id_game, id_currency, id_package, id_transaksi, price_idr, payment_method, status, account_data, contact_email, contact_whatsapp, payment_meta, approved_at, rejected_at, created_at, updated_at) SELECT id_game_topup, transaction_code, id_user, id_game, id_currency, id_package, id_transaksi, price_idr, payment_method, status, account_data, contact_email, contact_whatsapp, payment_meta, approved_at, rejected_at, created_at, updated_at FROM game_topups');
        DB::statement('DROP TABLE game_topups');
        DB::statement('ALTER TABLE __tmp_game_topups RENAME TO game_topups');
        DB::statement('CREATE UNIQUE INDEX game_topups_transaction_code_unique ON game_topups(transaction_code)');

        DB::statement('PRAGMA foreign_keys=on');
    }

    protected function makeColumnsNotNullSqlite(): void
    {
        DB::statement('PRAGMA foreign_keys=off');

        DB::statement(<<<'SQL'
CREATE TABLE __tmp_transaksi (
    id_transaksi INTEGER PRIMARY KEY AUTOINCREMENT,
    jenis_transaksi VARCHAR NOT NULL DEFAULT 'topup',
    jumlah INTEGER NOT NULL,
    tanggal_transaksi DATETIME NOT NULL,
    harga NUMERIC(15, 2) NOT NULL,
    status VARCHAR NOT NULL DEFAULT 'pending',
    id_user INTEGER NOT NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
)
SQL);

        DB::statement('INSERT INTO __tmp_transaksi (id_transaksi, jenis_transaksi, jumlah, tanggal_transaksi, harga, status, id_user, created_at, updated_at) SELECT id_transaksi, jenis_transaksi, jumlah, tanggal_transaksi, harga, status, id_user, created_at, updated_at FROM transaksi');
        DB::statement('DROP TABLE transaksi');
        DB::statement('ALTER TABLE __tmp_transaksi RENAME TO transaksi');

        DB::statement(<<<'SQL'
CREATE TABLE __tmp_game_topups (
    id_game_topup INTEGER PRIMARY KEY AUTOINCREMENT,
    transaction_code VARCHAR NOT NULL,
    id_user INTEGER NOT NULL,
    id_game INTEGER NOT NULL,
    id_currency INTEGER NOT NULL,
    id_package INTEGER NOT NULL,
    id_transaksi INTEGER NULL,
    price_idr INTEGER NOT NULL,
    payment_method VARCHAR NOT NULL,
    status VARCHAR NOT NULL DEFAULT 'pending',
    account_data TEXT NOT NULL,
    contact_email VARCHAR NULL,
    contact_whatsapp VARCHAR NULL,
    payment_meta TEXT NULL,
    approved_at DATETIME NULL,
    rejected_at DATETIME NULL,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_game) REFERENCES games(id_game) ON DELETE CASCADE,
    FOREIGN KEY (id_currency) REFERENCES game_currencies(id_currency) ON DELETE CASCADE,
    FOREIGN KEY (id_package) REFERENCES game_packages(id_package) ON DELETE CASCADE,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE SET NULL
)
SQL);

        DB::statement('INSERT INTO __tmp_game_topups (id_game_topup, transaction_code, id_user, id_game, id_currency, id_package, id_transaksi, price_idr, payment_method, status, account_data, contact_email, contact_whatsapp, payment_meta, approved_at, rejected_at, created_at, updated_at) SELECT id_game_topup, transaction_code, id_user, id_game, id_currency, id_package, id_transaksi, price_idr, payment_method, status, account_data, contact_email, contact_whatsapp, payment_meta, approved_at, rejected_at, created_at, updated_at FROM game_topups');
        DB::statement('DROP TABLE game_topups');
        DB::statement('ALTER TABLE __tmp_game_topups RENAME TO game_topups');
        DB::statement('CREATE UNIQUE INDEX game_topups_transaction_code_unique ON game_topups(transaction_code)');

        DB::statement('PRAGMA foreign_keys=on');
    }
};
