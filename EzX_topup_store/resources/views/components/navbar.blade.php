<div class="navbar bg-(--p)/80 text-(--text1) shadow-sm sticky top-0 z-50 backdrop-blur-md  ">
  <div class="navbar-start">
    @php
      $showPublicLinks = true;
      $normalizedRoleKey = null;
      if (auth()->check()) {
        $normalizedRoleKey = str_replace([' ', '-', '_'], '', strtolower(auth()->user()->role));
        if (in_array($normalizedRoleKey, ['admin', 'superadmin'], true)) {
          $showPublicLinks = false;
        }
      }
    @endphp
    <div class="dropdown">
      <div tabindex="0" role="button" class="btn btn-ghost lg:hidden">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
        </svg>
      </div>
      <ul tabindex="-1" class="menu menu-sm dropdown-content text-(--textsub1) bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
        @if ($showPublicLinks)
          <li><a href="/">Beranda</a></li>
          <li><a href="{{ route('orders.track') }}">Pesanan</a></li>
        @endif
      </ul>
    </div>
    <a href="/" class="btn btn-ghost p-0">
      <div class="flex items-center gap-3 px-4 py-2 rounded-xl border border-white/20 bg-black/10 backdrop-blur-md shadow">
        <img src="{{ asset('images/EzX_logo.gif') }}" alt="EzX Logo" class="h-8 sm:h-10">
      </div>
    </a>
    <span class="hidden sm:inline-block text-xl font-bold px-5">EzX Store Official</span>


  </div>
  <div class="navbar-center hidden lg:flex">
  {{--  --}}
  </div>
  <div class="navbar-end gap-4">
    @if ($showPublicLinks)
      <div class="hidden lg:block">
        <ul class="menu menu-horizontal px-1">
          <li><a href="/">Beranda</a></li>
          <li><a href="{{ route('orders.track') }}">Pesanan</a></li>
        </ul>
      </div>
    @endif

    @auth
      @php
        $user = auth()->user();
        $coinBalance = optional($user->koin)->jumlah_koin ?? 0;
        $notificationLimit = 5;
        $notifications = $user->notifications()->latest()->take($notificationLimit)->get();
        $unreadCount = $user->unreadNotifications()->count();
      @endphp
      @if ($user->role === 'user')
        <button type="button"
          class="btn btn-warning rounded-3xl btn-sm gap-3 border-none text-black shadow-md"
          onclick="document.getElementById('coinPurchaseModal').showModal()">
          <span class="inline-flex h-6  w-6  items-center justify-center rounded-full bg-yellow-200">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 21 21" fill="#000000"><g fill="none" fill-rule="evenodd" stroke="#000000" stroke-linecap="round" stroke-linejoin="round"><path d="M18.5 11.5v3c0 1.3-3.134 3-7 3s-7-1.7-7-3V12"/><path d="M4.794 12.259c.865 1.148 3.54 2.225 6.706 2.225c3.866 0 7-1.606 7-2.986c0-.775-.987-1.624-2.536-2.22"/><path d="M15.5 6.5v3c0 1.3-3.134 3-7 3s-7-1.7-7-3v-3"/><path d="M8.5 9.484c3.866 0 7-1.606 7-2.986c0-1.381-3.134-2.998-7-2.998s-7 1.617-7 2.998c0 1.38 3.134 2.986 7 2.986z"/></g></svg>
          </span>
          <span class="text-sm font-semibold">{{ number_format($coinBalance) }} Koin</span>
          <span class="btn btn-circle btn-xs border border-yellow-500 bg-transparent text-yellow-600">
            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 1024 1024"><path fill="#000000" d="M960 640H640v320q0 27-18.5 45.5T576 1024H448q-27 0-45.5-19T384 960V640H64q-27 0-45.5-19T0 576V448q0-27 18.5-45.5T64 384h320V64q0-27 18.5-45.5T448 0h128q27 0 45.5 18.5T640 64v320h320q27 0 45.5 18.5T1024 448v128q0 26-18.5 45T960 640z"/></svg>
          </span>
        </button>
      @endif
        {{-- tampilan notifikasi --}}
      <div class="dropdown dropdown-end">
        <button type="button" class="btn btn-ghost btn-circle" tabindex="0">
          <div class="indicator">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.657A2 2 0 0 1 13 19H11a2 2 0 0 1-1.857-1.343L8.1 16H5.5a1.5 1.5 0 0 1-1.415-2.01l1.48-4.439A6 6 0 0 1 11.322 5h1.356a6 6 0 0 1 5.757 4.551l1.48 4.439A1.5 1.5 0 0 1 18.5 16h-2.6l-.143 1.657Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 19a2 2 0 1 1-4 0" />
            </svg>
            @if ($unreadCount > 0)
              <span class="badge badge-secondary badge-sm indicator-item">{{ $unreadCount }}</span>
            @endif
          </div>
        </button>
        <div tabindex="0" class="card dropdown-content w-80 bg-base-100 shadow-xl border border-base-200 mt-3">
          <div class="card-body gap-4">
            <div class="flex items-center justify-between">
              <h3 class="text-lg text-(--textsub1) font-semibold">Notifikasi</h3>
              @if ($unreadCount > 0)
                <form method="POST" action="{{ route('user.notifications.read-all') }}">
                  @csrf
                  <button type="submit" class="btn bg-(--p2) text-white font-semibold btn-xs">Tandai terbaca</button>
                </form>
              @endif
            </div>
            <div class="space-y-3 max-h-80 overflow-y-auto">
              @forelse ($notifications as $notification)
                @php
                  $data = $notification->data;
                  $status = $data['status'] ?? 'info';
                  $statusBadge = [
                      'pending' => 'badge-warning',
                      'approved' => 'badge-success',
                      'rejected' => 'badge-error',
                  ][$status] ?? 'badge-ghost';
                @endphp
                <div class="rounded-xl border border-base-200 bg-black/10 p-3 space-y-1">
                  <div class="flex items-center justify-between">
                    <p class="text-sm text-(--textsub1) font-semibold">{{ $data['title'] ?? 'Notifikasi' }}</p>
                    <span class="badge badge-xs {{ $statusBadge }} text-xs capitalize">{{ $status }}</span>
                  </div>
                  <p class="text-xs text-(--textsub1)">{{ $data['message'] ?? '-' }}</p>
                  <p class="text-[11px] text-(--textsub1)/70">{{ optional($notification->created_at)->diffForHumans() }}</p>
                </div>
              @empty
                <p class="text-sm text-(--textsub1)">Belum ada notifikasi.</p>
              @endforelse
            </div>
            <a href="{{ route('user.dashboard', ['tab' => 'transactions']) }}" class="btn bg-(--p2) text-white font-semibold btn-sm w-full">Lihat Riwayat</a>
          </div>
        </div>
      </div>

      <div class="px-2">
        <label class="toggle text-base-content">
          <input id="theme-toggle" type="checkbox" class="theme-controller" />

          <svg aria-label="sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="4"></circle>
              <path d="M12 2v2"></path>
              <path d="M12 20v2"></path>
              <path d="m4.93 4.93 1.41 1.41"></path>
              <path d="m17.66 17.66 1.41 1.41"></path>
              <path d="M2 12h2"></path>
              <path d="M20 12h2"></path>
              <path d="m6.34 17.66-1.41 1.41"></path>
              <path d="m19.07 4.93-1.41 1.41"></path>
            </g>
          </svg>

          <svg aria-label="moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor">
              <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
            </g>
          </svg>

        </label>
      </div>

      @php
        $normalizedRole = strtolower($user->role);
        $roleKey = str_replace([' ', '-', '_'], '', $normalizedRole);
        $dashboardRoute = match ($normalizedRole) {
          'admin' => route('admin.dashboard'),
          'super_admin' => route('superadmin.dashboard'),
          default => route('user.dashboard'),
        };
        if ($roleKey === 'admin') {
          $avatar = asset('images/pp-admins.png');
        } elseif ($roleKey === 'superadmin') {
          $avatar = asset('images/pp-superadmins.png');
        } else {
          $photo = $user->foto_profil;
          $isAbsolute = $photo && (str_starts_with($photo, 'http://') || str_starts_with($photo, 'https://'));
          $avatar = $photo
            ? ($isAbsolute ? $photo : asset('storage/' . ltrim($photo, '/')))
            : 'https://ui-avatars.com/api/?name='.urlencode($user->username).'&background=1E3A8A&color=FFFFFF';
        }
      @endphp
      <div class="flex items-center gap-2">
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn rounded-3xl bg-(--p2) text-white font-semibold hover:bg-amber-50/20 " aria-label="Logout">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M10 17l5-5-5-5" />
              <path d="M15 12H3" />
              <path d="M19 21V3" />
            </svg>
            <span class="ml-1 text-xs font-semibold uppercase tracking-wide">Logout</span>
          </button>
        </form>

        <a href="{{ $dashboardRoute }}" class="btn btn-ghost btn-circle avatar" aria-label="Profil">
          <div class="w-10 rounded-full border border-base-content/20">
            <img src="{{ $avatar }}" alt="Avatar {{ $user->username }}" />
          </div>
        </a>
      </div>
    @else
      <div class="px-2">
        <label class="toggle text-base-content">
          <input id="theme-toggle" type="checkbox" class="theme-controller" />

          <svg aria-label="sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor">
              <circle cx="12" cy="12" r="4"></circle>
              <path d="M12 2v2"></path>
              <path d="M12 20v2"></path>
              <path d="m4.93 4.93 1.41 1.41"></path>
              <path d="m17.66 17.66 1.41 1.41"></path>
              <path d="M2 12h2"></path>
              <path d="M20 12h2"></path>
              <path d="m6.34 17.66-1.41 1.41"></path>
              <path d="m19.07 4.93-1.41 1.41"></path>
            </g>
          </svg>

          <svg aria-label="moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <g stroke-linejoin="round" stroke-linecap="round" stroke-width="2" fill="none" stroke="currentColor">
              <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
            </g>
          </svg>

        </label>
      </div>
      <a href="{{ route('login') }}" class="btn">Login</a>
    @endauth
  </div>
</div>

{{-- JIMKA LOGIN USER MENAMPILKAN SALDO KOIN NYA --}}
@auth
  @php
    $user = auth()->user();
  @endphp
  @if ($user->role === 'user')
    @php
      $coinPackagesConfig = config('coin.packages', []);
      $coinRate = (int) config('coin.coin_to_idr_rate', 100);
      $coinPaymentMethodsConfig = collect(config('coin.payment_methods', []));
      $qrisMethod = $coinPaymentMethodsConfig->firstWhere('type', 'qris');
      $bankMethods = $coinPaymentMethodsConfig->filter(fn ($method) => ($method['type'] ?? '') === 'bank_transfer');
      $ewalletMethods = $coinPaymentMethodsConfig->filter(fn ($method) => ($method['type'] ?? '') === 'ewallet');
    @endphp


    {{-- MODAL FORM COIN PURCHAS --}}
    <dialog id="coinPurchaseModal" class="modal">
      <div class="modal-box max-w-4xl space-y-6 text-left">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-2xl font-semibold text-(--textsub1)">Top Up Koin</h3>
            <p class="text-sm text-(--textsub1)">Pilih nominal koin dan metode pembayaran, kemudian lanjutkan untuk mendapatkan instruksi pembayaran.</p>
            <p class="mt-1 text-xs text-warning">10 koin = Rp 1.000</p>
          </div>
          <form method="dialog">
            <button class="btn btn-sm btn-circle">✕</button>
          </form>
        </div>

        @if ($errors->coinPurchase->any())
          <div class="alert alert-error shadow">
            <span>{{ $errors->coinPurchase->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('coins.purchases.store') }}" class="space-y-6" id="coinPurchaseForm">
          @csrf

          <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
            <div class="space-y-6">
              <div class="space-y-3">
                <h4 class="text-lg font-semibold text-(--textsub1)">Pilih Nominal</h4>
                <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                  @foreach ($coinPackagesConfig as $packageKey => $package)
                    @php
                      $price = (int) ($package['price'] ?? 0);
                      $coins = (int) round($price / max($coinRate, 1));
                    @endphp
                    <label class="group relative block cursor-pointer rounded-2xl border border-white/20 bg-black/10 p-4 backdrop-blur transition hover:border-primary/80 hover:bg-primary/10">
                      <input type="radio" name="package_key" value="{{ $packageKey }}" class="peer sr-only" data-price="{{ $price }}" data-coins="{{ $coins }}" @checked(old('package_key', array_key_first($coinPackagesConfig)) === $packageKey) />
                      <div class="flex flex-col gap-2">
                        {{-- <p class="text-lg font-semibold text-(--textsub1) peer-checked:text-primary">{{ $package['label'] ?? 'Paket' }}</p> --}}
                       <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 26 26">
                            <path fill="#d4b725" d="M18 .188c-4.315 0-7.813 1.929-7.813 4.312S13.686 8.813 18 8.813c4.315 0 7.813-1.93 7.813-4.313S22.314.187 18 .187zm7.813 5.593c-.002 2.383-3.498 4.313-7.813 4.313c-4.303 0-7.793-1.909-7.813-4.281V7.5c0 1.018.652 1.95 1.72 2.688c1.08.294 2.042.702 2.843 1.218c.993.252 2.085.406 3.25.406c4.315 0 7.813-1.929 7.813-4.312V5.781zm0 3c0 2.383-3.498 4.313-7.813 4.313c-.525 0-1.035-.039-1.531-.094a4.35 4.35 0 0 1 .781 1.781c.249.014.495.031.75.031c4.315 0 7.813-1.929 7.813-4.312V8.781zM8 11.187c-4.315 0-7.813 1.93-7.813 4.313S3.686 19.813 8 19.813c4.315 0 7.813-1.93 7.813-4.313S12.314 11.187 8 11.187zm17.813.594c-.002 2.383-3.498 4.313-7.813 4.313c-.251 0-.505-.018-.75-.032c-.011.075-.017.175-.031.25c.05.151.093.3.093.47v1c.227.011.455.03.688.03c4.315 0 7.813-1.929 7.813-4.312v-1.719zm0 3c-.002 2.383-3.498 4.313-7.813 4.313c-.251 0-.505-.018-.75-.032c-.011.075-.017.175-.031.25c.05.15.093.3.093.47v1c.227.011.455.03.688.03c4.315 0 7.813-1.929 7.813-4.312v-1.719zm-10 2c-.002 2.383-3.498 4.313-7.813 4.313c-4.303 0-7.793-1.909-7.813-4.282V18.5c0 2.383 3.497 4.313 7.813 4.313s7.813-1.93 7.813-4.313v-1.719zm0 3c-.002 2.383-3.498 4.313-7.813 4.313c-4.303 0-7.793-1.909-7.813-4.282V21.5c0 2.383 3.497 4.313 7.813 4.313s7.813-1.93 7.813-4.313v-1.719z"/>
                        </svg>

                        <p class="text-lg font-bold text-(--textsub1)">
                            {{ number_format($coins) }} koin
                        </p>
                    </div>

                        <p class="text-md font-semibold text-(--textsub1)/80">RP {{ number_format($price, 0, ',', '.') }}</p>
                      </div>
                      <span class="absolute right-4 top-4 hidden rounded-full border border-primary bg-primary/20 p-1 text-primary peer-checked:inline-flex">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </span>
                    </label>
                  @endforeach
                </div>
              </div>

              <div class="space-y-3">
                <h4 class="text-lg font-semibold text-(--textsub1)">Metode Pembayaran</h4>
                <div class="grid gap-4 lg:grid-cols-2">
                  @if ($qrisMethod)
                    @php $qrisKey = $coinPaymentMethodsConfig->search($qrisMethod, true); @endphp
                    <label class="group block cursor-pointer space-y-2 rounded-2xl border border-white/20 bg-black/10 p-4 backdrop-blur hover:border-primary/80 hover:bg-primary/10">
                      <input type="radio" name="payment_method" value="{{ $qrisKey }}" class="peer sr-only" data-method-label="{{ $qrisMethod['label'] ?? 'QRIS' }}" @checked(old('payment_method') ? old('payment_method') === $qrisKey : true) />
                      <p class="text-base font-semibold text-(--textsub1) peer-checked:text-primary">{{ $qrisMethod['label'] ?? 'QRIS' }}</p>
                      <p class="text-xs text-(--textsub1)">Scan QR secara instan dari semua bank & e-wallet.</p>
                    </label>
                  @endif

                  @foreach ($bankMethods as $key => $method)
                    <label class="group block cursor-pointer space-y-2 rounded-2xl border border-white/20 bg-black/10 p-4 backdrop-blur hover:border-primary/80 hover:bg-primary/10">
                      <input type="radio" name="payment_method" value="{{ $key }}" class="peer sr-only" data-method-label="{{ $method['label'] ?? strtoupper($key) }}" @checked(old('payment_method') ? old('payment_method') === $key : (!$qrisMethod && $loop->first)) />
                      <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-(--textsub1) peer-checked:text-primary">{{ $method['label'] ?? strtoupper($key) }}</p>
                        <p class="text-xs text-(--textsub1)">{{ $method['account_name'] ?? '-' }}</p>
                        <p class="text-xs font-mono text-(--textsub1)">{{ $method['account_number'] ?? '-' }}</p>
                      </div>
                    </label>
                  @endforeach

                  @foreach ($ewalletMethods as $key => $method)
                    <label class="group block cursor-pointer space-y-2 rounded-2xl border border-white/20 bg-black/10 p-4 backdrop-blur hover:border-primary/80 hover:bg-primary/10">
                      <input type="radio" name="payment_method" value="{{ $key }}" class="peer sr-only" data-method-label="{{ $method['label'] ?? strtoupper($key) }}" @checked(old('payment_method') ? old('payment_method') === $key : (!$qrisMethod && !$bankMethods->count() && $loop->first)) />
                      <div class="flex flex-col gap-1">
                        <p class="text-base font-semibold text-(--textsub1) peer-checked:text-primary">{{ $method['label'] ?? strtoupper($key) }}</p>
                        <p class="text-xs text-(--textsub1)">{{ $method['account_name'] ?? '-' }}</p>
                        <p class="text-xs font-mono text-(--textsub1)">{{ $method['account_number'] ?? '-' }}</p>
                      </div>
                    </label>
                  @endforeach
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div class="rounded-2xl border border-white/20 bg-black/10 p-5 backdrop-blur">
                <h4 class="text-lg font-semibold text-(--textsub1)">Ringkasan Pembayaran</h4>
                <dl class="mt-4 space-y-2 text-sm text-(--textsub1)">
                  <div class="flex items-center justify-between">
                    <dt>Nominal Koin</dt>
                    <dd id="coinSummaryCoins" class="font-semibold text-(--textsub1)">-</dd>
                  </div>
                  <div class="flex items-center justify-between">
                    <dt>Metode Pembayaran</dt>
                    <dd id="coinSummaryMethod" class="font-semibold text-(--textsub1)">-</dd>
                  </div>
                  <div class="flex items-center justify-between">
                    <dt>Total Pembayaran</dt>
                    <dd id="coinSummaryPrice" class="text-lg font-bold text-(--textsub1)">Rp 0</dd>
                  </div>
                </dl>
              </div>

              <button type="submit" class="btn btn-primary w-full">Lanjutkan Pembayaran</button>
            </div>
          </div>
        </form>
      </div>
      <form method="dialog" class="modal-backdrop">
        <button>Tutup</button>
      </form>
    </dialog>
  @endif
@endauth
{{-- END OF MODAL PEMBELIAN COIN --}}

<!-- Script toggle theme -->
<script>
  const toggle = document.getElementById('theme-toggle');
  const html = document.documentElement;

  // Cek preferensi awal
  if (localStorage.getItem('theme') === 'dark') {
    html.setAttribute('data-theme', 'dark');
    toggle.checked = true;
  }

  toggle.addEventListener('change', () => {
    if (toggle.checked) {
      html.setAttribute('data-theme', 'dark');
      localStorage.setItem('theme', 'dark');
    } else {
      html.setAttribute('data-theme', 'light');
      localStorage.setItem('theme', 'light');
    }
  });

  const coinModal = document.getElementById('coinPurchaseModal');
  if (coinModal) {
    const packageRadios = coinModal.querySelectorAll('input[name="package_key"]');
    const paymentRadios = coinModal.querySelectorAll('input[name="payment_method"]');
    const summaryCoins = coinModal.querySelector('#coinSummaryCoins');
    const summaryMethod = coinModal.querySelector('#coinSummaryMethod');
    const summaryPrice = coinModal.querySelector('#coinSummaryPrice');

    const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value || 0);

    const updateSummary = () => {
      const selectedPackage = coinModal.querySelector('input[name="package_key"]:checked');
      const selectedPayment = coinModal.querySelector('input[name="payment_method"]:checked');

      const price = selectedPackage ? Number(selectedPackage.dataset.price || 0) : 0;
      const coins = selectedPackage ? Number(selectedPackage.dataset.coins || 0) : 0;

      summaryCoins.textContent = coins ? `${formatNumber(coins)} koin` : '-';
      summaryPrice.textContent = `Rp ${formatNumber(price)}`;
      summaryMethod.textContent = selectedPayment ? (selectedPayment.dataset.methodLabel || selectedPayment.value) : '-';
    };

    packageRadios.forEach((radio) => radio.addEventListener('change', updateSummary));
    paymentRadios.forEach((radio) => radio.addEventListener('change', updateSummary));

    updateSummary();
  }

  @if ($errors->coinPurchase->any())
    document.addEventListener('DOMContentLoaded', () => {
      const modal = document.getElementById('coinPurchaseModal');
      if (modal && typeof modal.showModal === 'function') {
        modal.showModal();
      }
    });
  @endif
</script>
