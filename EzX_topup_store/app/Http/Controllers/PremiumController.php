<?php

namespace App\Http\Controllers;

use App\Services\PremiumService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PremiumController extends Controller
{
    public function store(Request $request, PremiumService $premiumService): RedirectResponse
    {
        $user = $request->user();

        try {
            $result = $premiumService->purchase($user);
        } catch (ValidationException $exception) {
            return redirect()
                ->back()
                ->withErrors($exception->errors(), 'premium')
                ->withInput();
        }

        return redirect()
            ->route('user.dashboard', ['tab' => 'upgrade'])
            ->with('premium_success', 'Keanggotaan premium berhasil diaktifkan.')
            ->with('premium_coins_used', $result['coins_used']);
    }
}
