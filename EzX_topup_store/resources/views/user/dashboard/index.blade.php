<x-layouts.app>

@php
    $user = $user ?? auth()->user();
    $coinPurchases = $coinPurchases ?? collect();
    $gameTopups = $gameTopups ?? collect();
    $monthlyTransactions = $monthlyTransactions ?? 0;
    $monthlySpending = $monthlySpending ?? 0;
    $pendingCoinPurchaseCount = $pendingCoinPurchaseCount ?? 0;
    $pendingGameTopupCount = $pendingGameTopupCount ?? 0;

    $activePage = session('user_active_tab', request()->query('tab', 'dashboard'));
    if ($errors->any() || old('action')) {
        $activePage = 'profile';
    }

    $recentActivities = collect($coinPurchases)
        ->map(fn ($purchase) => [
            'timestamp' => $purchase->created_at,
            'date' => optional($purchase->created_at)->format('d M Y'),
            'type' => 'Top Up Koin',
            'detail' => number_format($purchase->coin_amount) . ' koin',
            'status' => $purchase->status,
            'total' => $purchase->price_idr,
            'rejection_reason' => $purchase->rejection_reason,
        ])
        ->merge(
            $gameTopups->map(function ($topup) {
                $gameName = $topup->game->nama_game ?? '-';
                $packageAmount = $topup->package ? number_format($topup->package->amount) : null;
                $currencyName = $topup->currency->currency_name ?? null;
                $packageLabel = $packageAmount ? trim($packageAmount . ' ' . ($currencyName ?? '')) : null;
                $detailParts = array_filter([$gameName, $packageLabel]);

                return [
                    'timestamp' => $topup->created_at,
                    'date' => optional($topup->created_at)->format('d M Y'),
                    'type' => 'Top Up Game',
                    'detail' => $detailParts ? implode(' · ', $detailParts) : '-',
                    'status' => $topup->status,
                    'total' => $topup->price_idr,
                    'rejection_reason' => $topup->rejection_reason,
                ];
            })
        )
        ->sortByDesc('timestamp')
        ->take(5);

    $statusBadges = [
        'pending' => 'badge-warning',
        'waiting_verification' => 'badge-info',
        'approved' => 'badge-success',
        'completed' => 'badge-success',
        'rejected' => 'badge-error',
        'failed' => 'badge-error',
    ];

    $coinPaymentMethods = config('coin.payment_methods', []);
    $premiumActive = $premiumActive ?? false;
    $premiumDiscount = $premiumDiscount ?? 0;
    $premiumPriceCoins = $premiumPriceCoins ?? 0;
    $premiumPriceIdr = $premiumPriceIdr ?? 0;
    $premiumNextDiscount = $premiumNextDiscount ?? null;
    $premiumMaxDiscount = $premiumMaxDiscount ?? 0;
    $premiumBaseDiscount = $premiumBaseDiscount ?? 0;
    $premiumRewardCoins = $premiumRewardCoins ?? 0;
    $premiumIncrement = $premiumIncrement ?? 0;
    $premiumTransactions = $premiumTransactions ?? collect();
    $premiumErrorBag = $errors->getBag('premium');
    $premiumExpiry = optional(optional($user)->premium)->tanggal_expired;
    $favoriteGames = $favoriteGames ?? collect();
    $coinSummary = $coinSummary ?? [
        'total_coin' => 0,
        'total_spent' => 0,
        'successful_orders' => 0,
        'last_purchase_at' => null,
    ];
    $lastGameTopup = $lastGameTopup ?? null;
@endphp

@section('sidebar-user')
<x-sidebar-user :active-page="$activePage">
  <section data-page="dashboard" class="{{ $activePage === 'dashboard' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h1 class="text-2xl font-semibold text-(--textsub1)">Dashboard</h1>
      <p class="text-sm text-(--textsub1)">Lihat ringkasan aktivitas akun dan promo terbaru.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-5 stat-grid">
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Transaksi Bulan Ini</div>
        <div class="stat-value text-primary">{{ number_format($monthlyTransactions) }}</div>
        <div class="stat-desc text-(--textsub1)">Periode {{ now()->format('F Y') }}</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Total Pengeluaran</div>
        <div class="stat-value">Rp {{ number_format($monthlySpending, 0, ',', '.') }}</div>
        <div class="stat-desc text-(--textsub1)">Akumulasi transaksi bulan berjalan</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Saldo Koin</div>
        <div class="stat-value text-secondary">{{ number_format($user->coin_balance) }} Koin</div>
        <div class="stat-desc text-success">Setara Rp {{ number_format($user->coin_balance * 100, 0, ',', '.') }}</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Pending Top Up Koin</div>
        <div class="stat-value">{{ number_format($pendingCoinPurchaseCount) }}</div>
        <div class="stat-desc text-warning">Menunggu persetujuan admin</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Pending Top Up Game</div>
        <div class="stat-value">{{ number_format($pendingGameTopupCount) }}</div>
        <div class="stat-desc text-warning">Menunggu verifikasi pembayaran</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Status Premium</div>
        @if ($premiumActive)
          <div class="stat-value text-accent">{{ $premiumDiscount }}%</div>
          <div class="stat-desc text-(--textsub1)">Diskon aktif hingga {{ $premiumMaxDiscount }}%</div>
        @else
          <div class="stat-value text-warning">Belum Aktif</div>
          <div class="stat-desc text-(--textsub1)">
            <button type="button" class="btn rounded-2xl bg-(--p2) font-bold text-white" onclick="loadUserPage('upgrade')">Upgrade sekarang</button>
          </div>
        @endif
      </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3 dashboard-grid cols-3">
      <div class="card shadow-lg xl:col-span-2">
        <div class="card-body gap-5">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="card-title text-xl text-(--textsub1) ">Riwayat Singkat</h2>
            <div><a href="{{ route('user.dashboard', ['tab' => 'transactions']) }}" class="btn btn-ghost btn-sm w-full text-(--textsub1)">Lihat Semua</a></div>
          </div>
          <div class="overflow-x-auto table-responsive">
            <table class="table">
              <thead>
                <tr class="text-sm text-(--textsub1)">
                  <th>Tanggal</th>
                  <th>Jenis</th>
                  <th>Detail</th>
                  <th>Status</th>
                  <th>Total</th>
                </tr>
              </thead>
              <tbody>
                @forelse ($recentActivities as $activity)
                  @php
                    $statusKey = strtolower($activity['status'] ?? '');
                    $badgeClass = $statusBadges[$statusKey] ?? 'badge-ghost';
                  @endphp
                  <tr class="text-(--textsub1)">
                    <td>{{ $activity['date'] ?? '-' }}</td>
                    <td>{{ $activity['type'] ?? '-' }}</td>
                    <td>{{ $activity['detail'] ?? '-' }}</td>
                    <td>
                      <span class="badge {{ $badgeClass }} text-xs capitalize">{{ isset($activity['status']) ? ucwords(str_replace('_', ' ', $activity['status'])) : '-' }}</span>
                      @if (isset($activity['status'], $activity['rejection_reason']) && strtolower($activity['status']) === 'rejected' && $activity['rejection_reason'])
                        <p class="mt-1 text-xs text-error"><span class="font-semibold">Alasan:</span> {{ $activity['rejection_reason'] }}</p>
                      @endif
                    </td>
                    <td>Rp {{ number_format($activity['total'] ?? 0, 0, ',', '.') }}</td>
                  </tr>
                @empty
                  <tr>
                    <td colspan="5" class="text-center text-sm text-(--textsub1)">Belum ada aktivitas transaksi.</td>
                  </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="card shadow-lg">
        <div class="card-body gap-4">
          <h2 class="card-title text-xl text-(--textsub1)">Ringkasan Aktivitas</h2>
          <div class="space-y-4">
            <div class="grid gap-4">
              <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-1">
                <p class="text-xs text-(--textsub1)">Total Koin Berhasil Dibeli</p>
                <p class="text-2xl font-semibold text-(--p2)">{{ number_format($coinSummary['total_coin'] ?? 0) }} koin</p>
                <p class="text-xs text-(--textsub1)">Dari {{ number_format($coinSummary['successful_orders'] ?? 0) }} transaksi koin</p>
              </div>
              <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-1">
                <p class="text-xs text-(--textsub1)">Nilai Pembelian Koin</p>
                <p class="text-2xl font-semibold text-(--p2)">Rp {{ number_format($coinSummary['total_spent'] ?? 0, 0, ',', '.') }}</p>
                <p class="text-xs text-(--textsub1)">Terakhir: {{ optional(data_get($coinSummary, 'last_purchase_at'))->format('d M Y H:i') ?? '-' }}</p>
              </div>
              @if ($lastGameTopup)
                @php
                  $lastTopupDetail = [];
                  if ($lastGameTopup->package && $lastGameTopup->currency) {
                      $lastTopupDetail[] = number_format($lastGameTopup->package->amount) . ' ' . $lastGameTopup->currency->currency_name;
                  }
                  if ($lastGameTopup->price_idr) {
                      $lastTopupDetail[] = 'Rp ' . number_format($lastGameTopup->price_idr, 0, ',', '.');
                  }
                  $lastTopupStatusKey = strtolower($lastGameTopup->status ?? '');
                  $lastTopupBadge = $statusBadges[$lastTopupStatusKey] ?? 'badge-ghost';
                @endphp
                <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-2">
                  <p class="text-xs text-(--textsub1)">Top Up Game Terakhir</p>
                  <p class="text-lg font-bold text-(--p2)">{{ $lastGameTopup->game->nama_game ?? '-' }}</p>
                  <p class="text-xs text-(--textsub1)">{{ implode(' • ', $lastTopupDetail) ?: '-' }}</p>
                  <div class="flex items-center gap-2 text-xs">
                    <span class="badge {{ $lastTopupBadge }} capitalize">{{ $lastGameTopup->status ? ucwords(str_replace('_', ' ', $lastGameTopup->status)) : '-' }}</span>
                    <span class="text-(--textsub1)">{{ optional($lastGameTopup->created_at)->format('d M Y H:i') ?? '-' }}</span>
                  </div>
                </div>
              @endif
            </div>

            <div class="space-y-3">
              <h3 class="text-sm font-semibold text-(--textsub1)">Game Paling Sering Dibeli</h3>
              <div class="space-y-3">
                @forelse ($favoriteGames as $index => $game)
                  <div class="flex items-start justify-between gap-3 rounded-box border border-base-200 p-3">
                    <div>
                      <p class="font-bold text-(--p2)">{{ $game->name }}</p>
                      <p class="text-xs text-(--textsub1)">{{ number_format($game->total_orders) }} pesanan · Rp {{ number_format($game->total_spent ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <span class="badge badge-primary badge-outline">#{{ $index + 1 }}</span>
                  </div>
                @empty
                  <p class="text-sm text-(--textsub1)">Belum ada data pembelian game.</p>
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section data-page="profile" class="{{ $activePage === 'profile' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Profil</h2>
      <p class="text-sm text-(--textsub1)">Perbarui informasi pribadi, avatar, dan latar.</p>
    </div>

    @if (session('status_account'))
      <div class="alert alert-success shadow">
        <span>{{ session('status_account') }}</span>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-error shadow">
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    @php
      $profilePhotoUrl = $user->foto_profil
        ? asset('storage/' . $user->foto_profil)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->username) . '&background=1E3A8A&color=FFFFFF';
      $backgroundPhotoUrl = $user->background_profil
        ? asset('storage/' . $user->background_profil)
        : null;
      $photoSectionOpen = old('action') === 'photos'
        || $errors->has('foto_profil')
        || $errors->has('background_profil');
    @endphp

    <div class="grid gap-6 xl:grid-cols-3 dashboard-grid cols-3">
      <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data" class="card shadow-lg xl:col-span-1">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" value="photos" />

        <div class="card-body items-center gap-4">
          @if (session('status_photos'))
            <div class="alert alert-success shadow w-full">
              <span>{{ session('status_photos') }}</span>
            </div>
          @endif
          <div class="w-full overflow-hidden rounded-2xl border border-base-200 bg-base-200" style="min-height: 200px; {{ $backgroundPhotoUrl ? 'background-image: url(' . $backgroundPhotoUrl . '); background-size: cover; background-position: center;' : '' }}">
            @unless ($backgroundPhotoUrl)
              <div class="flex h-full items-center justify-center text-sm text-(--textsub1)">Belum ada latar belakang</div>
            @endunless
          </div>
          <div class="avatar -mt-16">
            <div class="w-32 rounded-full ring ring-primary ring-offset-4 ring-offset-base-100">
              <img src="{{ $profilePhotoUrl }}" alt="Avatar pengguna" />
            </div>
          </div>
          <div class="space-y-2 text-center">
            <p class="text-lg font-semibold text-(--textsub1)">{{ $user->nama_lengkap ?? $user->username }}</p>
            @if ($premiumActive)
              <span class="badge badge-warning badge-outline">Premium</span>
            @endif
          </div>
          <div class="w-full space-y-3">
            <button type="button" class="btn btn-outline btn-sm w-full text-(--textsub1)" onclick="togglePhotoSettings()">Atur Foto Profil</button>
            <div id="photo-settings" class="space-y-3 {{ $photoSectionOpen ? '' : 'hidden' }}">
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Foto Profil</span>
                </label>
                <input type="file" name="foto_profil" class="file-input file-input-bordered w-full" accept="image/*" />
                @error('foto_profil')
                  <span class="text-xs text-error">{{ $message }}</span>
                @enderror
              </div>
              @if ($user->foto_profil)
                <label class="label cursor-pointer justify-start gap-3">
                  <input type="checkbox" name="remove_foto_profil" value="1" class="checkbox checkbox-sm" @checked(old('remove_foto_profil')) />
                  <span class="label-text text-sm">Hapus foto profil saat ini</span>
                </label>
              @endif
              <div class="form-control">
                <label class="label">
                  <span class="label-text">Foto Latar Belakang</span>
                </label>
                <input type="file" name="background_profil" class="file-input file-input-bordered w-full" accept="image/*" />
                @error('background_profil')
                  <span class="text-xs text-error">{{ $message }}</span>
                @enderror
              </div>
              @if ($user->background_profil)
                <label class="label cursor-pointer justify-start gap-3">
                  <input type="checkbox" name="remove_background_profil" value="1" class="checkbox checkbox-sm" @checked(old('remove_background_profil')) />
                  <span class="label-text text-sm">Hapus latar belakang</span>
                </label>
              @endif
              <div class="card-actions pt-2">
                <button type="submit" class="btn btn-primary btn-sm w-full">Simpan Foto</button>
              </div>
            </div>
          </div>
        </div>
      </form>

      <form method="POST" action="{{ route('user.profile.update') }}" class="card shadow-lg xl:col-span-2">
        @csrf
        @method('PUT')
        <input type="hidden" name="action" value="account" />

        <div class="card-body space-y-4">
          <h3 class="card-title text-lg text-(--textsub1)">Informasi Akun</h3>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="form-control md:col-span-2">
              <label class="label text-(--textsub1)">
                <span class="label-text">Nama Lengkap</span>
              </label>
              <input type="text" name="nama_lengkap" class="input input-bordered text-(--textsub1)" placeholder="Masukkan nama" value="{{ old('nama_lengkap', $user->nama_lengkap) }}" />
              @error('nama_lengkap')
                <span class="text-xs text-error">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-control text-(--textsub1)">
              <label class="label">
                <span class="label-text">Username</span>
              </label>
              <input type="text" name="username" class="input input-bordered" placeholder="Masukkan username" value="{{ old('username', $user->username) }}" required />
              @error('username')
                <span class="text-xs text-error">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-control text-(--textsub1)">
              <label class="label">
                <span class="label-text">Email</span>
              </label>
              <input type="email" name="email" class="input input-bordered" value="{{ old('email', $user->email) }}" required />
              @error('email')
                <span class="text-xs text-error">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-control text-(--textsub1)">
              <label class="label">
                <span class="label-text">Nomor Telepon</span>
              </label>
              <input type="tel" name="nomor_telepon" class="input input-bordered" placeholder="08xxxxxxxxxx" value="{{ old('nomor_telepon', $user->nomor_telepon) }}" />
              @error('nomor_telepon')
                <span class="text-xs text-error">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-control text-(--textsub1)">
              <label class="label">
                <span class="label-text">Tanggal Lahir</span>
              </label>
              <input type="date" name="tanggal_lahir" class="input input-bordered" value="{{ old('tanggal_lahir', optional($user->tanggal_lahir)->format('Y-m-d')) }}" />
              @error('tanggal_lahir')
                <span class="text-xs text-error">{{ $message }}</span>
              @enderror
            </div>
            <div class="form-control md:col-span-2 text-(--textsub1)">
              <label class="label">
                <span class="label-text">Bio</span>
              </label>
              <textarea name="bio" class="textarea textarea-bordered" rows="3" placeholder="Ceritakan tentang kamu">{{ old('bio', $user->bio) }}</textarea>
              @error('bio')
                <span class="text-xs text-error">{{ $message }}</span>
              @enderror
            </div>
          </div>
          <div class="card-actions justify-end">
            <button type="reset" class="btn btn-ghost text-(--textsub1)">Reset</button>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </div>
      </form>
    </div>

    <form method="POST" action="{{ route('user.profile.update') }}" class="card shadow-lg mt-6 max-w-3xl">
      @csrf
      @method('PUT')
      <input type="hidden" name="action" value="security" />
      <div class="card-body space-y-4">
        <h3 class="card-title text-lg text-(--textsub1)">Keamanan</h3>
        @if (session('status_security'))
          <div class="alert alert-success shadow">
            <span>{{ session('status_security') }}</span>
          </div>
        @endif
        <div class="grid gap-4 md:grid-cols-2">
          <div class="form-control md:col-span-2">
            <label class="label">
              <span class="label-text text-(--textsub1)">Password Baru</span>
            </label>
            <input type="password" name="password" class="input input-bordered text-(--textsub1)" placeholder="Minimal 8 karakter" autocomplete="new-password" />
            @error('password')
              <span class="text-xs text-error">{{ $message }}</span>
            @enderror
          </div>
          <div class="form-control md:col-span-2 text-(--textsub1)">
            <label class="label">
              <span class="label-text">Konfirmasi Password</span>
            </label>
            <input type="password" name="password_confirmation" class="input input-bordered" placeholder="Ulangi password" autocomplete="new-password" />
          </div>
        </div>
        <div class="card-actions justify-end">
          <button type="submit" class="btn btn-primary">Simpan Password</button>
        </div>
      </div>
    </form>

    @once
      <script>
        window.togglePhotoSettings = function () {
          const section = document.getElementById('photo-settings');
          if (!section) {
            return;
          }
          section.classList.toggle('hidden');
        };
      </script>
    @endonce
  </section>

  <section data-page="transactions" class="{{ $activePage === 'transactions' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Transaksi</h2>
      <p class="text-sm text-(--textsub1)">Cek semua riwayat top up game, coin, dan layanan premium.</p>
    </div>

    <div class="tabs tabs-lifted">
      <input type="radio" name="transaction_tabs" role="tab" class="tab" aria-label="Top Up Game" checked />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-(--textsub1)">Top Up Game</h3>
        </div>
        <div class="overflow-x-auto table-responsive">
          <table class="table table-zebra">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Tanggal</th>
                <th>Game</th>
                <th>Detail</th>
                <th>Status</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($gameTopups as $topup)
                @php
                  $statusKey = strtolower($topup->status ?? '');
                  $badgeClass = $statusBadges[$statusKey] ?? 'badge-ghost';
                  $gameName = $topup->game->nama_game ?? '-';
                  $packageAmount = number_format($topup->package->amount ?? 0);
                  $currencyName = $topup->currency->currency_name ?? '-';
                  $detail = trim($packageAmount . ' ' . $currencyName);
                @endphp
                <tr class="text-(--textsub1)">
                  <td>{{ optional($topup->created_at)->format('d M Y H:i') }}</td>
                  <td>{{ $gameName }}</td>
                  <td>{{ $detail }}</td>
                  <td>
                    <span class="badge {{ $badgeClass }} text-xs capitalize">{{ $topup->status ? ucwords(str_replace('_', ' ', $topup->status)) : '-' }}</span>
                    @if ($topup->status === 'rejected' && $topup->rejection_reason)
                      <p class="mt-1 text-xs text-error"><span class="font-semibold">Alasan:</span> {{ $topup->rejection_reason }}</p>
                    @endif
                  </td>
                  <td>Rp {{ number_format($topup->price_idr ?? 0, 0, ',', '.') }}</td>
                  <td>
                    @if (in_array($topup->status, ['approved', 'completed'], true) && $topup->transaksi)
                      <button type="button" class="btn btn-ghost btn-sm gap-2 text-primary" data-invoice-url="{{ route('invoices.show', $topup->transaksi) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M7 21h10a2 2 0 0 0 2-2V7l-4-4H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z"></path>
                          <path d="M7 3v4h4"></path>
                          <path d="M9 13h6"></path>
                          <path d="M9 17h6"></path>
                        </svg>
                        <span class="text-xs font-semibold uppercase tracking-wide">Lihat Struk</span>
                      </button>
                    @else
                      <span class="text-xs text-(--textsub1)">-</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-sm text-(--textsub1)">Belum ada transaksi top up game.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <input type="radio" name="transaction_tabs" role="tab" class="tab" aria-label="Top Up Coin" />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <h3 class="text-lg font-semibold">Top Up Koin</h3>
        <div class="overflow-x-auto table-responsive">
          <table class="table">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Tanggal</th>
                <th>Koin</th>
                <th>Metode Pembayaran</th>
                <th>Status</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($coinPurchases as $purchase)
                @php
                  $statusKey = strtolower($purchase->status ?? '');
                  $badgeClass = $statusBadges[$statusKey] ?? 'badge-ghost';
                  $paymentLabel = $coinPaymentMethods[$purchase->payment_method]['label'] ?? strtoupper($purchase->payment_method);
                @endphp
                <tr class="text-(--textsub1)">
                  <td>{{ optional($purchase->created_at)->format('d M Y H:i') }}</td>
                  <td>{{ number_format($purchase->coin_amount) }} koin</td>
                  <td>{{ $paymentLabel }}</td>
                  <td>
                    <span class="badge {{ $badgeClass }} text-xs capitalize">{{ ucwords(str_replace('_', ' ', $purchase->status)) }}</span>
                    @if ($purchase->status === 'rejected' && $purchase->rejection_reason)
                      <p class="mt-1 text-xs text-error"><span class="font-semibold">Alasan:</span> {{ $purchase->rejection_reason }}</p>
                    @endif
                  </td>
                  <td>Rp {{ number_format($purchase->price_idr, 0, ',', '.') }}</td>
                  <td>
                    @if (in_array($purchase->status, ['approved', 'completed'], true) && $purchase->transaksi)
                      <button type="button" class="btn btn-ghost btn-sm gap-2 text-primary" data-invoice-url="{{ route('invoices.show', $purchase->transaksi) }}">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M7 21h10a2 2 0 0 0 2-2V7l-4-4H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2Z"></path>
                          <path d="M7 3v4h4"></path>
                          <path d="M9 13h6"></path>
                          <path d="M9 17h6"></path>
                        </svg>
                        <span class="text-xs font-semibold uppercase tracking-wide">Lihat Struk</span>
                      </button>
                    @else
                      <span class="text-xs text-(--textsub1)">-</span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="6" class="text-center text-sm text-(--textsub1)">Belum ada riwayat top up koin.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      <input type="radio" name="transaction_tabs" role="tab" class="tab" aria-label="Layanan Premium" />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <h3 class="text-lg font-semibold">Layanan Premium</h3>
        <div class="overflow-x-auto table-responsive">
          <table class="table">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Tanggal</th>
                <th>Layanan</th>
                <th>Durasi</th>
                <th>Status</th>
                <th>Total</th>
                <th>Koin</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($premiumTransactions as $transaction)
                @php
                  $transactionStatus = strtolower($transaction->status ?? 'completed');
                  $badgeClass = $statusBadges[$transactionStatus] ?? 'badge-ghost';
                  $coinRateValue = (int) config('coin.coin_to_idr_rate', 100);
                  $coinsUsed = $coinRateValue > 0 ? (int) ceil(((float) $transaction->harga) / $coinRateValue) : 0;
                @endphp
                <tr class="text-(--textsub1)">
                  <td>{{ optional($transaction->tanggal_transaksi)->format('d M Y H:i') }}</td>
                  <td>Keanggotaan Premium</td>
                  <td>{{ config('premium.duration_days', 365) }} Hari</td>
                  <td>
                    <span class="badge {{ $badgeClass }} text-xs capitalize">{{ ucwords(str_replace('_', ' ', $transactionStatus)) }}</span>
                  </td>
                  <td>Rp {{ number_format($transaction->harga ?? 0, 0, ',', '.') }}</td>
                  <td>{{ number_format($coinsUsed) }} koin</td>
                </tr>
              @empty
                <tr class="text-(--textsub1)">
                  <td colspan="6" class="text-center text-sm text-(--textsub1)">Belum ada riwayat pembelian premium.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <section data-page="upgrade" class="{{ $activePage === 'upgrade' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Upgrade Akun</h2>
      <p class="text-sm text-(--textsub1)">Nikmati fitur eksklusif dengan menjadi member Premium.</p>
    </div>

    @if ($premiumErrorBag->any())
      <div class="alert alert-error shadow">
        <span>{{ $premiumErrorBag->first() }}</span>
      </div>
    @endif
    @if (session('premium_success'))
      <div class="alert alert-success shadow">
        <span>{{ session('premium_success') }}</span>
      </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
      <div class="card shadow-2xl xl:col-span-2">
        <div class="card-body gap-6 ">
          <div class="flex flex-col gap-2">
            <span class="badge badge-primary badge-outline w-fit">Premium Plan</span>
            <h3 class="text-3xl font-bold">
              <span class="font-semibold">{{ number_format($premiumPriceCoins) }} koin</span>
              <span class="text-base font-medium text-(--textsub1)"> / {{ config('premium.duration_days', 30) }} hari</span>
            </h3>
            <p class="text-(--textsub1)">
              Diskon eksklusif mulai {{ $premiumBaseDiscount }}% dan meningkat
              @if ($premiumIncrement > 0)
                {{ $premiumIncrement }}% tiap transaksi
              @endif
              hingga maksimal {{ $premiumMaxDiscount }}% untuk setiap top up game Anda.
            </p>
            <p class="text-sm text-(--textsub1)">
              Pembelian hanya dengan <span class="font-semibold">{{ number_format($premiumPriceCoins) }} koin</span> Harga Sekitar  <span class="font-semibold">Rp {{ number_format($premiumPriceIdr, 0, ',', '.') }} </span>. Saldo koin Anda saat ini: {{ number_format($user->coin_balance) }} koin.
            </p>
            @if ($premiumActive)
              <p class="text-sm text-success">Status aktif hingga {{ optional($premiumExpiry)->format('d M Y') ?? 'tidak ditentukan' }} dengan diskon {{ $premiumDiscount }}%.</p>
              @if ($premiumNextDiscount && $premiumNextDiscount > $premiumDiscount)
                <p class="text-sm text-(--textsub1)">Selesaikan 1 transaksi lagi untuk naik menjadi {{ $premiumNextDiscount }}%.</p>
              @endif
            @else
              <p class="text-sm text-warning">Aktifkan sekarang dan klaim bonus {{ $premiumRewardCoins }} koin setiap transaksi game.</p>
            @endif
          </div>
          <div class="grid gap-4 md:grid-cols-2">
            <div class="flex items-start gap-3">
              <span class="badge badge-primary badge-lg badge-outline">1</span>
              <div>
                <p class="font-semibold">Diskon Dinamis</p>
                <p class="text-sm text-(--textsub1)">Mulai {{ $premiumBaseDiscount }}% dan naik otomatis hingga {{ $premiumMaxDiscount }}%.</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <span class="badge badge-primary badge-lg badge-outline">2</span>
              <div>
                <p class="font-semibold">Reward Koin</p>
                <p class="text-sm text-(--textsub1)">Setiap top up game memberi tambahan {{ $premiumRewardCoins }} koin.</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <span class="badge badge-primary badge-lg badge-outline">3</span>
              <div>
                <p class="font-semibold">Badge Eksklusif</p>
                <p class="text-sm text-(--textsub1)">Status premium terlihat di profil dan dashboard.</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <span class="badge badge-primary badge-lg badge-outline">4</span>
              <div>
                <p class="font-semibold">Saldo Koin Terlindungi</p>
                <p class="text-sm text-(--textsub1)">Proses pembayaran aman dan cepat dengan saldo koin.</p>
              </div>
            </div>
          </div>
          <div class="card-actions justify-end">
            <button type="button" class="btn btn-primary btn-lg" onclick="openPremiumConfirmModal()">
              {{ $premiumActive ? 'Perpanjang Premium' : 'Beli Premium' }}
            </button>
            <button type="button" class="btn btn-ghost" onclick="loadUserPage('dashboard')">Kembali ke Dashboard</button>
          </div>
        </div>
      </div>

      <div class="card bg-base-200 shadow-lg">
        <div class="card-body gap-4">
          <h3 class="card-title">Ringkasan Manfaat</h3>
          <ul class="space-y-3 text-sm text-(--textsub1)">
            <li class="flex items-center gap-2">
              <span class="badge badge-success badge-xs"></span>
              Diskon premium otomatis diterapkan di setiap pesanan game.
            </li>
            <li class="flex items-center gap-2">
              <span class="badge badge-success badge-xs"></span>
              Bonus {{ $premiumRewardCoins }} koin setelah transaksi disetujui.
            </li>
            <li class="flex items-center gap-2">
              <span class="badge badge-success badge-xs"></span>
              Tidak ada biaya tambahan, seluruh pembayaran dari saldo koin Anda.
            </li>
          </ul>
          <div class="alert alert-warning text-sm">
            <span>Pastikan saldo koin mencukupi sebelum konfirmasi pembelian premium.</span>
          </div>
        </div>
      </div>
    </div>

    <form id="premiumPurchaseForm" method="POST" action="{{ route('user.premium.store') }}" class="hidden">
      @csrf
    </form>

    <dialog id="premiumConfirmModal" class="modal">
      <div class="modal-box space-y-4">
        <h3 class="text-lg font-semibold text-(--text1)">Konfirmasi Pembelian Premium</h3>
        <p class="text-sm text-(--textsub1)">Apakah ingin melakukan pembelian premium? Saldo koin Anda {{ number_format($user->coin_balance) }} koin dan biaya yang diperlukan {{ number_format($premiumPriceCoins) }} koin.</p>
        <div class="modal-action">
          <form method="dialog" class="flex gap-2">
            <button class="btn btn-ghost" type="submit">Tidak</button>
            <button type="button" class="btn btn-primary" data-premium-confirm="yes">Ya</button>
          </form>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button>Batal</button>
      </form>
    </dialog>

    <dialog id="premiumSuccessModal" class="modal" data-show="{{ session('premium_success') ? 'true' : 'false' }}">
      <div class="modal-box space-y-4">
        <h3 class="text-lg font-semibold text-success">Premium Aktif!</h3>
        <p class="text-sm text-(--textsub1)">{{ session('premium_success') }}</p>
        @if (session('premium_coins_used'))
          <p class="text-sm text-(--textsub1)">Koin terpakai: {{ number_format(session('premium_coins_used')) }} koin.</p>
        @endif
        <p class="text-sm text-(--textsub1)">Diskon Anda saat ini {{ $premiumDiscount }}%.</p>
        <div class="modal-action">
          <form method="dialog">
            <button class="btn btn-primary" type="submit">Tutup</button>
          </form>
        </div>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button>Tutup</button>
      </form>
    </dialog>
  </section>

  @once
    <script>
      window.openPremiumConfirmModal = function () {
        const modal = document.getElementById('premiumConfirmModal');
        if (modal) {
          modal.showModal();
        }
      };

      document.addEventListener('DOMContentLoaded', () => {
        const confirmModal = document.getElementById('premiumConfirmModal');
        const successModal = document.getElementById('premiumSuccessModal');
        const confirmButton = confirmModal ? confirmModal.querySelector('[data-premium-confirm="yes"]') : null;

        if (confirmButton) {
          confirmButton.addEventListener('click', () => {
            const form = document.getElementById('premiumPurchaseForm');
            if (form) {
              form.submit();
            }
          });
        }

        if (successModal && successModal.dataset.show === 'true') {
          successModal.showModal();
        }
      });
    </script>
  @endonce
</x-sidebar-user>
@endsection

</x-layouts.app>

@include('components.invoice-modal')