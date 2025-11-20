<?php

use App\Http\Controllers\AdminCoinPurchaseApprovalController;
use App\Http\Controllers\AdminGameTopupApprovalController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CoinPurchaseController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\GameCurrencyController;
use App\Http\Controllers\GamePackageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PremiumController;
use App\Http\Controllers\SuperAdminAdminController;
use App\Http\Controllers\SuperAdminDashboardController;
use App\Http\Controllers\GameTopupController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\UserNotificationController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\ReportController;

use Illuminate\Support\Facades\Route;


Route::get('/', HomeController::class)->name('home');

Route::get('/games/{game:id_game}', [GameController::class, 'show'])->name('games.show');
Route::post('/games/{game}/topups', [GameTopupController::class, 'store'])->name('game-topups.store');
Route::match(['get', 'post'], '/orders/track', [OrderTrackingController::class, 'index'])->name('orders.track');
Route::get('/orders/{transaction_code}/confirm', [OrderTrackingController::class, 'confirm'])->name('orders.confirm');
Route::middleware('auth')->get('/invoices/{transaksi}', [InvoiceController::class, 'show'])->name('invoices.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'show'])->name('login');
    Route::get('/auth', [AuthController::class, 'show'])->name('auth.index');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('/register', [AuthController::class, 'register'])->name('register');
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/coins/purchases/{coinPurchase}/payment-proof/preview', [CoinPurchaseController::class, 'previewPaymentProof'])->name('coins.purchases.payment-proof.preview');
});

Route::post('/game-topups/{gameTopup}/payment-proof', [GameTopupController::class, 'uploadPaymentProof'])->name('game-topups.payment-proof');
Route::get('/game-topups/{gameTopup}/payment-proof/preview', [GameTopupController::class, 'previewPaymentProof'])->name('game-topups.payment-proof.preview');

Route::middleware(['auth', 'role:user'])->group(function () {
    Route::get('/user', [UserDashboardController::class, 'index'])->name('user.dashboard');
    Route::put('/user/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::post('/coins/purchases', [CoinPurchaseController::class, 'store'])->name('coins.purchases.store');
    Route::get('/coins/purchases/{coinPurchase}', [CoinPurchaseController::class, 'show'])->name('coins.purchases.show');
    Route::post('/coins/purchases/{coinPurchase}/payment-proof', [CoinPurchaseController::class, 'uploadPaymentProof'])->name('coins.purchases.payment-proof');
    Route::get('/game-topups/{gameTopup}', [GameTopupController::class, 'show'])->name('game-topups.show');
    Route::post('/user/notifications/read-all', [UserNotificationController::class, 'markAllRead'])->name('user.notifications.read-all');
    Route::post('/user/premium', [PremiumController::class, 'store'])->name('user.premium.store');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/games', [GameController::class, 'store'])->name('games.store');
    Route::put('/games/{game}', [GameController::class, 'update'])->name('games.update');
    Route::delete('/games/{game}', [GameController::class, 'destroy'])->name('games.destroy');

    Route::post('/games/{game}/currencies', [GameCurrencyController::class, 'store'])->name('games.currencies.store');
    Route::put('/currencies/{currency}', [GameCurrencyController::class, 'update'])->name('currencies.update');
    Route::delete('/currencies/{currency}', [GameCurrencyController::class, 'destroy'])->name('currencies.destroy');

    Route::post('/currencies/{currency}/packages', [GamePackageController::class, 'store'])->name('currencies.packages.store');
    Route::put('/packages/{package}', [GamePackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [GamePackageController::class, 'destroy'])->name('packages.destroy');

    Route::patch('/coin-purchases/{coinPurchase}/approve', [AdminCoinPurchaseApprovalController::class, 'approve'])->name('coin-purchases.approve');
    Route::patch('/coin-purchases/{coinPurchase}/reject', [AdminCoinPurchaseApprovalController::class, 'reject'])->name('coin-purchases.reject');
    Route::patch('/game-topups/{gameTopup}/approve', [AdminGameTopupApprovalController::class, 'approve'])->name('game-topups.approve');
    Route::patch('/game-topups/{gameTopup}/reject', [AdminGameTopupApprovalController::class, 'reject'])->name('game-topups.reject');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

       Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');

    Route::post('/admins', [SuperAdminAdminController::class, 'store'])->name('admins.store');
    Route::put('/admins/{admin}', [SuperAdminAdminController::class, 'update'])->name('admins.update');
    Route::delete('/admins/{admin}', [SuperAdminAdminController::class, 'destroy'])->name('admins.destroy');
});
