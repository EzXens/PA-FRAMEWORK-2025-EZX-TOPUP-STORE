<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SuperAdminAdminController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        return redirect()->route('superadmin.dashboard', ['tab' => 'accounts'])->with('status', 'Admin baru berhasil dibuat.');
    }

    public function update(Request $request, User $admin): RedirectResponse
    {
        $this->ensureAdmin($admin);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $admin->id_user . ',id_user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $admin->id_user . ',id_user'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'username' => $data['username'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $admin->update($payload);

        return redirect()->route('superadmin.dashboard', ['tab' => 'accounts'])->with('status', 'Data admin berhasil diperbarui.');
    }

    public function destroy(User $admin): RedirectResponse
    {
        $this->ensureAdmin($admin);

        if (auth()->id() === $admin->id_user) {
            return redirect()->route('superadmin.dashboard', ['tab' => 'accounts'])
                ->withErrors(['admin' => 'Tidak dapat menghapus akun yang sedang digunakan.']);
        }

        $admin->delete();

        return redirect()->route('superadmin.dashboard', ['tab' => 'accounts'])->with('status', 'Admin berhasil dihapus.');
    }

    private function ensureAdmin(User $admin): void
    {
        if ($admin->role !== 'admin') {
            abort(403, 'Aksi hanya untuk akun admin.');
        }
    }
}
