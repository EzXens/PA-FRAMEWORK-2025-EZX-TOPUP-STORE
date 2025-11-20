<?php

namespace Tests\Feature;

use App\Models\CoinPurchase;
use App\Models\Koin;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use App\Notifications\CoinPurchaseStatusNotification;
use Tests\TestCase;

class CoinPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_coin_purchase_and_redirect_to_confirmation(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        Koin::factory()->create([
            'id_user' => $user->id_user,
            'jumlah_koin' => 0,
        ]);

        $this->actingAs($user);
        Session::start();

        $response = $this->post(route('coins.purchases.store'), [
                '_token' => Session::token(),
                'package_key' => '10k',
                'payment_method' => 'qris',
            ]);

        $response->assertRedirect();

        $purchase = CoinPurchase::first();

        $this->assertNotNull($purchase);
        $response->assertRedirect(route('coins.purchases.show', $purchase));
        $this->assertSame($user->id_user, $purchase->id_user);
        $this->assertSame('pending', $purchase->status);
        $this->assertEquals(1, $user->notifications()->count());
        $this->assertEquals(1, $user->unreadNotifications()->count());
    }

    public function test_admin_can_approve_coin_purchase_and_increment_user_balance(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $wallet = Koin::factory()->create([
            'id_user' => $user->id_user,
            'jumlah_koin' => 0,
        ]);

        $transaksi = Transaksi::create([
            'jenis_transaksi' => 'purchase',
            'jumlah' => 100,
            'harga' => 10_000,
            'status' => 'pending',
            'id_user' => $user->id_user,
            'tanggal_transaksi' => now(),
        ]);

        $purchase = CoinPurchase::create([
            'id_user' => $user->id_user,
            'id_transaksi' => $transaksi->id_transaksi,
            'package_key' => '10k',
            'coin_amount' => 100,
            'price_idr' => 10_000,
            'payment_method' => 'qris',
            'status' => 'pending',
            'payment_meta' => ['label' => 'QRIS'],
        ]);

        $this->actingAs($admin);
        Session::start();

        $response = $this->patch(route('admin.coin-purchases.approve', $purchase), [
            '_token' => Session::token(),
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertEquals(100, $wallet->refresh()->jumlah_koin);
        $this->assertEquals('approved', $purchase->refresh()->status);
        $this->assertEquals('completed', $transaksi->refresh()->status);
        $this->assertEquals(1, $user->notifications()->count());
        $this->assertEquals(1, $user->unreadNotifications()->count());
    }

    public function test_user_can_mark_all_notifications_as_read(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
        ]);

        $transaksi = Transaksi::create([
            'jenis_transaksi' => 'purchase',
            'jumlah' => 100,
            'harga' => 10000,
            'status' => 'pending',
            'id_user' => $user->id_user,
            'tanggal_transaksi' => now(),
        ]);

        $purchase = CoinPurchase::create([
            'id_user' => $user->id_user,
            'id_transaksi' => $transaksi->id_transaksi,
            'package_key' => '10k',
            'coin_amount' => 100,
            'price_idr' => 10000,
            'payment_method' => 'qris',
            'status' => 'pending',
        ]);

        $user->notify(new CoinPurchaseStatusNotification($purchase->fresh(), 'pending'));
        $user->notify(new CoinPurchaseStatusNotification($purchase->fresh(), 'approved'));

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $this->actingAs($user);
        Session::start();

        $response = $this->post(route('user.notifications.read-all'), [
            '_token' => Session::token(),
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }
}
