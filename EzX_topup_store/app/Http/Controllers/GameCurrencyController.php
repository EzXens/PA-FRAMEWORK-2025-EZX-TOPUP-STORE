<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameCurrency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameCurrencyController extends Controller
{
    public function store(Request $request, Game $game): RedirectResponse
    {
        $data = $request->validate([
            'currency_name' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'gambar_currency' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('gambar_currency')) {
            $data['gambar_currency'] = $request->file('gambar_currency')->store('currencies', 'public');
        }

        $game->currencies()->create($data);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Currency baru berhasil ditambahkan.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'currencies');
    }

    public function update(Request $request, GameCurrency $currency): RedirectResponse
    {
        $data = $request->validate([
            'currency_name' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'gambar_currency' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('gambar_currency')) {
            if ($currency->gambar_currency) {
                Storage::disk('public')->delete($currency->gambar_currency);
            }
            $data['gambar_currency'] = $request->file('gambar_currency')->store('currencies', 'public');
        }

        $currency->update($data);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Currency berhasil diperbarui.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'currencies');
    }

    public function destroy(GameCurrency $currency): RedirectResponse
    {
        if ($currency->gambar_currency) {
            Storage::disk('public')->delete($currency->gambar_currency);
        }

        $currency->delete();

        return redirect()->route('admin.dashboard')
            ->with('status', 'Currency berhasil dihapus.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'currencies');
    }
}
