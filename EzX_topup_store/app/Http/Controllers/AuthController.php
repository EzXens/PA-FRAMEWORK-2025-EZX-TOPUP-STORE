<?php

namespace App\Http\Controllers;

use App\Models\Koin;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function show(): View
    {
        return view('auth.index');
    }

    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'register')
                ->withInput()
                ->with('auth_tab', 'register');
        }

        $data = $validator->validated();

        $user = User::create([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        Koin::create([
            'id_user' => $user->id_user,
            'jumlah_koin' => 0,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $request->session()->flash('auth_success', 'Registrasi berhasil! Selamat datang, ' . $user->username . '.');

        return $this->redirectAfterAuthentication($request, $user);
    }

    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator, 'login')
                ->withInput()
                ->with('auth_tab', 'login');
        }

        $credentials = $validator->validated();

        $loginField = filter_var($credentials['identifier'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (! Auth::attempt([
            $loginField => $credentials['identifier'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'))) {
            return back()->withErrors([
                'identifier' => 'Kredensial tidak valid. Silakan periksa kembali.',
            ], 'login')->withInput($request->only('identifier', 'remember'))->with('auth_tab', 'login');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        $request->session()->flash('auth_success', 'Login berhasil! Senang melihatmu lagi, ' . $user->username . '.');

        return $this->redirectAfterAuthentication($request, $user);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    private function resolveDashboardRoute(string $role): string
    {
        $normalizedRole = strtolower($role);

        return match ($normalizedRole) {
            'admin' => route('admin.dashboard'),
            'super_admin' => route('superadmin.dashboard'),
            default => route('user.dashboard'),
        };
    }

    private function redirectAfterAuthentication(Request $request, User $user): RedirectResponse
    {
        $fallback = $this->resolveDashboardRoute($user->role);
        $intended = $request->session()->pull('url.intended');

        if ($intended && $this->isIntendedAllowedForRole($intended, $user->role)) {
            return redirect()->to($intended);
        }

        return redirect()->to($fallback);
    }

    private function isIntendedAllowedForRole(string $intended, string $role): bool
    {
        $path = parse_url($intended, PHP_URL_PATH) ?? '/';

        $normalizedRole = strtolower($role);

        return match ($normalizedRole) {
            'admin' => $this->pathStartsWith($path, ['/admin']),
            'super_admin' => $this->pathStartsWith($path, ['/superadmin']),
            default => true,
        };
    }

    private function pathStartsWith(string $path, array $prefixes): bool
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
