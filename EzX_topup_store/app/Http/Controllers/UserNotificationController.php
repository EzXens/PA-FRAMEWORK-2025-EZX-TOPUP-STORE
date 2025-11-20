<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserNotificationController extends Controller
{
    public function markAllRead(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('status', 'Semua notifikasi telah ditandai dibaca.');
    }
}
