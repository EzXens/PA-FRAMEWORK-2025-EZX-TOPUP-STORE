<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use App\Services\PremiumService;

class GameController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_game' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('games', 'public');
        }

        Game::create($data);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Game baru berhasil ditambahkan.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'games');
    }

    public function update(Request $request, Game $game): RedirectResponse
    {
        $data = $request->validate([
            'nama_game' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'gambar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('gambar')) {
            if ($game->gambar) {
                Storage::disk('public')->delete($game->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('games', 'public');
        }

        $game->update($data);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Game berhasil diperbarui.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'games');
    }

    public function destroy(Game $game): RedirectResponse
    {
        if ($game->gambar) {
            Storage::disk('public')->delete($game->gambar);
        }

        $game->delete();

        return redirect()->route('admin.dashboard')
            ->with('status', 'Game berhasil dihapus.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'games');
    }

    public function show(Request $request, Game $game): View
    {
        $game->load(['currencies.packages']);

        $accountFieldConfig = config('game_account_fields');
        $accountFieldKey = Str::slug($game->nama_game);
        $accountFieldDefinition = $accountFieldConfig[$accountFieldKey] ?? $accountFieldConfig['default'];

        $user = $request->user();
        $premiumService = app(PremiumService::class);
        $premiumActive = $premiumService->isPremiumActive($user);
        $premiumDiscount = $premiumService->calculateDiscountPercentage($user);

        return view('games.show', [
            'game' => $game,
            'accountFieldDefinition' => $accountFieldDefinition,
            'paymentMethods' => config('coin.payment_methods', []),
            'coinBalance' => optional($user)->coin_balance ?? 0,
            'coinRate' => config('coin.coin_to_idr_rate', 100),
            'premiumActive' => $premiumActive,
            'premiumDiscount' => $premiumDiscount,
        ]);
    }
}
