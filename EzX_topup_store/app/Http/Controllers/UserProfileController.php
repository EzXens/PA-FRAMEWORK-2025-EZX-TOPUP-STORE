<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserProfileController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $action = $request->input('action', 'account');

        if ($action === 'security') {
            $validated = $request->validate([
                'password' => ['required', 'confirmed', 'min:8'],
            ], [
                'password.required' => 'Password baru harus diisi.',
                'password.confirmed' => 'Konfirmasi password tidak sesuai.',
                'password.min' => 'Password minimal 8 karakter.',
            ]);

            $user->update([
                'password' => $validated['password'],
            ]);

            return redirect()->route('user.dashboard')
                ->with('status_security', 'Password berhasil diperbarui.')
                ->with('user_active_tab', 'profile');
        }

        if ($action === 'photos') {
            $validated = $request->validate([
                'foto_profil' => ['nullable', 'image', 'max:2048'],
                'background_profil' => ['nullable', 'image', 'max:4096'],
                'remove_foto_profil' => ['nullable', 'boolean'],
                'remove_background_profil' => ['nullable', 'boolean'],
            ], [
                'foto_profil.image' => 'Foto profil harus berupa gambar.',
                'foto_profil.max' => 'Foto profil maksimal 2MB.',
                'background_profil.image' => 'Foto latar belakang harus berupa gambar.',
                'background_profil.max' => 'Foto latar belakang maksimal 4MB.',
            ]);

            $removeProfilePhoto = $request->boolean('remove_foto_profil');
            $removeBackgroundPhoto = $request->boolean('remove_background_profil');

            if ($removeProfilePhoto) {
                if ($user->foto_profil) {
                    Storage::disk('public')->delete($user->foto_profil);
                }
                $user->foto_profil = null;
            } elseif ($request->hasFile('foto_profil')) {
                if ($user->foto_profil) {
                    Storage::disk('public')->delete($user->foto_profil);
                }

                $user->foto_profil = $request->file('foto_profil')->store('profiles', 'public');
            }

            if ($removeBackgroundPhoto) {
                if ($user->background_profil) {
                    Storage::disk('public')->delete($user->background_profil);
                }
                $user->background_profil = null;
            } elseif ($request->hasFile('background_profil')) {
                if ($user->background_profil) {
                    Storage::disk('public')->delete($user->background_profil);
                }

                $user->background_profil = $request->file('background_profil')->store('profiles/backgrounds', 'public');
            }

            $user->save();

            return redirect()->route('user.dashboard')
                ->with('status_photos', 'Foto profil berhasil diperbarui.')
                ->with('user_active_tab', 'profile');
        }

        $validated = $request->validate([
            'nama_lengkap' => ['nullable', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username,' . $user->id_user . ',id_user'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id_user . ',id_user'],
            'nomor_telepon' => ['nullable', 'string', 'max:30'],
            'tanggal_lahir' => ['nullable', 'date'],
            'bio' => ['nullable', 'string', 'max:500'],
        ], [
            'username.required' => 'Username harus diisi.',
            'username.unique' => 'Username sudah digunakan.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        $user->update($validated);

        return redirect()->route('user.dashboard')
            ->with('status_account', 'Informasi akun berhasil diperbarui.')
            ->with('user_active_tab', 'profile');
    }
}
