<?php

namespace App\Http\Controllers;

use App\Models\GameCurrency;
use App\Models\GamePackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GamePackageController extends Controller
{
    public function store(Request $request, GameCurrency $currency): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $currency->packages()->create($data);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Paket baru berhasil ditambahkan.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'packages');
    }

    public function update(Request $request, GamePackage $package): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $package->update($data);

        return redirect()->route('admin.dashboard')
            ->with('status', 'Paket berhasil diperbarui.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'packages');
    }

    public function destroy(GamePackage $package): RedirectResponse
    {
        $package->delete();

        return redirect()->route('admin.dashboard')
            ->with('status', 'Paket berhasil dihapus.')
            ->with('admin_active_tab', 'games')
            ->with('admin_active_sub_tab', 'packages');
    }
}
