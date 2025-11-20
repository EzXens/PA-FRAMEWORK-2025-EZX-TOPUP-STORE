<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\GameCurrency;
use App\Models\GamePackage;
use App\Models\GameTopup;
use App\Models\Koin;
use App\Models\Transaksi;
use App\Models\TransaksiDetail;
use App\Models\User;
use App\Notifications\GameTopupStatusNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class GameTopupTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_game_topup_and_view_confirmation(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $game = Game::factory()->create([
            'nama_game' => 'Mobile Legends',
        ]);

        $currency = GameCurrency::factory()->create([
            'id_game' => $game->id_game,
            'currency_name' => 'Diamonds',
            'deskripsi' => 'Paket diamond Mobile Legends',
        ]);

        $package = GamePackage::factory()->create([
            'id_currency' => $currency->id_currency,
            'amount' => 257,
            'price' => 72_000,
            'deskripsi' => '257 Diamonds',
        ]);

        $this->actingAs($user);
        Session::start();

        $response = $this->post(route('game-topups.store', $game), [
            '_token' => Session::token(),
            'currency' => $currency->id_currency,
            'package' => $package->id_package,
            'payment_method' => 'qris',
            'account' => [
                'player_id' => '123456789',
                'server_id' => '1123',
            ],
            'email' => 'buyer@example.com',
            'whatsapp' => '08123456789',
        ]);

        $response->assertRedirect();

        $topup = GameTopup::first();

        $this->assertNotNull($topup);
        $response->assertRedirect(route('game-topups.show', $topup));
        $this->assertSame($user->id_user, $topup->id_user);
        $this->assertSame('pending', $topup->status);
        $this->assertSame('qris', $topup->payment_method);
        $this->assertNotNull($topup->id_transaksi);

        $transaksi = $topup->transaksi;
        $this->assertNotNull($transaksi);
        $this->assertSame('topup', $transaksi->jenis_transaksi);
        $this->assertDatabaseHas('transaksi_detail', [
            'id_transaksi' => $transaksi->id_transaksi,
            'id_package' => $package->id_package,
        ]);

        Notification::assertSentTo($user, GameTopupStatusNotification::class);
    }

    public function test_guest_can_create_game_topup_without_login(): void
    {
        Notification::fake();

        $game = Game::factory()->create([
            'nama_game' => 'Mobile Legends',
        ]);

        $currency = GameCurrency::factory()->create([
            'id_game' => $game->id_game,
            'currency_name' => 'Diamonds',
        ]);

        $package = GamePackage::factory()->create([
            'id_currency' => $currency->id_currency,
            'amount' => 86,
            'price' => 25_000,
        ]);

        Session::start();

        $response = $this->post(route('game-topups.store', $game), [
            '_token' => Session::token(),
            'currency' => $currency->id_currency,
            'package' => $package->id_package,
            'payment_method' => 'qris',
            'account' => [
                'player_id' => '123456789',
                'server_id' => '1234',
            ],
            'email' => 'guest@example.com',
            'whatsapp' => '0812345678',
        ]);

        $response->assertRedirect();

        $topup = GameTopup::first();
        $this->assertNotNull($topup);
        $this->assertNull($topup->id_user);
        $this->assertSame('qris', $topup->payment_method);
        $this->assertSame('pending', $topup->status);

        $expectedRedirect = route('orders.track', ['transaction_code' => $topup->transaction_code]);
        $response->assertRedirect($expectedRedirect);

        $transaksi = $topup->transaksi;
        $this->assertNotNull($transaksi);
        $this->assertNull($transaksi->id_user);

        Notification::assertNothingSent();
    }

    public function test_admin_can_approve_game_topup(): void
    {
        Notification::fake();

        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $game = Game::factory()->create(['nama_game' => 'Mobile Legends']);
        $currency = GameCurrency::factory()->create([
            'id_game' => $game->id_game,
            'currency_name' => 'Diamonds',
        ]);
        $package = GamePackage::factory()->create([
            'id_currency' => $currency->id_currency,
            'amount' => 257,
            'price' => 72_000,
        ]);

        $transaksi = Transaksi::create([
            'jenis_transaksi' => 'topup',
            'jumlah' => $package->amount,
            'harga' => $package->price,
            'status' => 'pending',
            'id_user' => $user->id_user,
            'tanggal_transaksi' => now(),
        ]);

        TransaksiDetail::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'jenis_transaksi' => 'topup',
            'jumlah' => 1,
            'tanggal_transaksi' => now(),
            'harga' => $package->price,
            'id_package' => $package->id_package,
        ]);

        $topup = GameTopup::create([
            'id_user' => $user->id_user,
            'id_game' => $game->id_game,
            'id_currency' => $currency->id_currency,
            'id_package' => $package->id_package,
            'id_transaksi' => $transaksi->id_transaksi,
            'price_idr' => $package->price,
            'payment_method' => 'qris',
            'status' => 'pending',
            'account_data' => [
                'player_id' => '123456789',
                'server_id' => '1123',
            ],
            'payment_meta' => config('coin.payment_methods.qris'),
        ]);

        $this->actingAs($admin);
        Session::start();

        $response = $this->patch(route('admin.game-topups.approve', $topup), [
            '_token' => Session::token(),
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertEquals('approved', $topup->fresh()->status);
        $this->assertNotNull($topup->fresh()->approved_at);
        $this->assertEquals('completed', $transaksi->fresh()->status);

        Notification::assertSentTo($user, GameTopupStatusNotification::class);
    }

    public function test_user_dashboard_displays_game_topup_history(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $game = Game::factory()->create(['nama_game' => 'Mobile Legends']);
        $currency = GameCurrency::factory()->create([
            'id_game' => $game->id_game,
            'currency_name' => 'Diamonds',
        ]);
        $package = GamePackage::factory()->create([
            'id_currency' => $currency->id_currency,
            'amount' => 257,
            'price' => 72_000,
        ]);

        $transaksi = Transaksi::create([
            'jenis_transaksi' => 'topup',
            'jumlah' => $package->amount,
            'harga' => $package->price,
            'status' => 'completed',
            'id_user' => $user->id_user,
            'tanggal_transaksi' => now(),
        ]);

        TransaksiDetail::create([
            'id_transaksi' => $transaksi->id_transaksi,
            'jenis_transaksi' => 'topup',
            'jumlah' => 1,
            'tanggal_transaksi' => now(),
            'harga' => $package->price,
            'id_package' => $package->id_package,
        ]);

        GameTopup::create([
            'id_user' => $user->id_user,
            'id_game' => $game->id_game,
            'id_currency' => $currency->id_currency,
            'id_package' => $package->id_package,
            'id_transaksi' => $transaksi->id_transaksi,
            'price_idr' => $package->price,
            'payment_method' => 'qris',
            'status' => 'approved',
            'account_data' => [
                'player_id' => '123456789',
                'server_id' => '1123',
            ],
            'payment_meta' => config('coin.payment_methods.qris'),
            'approved_at' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('user.dashboard', ['tab' => 'transactions']));

        $response->assertOk();
        $response->assertSee('Mobile Legends');
        $response->assertSee('257');
        $response->assertSee('Rp 72.000');
        $response->assertSee('approved');
    }

    public function test_user_can_pay_with_coins_and_balance_is_deducted(): void
    {
        Notification::fake();

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $initialCoins = 2_000;

        Koin::factory()->create([
            'id_user' => $user->id_user,
            'jumlah_koin' => $initialCoins,
        ]);

        $game = Game::factory()->create(['nama_game' => 'Genshin Impact']);
        $currency = GameCurrency::factory()->create([
            'id_game' => $game->id_game,
            'currency_name' => 'Genesis Crystal',
        ]);
        $package = GamePackage::factory()->create([
            'id_currency' => $currency->id_currency,
            'amount' => 980,
            'price' => 150_000,
        ]);

        $expectedCoins = (int) ceil($package->price / config('coin.coin_to_idr_rate'));

        $this->actingAs($user);
        Session::start();

        $response = $this->post(route('game-topups.store', $game), [
            '_token' => Session::token(),
            'currency' => $currency->id_currency,
            'package' => $package->id_package,
            'payment_method' => 'coins',
            'account' => [
                'uid' => '800123456',
                'server' => 'Asia',
            ],
            'email' => 'player@example.com',
        ]);

        $response->assertRedirect();

        $wallet = $user->fresh()->koin;
        $this->assertNotNull($wallet);
        $this->assertEquals(max(0, $initialCoins - $expectedCoins), $wallet->jumlah_koin);

        $topup = GameTopup::latest('id_game_topup')->first();
        $this->assertNotNull($topup);
        $this->assertSame('coins', $topup->payment_method);
        $this->assertEquals($expectedCoins, $topup->payment_meta['coins_used']);

        Notification::assertSentTo($user, GameTopupStatusNotification::class);
    }
}
