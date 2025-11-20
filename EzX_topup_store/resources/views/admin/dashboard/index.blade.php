<x-layouts.app>

@php
  $activePage = session('admin_active_tab', 'dashboard');
  $activeManagementTab = session('admin_active_sub_tab', 'games');
  $topPurchasedGames = $topPurchasedGames ?? collect();
  $topSpenders = $topSpenders ?? collect();
@endphp

@section('sidebar-admin')
<x-sidebar-admin :active-page="$activePage" :active-management-tab="$activeManagementTab">
  <section data-page="dashboard" class="{{ $activePage === 'dashboard' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h1 class="text-2xl font-semibold text-(--textsub1)">Dashboard</h1>
      <p class="text-sm text-(--textsub1)">Ringkasan performa platform top up.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4 stat-grid">
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Total Game</div>
        <div class="stat-value text-primary">{{ number_format($stats['totalGames']) }}</div>
        <div class="stat-desc text-success">Game aktif di katalog</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Pendapatan</div>
        <div class="stat-value text-(--p2)">Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}</div>
        <div class="stat-desc text-success">Transaksi selesai</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Top Up Selesai</div>
        <div class="stat-value text-secondary">{{ number_format($stats['completedTopUps']) }}</div>
        <div class="stat-desc text-(--textsub1)">Dalam seluruh periode</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">User Aktif</div>
        <div class="stat-value text-(--p2)">{{ number_format($stats['activeUsers']) }}</div>
        <div class="stat-desc text-success">Status akun aktif</div>
      </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3 dashboard-grid cols-3">
      <div class="space-y-6 xl:col-span-2">
        <div class="card shadow-lg">
          <div class="card-body gap-6">
            <div class="flex items-center justify-between">
              <h2 class="card-title text-xl text-(--textsub1)">Top Game Berdasarkan Paket Currency</h2>
              <span class="badge badge-secondary badge-outline">{{ $topGames->sum('currencies_count') }} paket terdata</span>
            </div>
            <p class="text-sm text-(--textsub1)">Lihat game mana yang memiliki variasi paket terbanyak sebagai acuan prioritas promosi.</p>
            @php
                $maxCurrency = max($topGames->max('currencies_count') ?? 1, 1);
            @endphp
            <div class="space-y-4">
              @forelse ($topGames as $game)
                @php
                    $percentage = $maxCurrency ? round(($game->currencies_count / $maxCurrency) * 100, 2) : 0;
                @endphp
                <div>
                  <div class="flex justify-between text-sm text-(--textsub1)">
                    <span>{{ $game->nama_game }}</span>
                    <span>{{ $game->currencies_count }} paket</span>
                  </div>
                  <progress class="progress progress-primary" value="{{ $percentage }}" max="100"></progress>
                </div>
              @empty
                <p class="text-sm text-(--textsub1)">Belum ada data game atau currency.</p>
              @endforelse
            </div>
          </div>
        </div>

        <div class="card shadow-lg">
          <div class="card-body gap-6">
            <div class="flex items-center justify-between">
              <h2 class="card-title text-xl text-(--textsub1)">Top Game Berdasarkan Jumlah Pembelian</h2>
              <span class="badge badge-secondary badge-outline">{{ $topPurchasedGames->sum('total_orders') }} pesanan</span>
            </div>
            <p class="text-sm text-(--textsub1)">Monitor game dengan volume transaksi tertinggi untuk pengaturan stok dan promosi.</p>
            @php
                $maxPurchases = max($topPurchasedGames->max('total_orders') ?? 1, 1);
            @endphp
            <div class="space-y-4">
              @forelse ($topPurchasedGames as $game)
                @php
                    $purchasePercentage = $maxPurchases ? round(($game->total_orders / $maxPurchases) * 100, 2) : 0;
                @endphp
                <div>
                  <div class="flex justify-between text-sm text-(--textsub1)">
                    <span>{{ $game->name }}</span>
                    <span>{{ number_format($game->total_orders) }} pembelian</span>
                  </div>
                  <progress class="progress progress-secondary" value="{{ $purchasePercentage }}" max="100"></progress>
                  <p class="text-xs text-(--textsub1) mt-1">Total pendapatan: Rp {{ number_format($game->total_revenue ?? 0, 0, ',', '.') }}</p>
                </div>
              @empty
                <p class="text-sm text-(--textsub1)">Belum ada data transaksi game.</p>
              @endforelse
            </div>
          </div>
        </div>

        <div class="card shadow-lg">
          <div class="card-body gap-5">
            <div class="flex items-center justify-between">
              <h2 class="card-title text-xl text-(--textsub1)">Top User Berdasarkan Pengeluaran</h2>
              <span class="badge badge-secondary badge-outline">{{ $topSpenders->sum('total_orders') }} transaksi</span>
            </div>
            <p class="text-sm text-(--textsub1)">Identifikasi pelanggan bernilai tinggi untuk program loyalitas.</p>
            <div class="space-y-4">
              @forelse ($topSpenders as $spender)
                <div class="flex items-start justify-between gap-3 rounded-box border border-base-200 p-3">
                  <div>
                    <p class="font-bold text-(--p2)">{{ $spender->user->username ?? ('User #' . $spender->id_user) }}</p>
                    <p class="text-xs text-(--textsub1)">{{ $spender->user->email ?? '-' }}</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-bold text-(--p2)">Rp {{ number_format($spender->total_spent ?? 0, 0, ',', '.') }}</p>
                    <p class="text-xs text-(--textsub1)">{{ number_format($spender->total_orders ?? 0) }} transaksi</p>
                  </div>
                </div>
              @empty
                <p class="text-sm text-(--textsub1)">Belum ada data user dengan transaksi selesai.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card shadow-lg">
          <div class="card-body gap-4">
            <h2 class="card-title text-xl text-(--textsub1)">Currency Terbaru</h2>
            <div class="space-y-4">
              @forelse ($recentCurrencies as $currency)
                <div class="flex items-start gap-3">
                  <div class="avatar">
                      <div class="w-12 rounded-xl">
                      <img src="{{ $currency->gambar_currency_url ?? 'https://placehold.co/96x96?text=Currency' }}" alt="{{ $currency->currency_name }}" />
                    </div>
                  </div>
                  <div class="flex-1">
                    <p class="font-medium text-(--textsub1)">{{ $currency->currency_name }}</p>
                    <p class="text-xs text-(--textsub1)">Game: {{ $currency->game->nama_game ?? '-' }}</p>
                    <p class="text-xs text-(--textsub1)">Ditambahkan: {{ $currency->created_at?->diffForHumans() ?? '-' }}</p>
                  </div>
                  <span class="badge badge-accent badge-outline">Baru</span>
                </div>
              @empty
                <p class="text-sm text-(--textsub1)">Belum ada currency baru ditambahkan.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-lg">
      <div class="card-body gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
          <div>
            <h2 class="card-title text-xl text-(--textsub1)">Game Terbaru</h2>
            <p class="text-sm text-(--textsub1)">Pantau game yang baru saja ditambahkan oleh tim.</p>
          </div>
        </div>
        <div class="overflow-x-auto table-responsive">
          <table class="table">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Game</th>
                <th>Jumlah Currency</th>
                <th>Dibuat</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($games->take(5) as $game)
                <tr class="text-(--textsub1)">
                  <td>{{ $game->nama_game }}</td>
                  <td>{{ $game->currencies->count() }}</td>
                  <td>{{ $game->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-sm text-(--textsub1)">Belum ada data game.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </section>

  <section data-page="games" class="{{ $activePage === 'games' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Manajemen Game</h2>
      <p class="text-sm text-(--textsub1)">Kelola katalog game dan mata uang digital.</p>
    </div>

    @if (session('status'))
      <div class="alert alert-success shadow">
        <span>{{ session('status') }}</span>
      </div>
    @endif

    @if ($errors->any())
      <div class="alert alert-error shadow">
        <span>{{ $errors->first() }}</span>
      </div>
    @endif

    <div class="tabs tabs-lifted">
      <input type="radio" name="management_tabs" role="tab" class="tab" aria-label="Game" data-management-tab="games" @checked($activeManagementTab === 'games') />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-(--textsub1)">Daftar Game</h3>
          <button class="btn btn-primary btn-sm" onclick="document.getElementById('createGameModal').showModal()">+ Tambah Game</button>
        </div>

        {{-- modal tambah game --}}
        <dialog id="createGameModal" class="modal">
          <div class="modal-box space-y-4">
            <h4 class="text-lg font-semibold text-(--textsub1)">Tambah Game Baru</h4>
            <form method="POST" action="{{ route('admin.games.store') }}" enctype="multipart/form-data" class="space-y-3">
              @csrf
              <div class="form-control">
                <label class="label text-(--textsub1)"> <span class="label-text">Nama Game</span> </label>
                <input type="text" name="nama_game" class="input input-bordered text-(--textsub1)" required />
              </div>
              <div class="form-control">
                <label class="label text-(--textsub1)"> <span class="label-text">Deskripsi</span> </label>
                <textarea name="deskripsi" rows="3" class="textarea textarea-bordered text-(--textsub1)" placeholder="Tulis deskripsi singkat"></textarea>
              </div>
              <div class="form-control">
                <label class="label text-(--textsub1)"> <span class="label-text">Gambar</span> </label>
                <input type="file" name="gambar" class="file-input file-input-bordered text-(--textsub1)" accept="image/*" />
              </div>
              <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('createGameModal').close()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </div>
            </form>
          </div>
        </dialog>

        <div class="overflow-x-auto rounded-box border border-base-300 table-responsive">
          <table class="table table-zebra">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Game</th>
                <th>Deskripsi</th>
                <th>Currency</th>
                <th>Dibuat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($games as $game)
                <tr class="text-(--textsub1)">
                  <td class="flex items-center gap-3">
                    <div class="avatar">
                      <div class="w-12 rounded-xl">
                        <img src="{{ $game->gambar_url ?? 'https://placehold.co/120x120?text=Game' }}" alt="{{ $game->nama_game }}" />
                      </div>
                    </div>
                    <span class="font-medium">{{ $game->nama_game }}</span>
                  </td>
                  <td class="text-sm text-(--textsub1)">{{ \Illuminate\Support\Str::limit($game->deskripsi, 80) ?: '-' }}</td>
                  <td>{{ $game->currencies->count() }}</td>
                  <td>{{ $game->created_at?->format('d M Y H:i') ?? '-' }}</td>
                  <td>
                    <div class="flex flex-wrap gap-2">
                      <button type="button" class="btn btn-xs btn-outline" onclick="document.getElementById('editGameModal-{{ $game->id_game }}').showModal()">Edit</button>
                      <form method="POST" action="{{ route('admin.games.destroy', $game) }}" onsubmit="return confirm('Hapus game ini beserta currency-nya?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-error">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr class="text-(--textsub1)">
                  <td colspan="5" class="text-center text-sm text-(--textsub1)">Belum ada data game.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- modal edit game --}}
        @foreach ($games as $game)
          <dialog id="editGameModal-{{ $game->id_game }}" class="modal">
            <div class="modal-box space-y-4">
              <h4 class="text-lg font-semibold text-(--textsub1)">Edit Game: {{ $game->nama_game }}</h4>
              <form method="POST" action="{{ route('admin.games.update', $game) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                @method('PUT')
                <div class="form-control">
                  <label class="label text-(--textsub1)"> <span class="label-text">Nama Game</span> </label>
                  <input type="text" name="nama_game" class="input input-bordered text-(--textsub1)" value="{{ old('nama_game', $game->nama_game) }}" required />
                </div>
                <div class="form-control">
                  <label class="label text-(--textsub1)"> <span class="label-text">Deskripsi</span> </label>
                  <textarea name="deskripsi" rows="4" class="textarea textarea-bordered text-(--textsub1)" placeholder="Tulis deskripsi singkat">{{ old('deskripsi', $game->deskripsi) }}</textarea>
                </div>
                <div class="form-control space-y-2">
                  <label class="label text-(--textsub1)"> <span class="label-text">Gambar</span> </label>
                  <input type="file" name="gambar" class="file-input file-input-bordered text-(--textsub1)" accept="image/*" />
                  @if ($game->gambar_url)
                    <img src="{{ $game->gambar_url }}" alt="{{ $game->nama_game }}" class="rounded-lg w-full max-h-40 object-cover" />
                  @endif
                </div>
                <div class="modal-action">
                  <button type="button" class="btn" onclick="document.getElementById('editGameModal-{{ $game->id_game }}').close()">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
              </form>
            </div>
          </dialog>
        @endforeach
      </div>

      <input type="radio" name="management_tabs" role="tab" class="tab" aria-label="Game Currency" data-management-tab="currencies" @checked($activeManagementTab === 'currencies') />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <div class="space-y-6">
          @forelse ($games as $game)
            <div class="card border border-base-300 shadow-sm">
              <div class="card-body space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-3">
                  <div class="flex items-center gap-3">
                    <div class="avatar">
                      <div class="w-12 rounded-xl">
                        <img src="{{ $game->gambar_url ?? 'https://placehold.co/120x120?text=Game' }}" alt="{{ $game->nama_game }}" />
                      </div>
                    </div>
                    <div>
                      <p class="font-semibold text-(--textsub1)">{{ $game->nama_game }}</p>
                      <p class="text-xs text-(--textsub1)">{{ $game->currencies->count() }} currency terdaftar</p>
                    </div>
                  </div>
                  <button class="btn btn-sm btn-primary" onclick="document.getElementById('addCurrencyModal-{{ $game->id_game }}').showModal()">Tambah Currency</button>
                </div>

                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3 dashboard-grid cols-3">
                  @forelse ($game->currencies as $currency)
                    <div class="card bg-base-100 shadow">
                      <div class="card-body gap-3">
                        <div class="flex items-center gap-3">
                          <div class="avatar">
                            <div class="w-14 rounded-xl">
                              <img src="{{ $currency->gambar_currency_url ?? 'https://placehold.co/120x120?text=Currency' }}" alt="{{ $currency->currency_name }}" />
                            </div>
                          </div>
                          <div>
                            <p class="font-semibold text-(--textsub1)">{{ $currency->currency_name }}</p>
                            <p class="text-xs text-(--textsub1)">{{ \Illuminate\Support\Str::limit($currency->deskripsi, 60) ?: '-' }}</p>
                          </div>
                        </div>
                        <div class="card-actions justify-end">
                          <button type="button" class="btn btn-xs btn-outline text-(--textsub1)" onclick="document.getElementById('editCurrencyModal-{{ $currency->id_currency }}').showModal()">Edit</button>
                          <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" onsubmit="return confirm('Hapus currency ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-xs btn-error">Hapus</button>
                          </form>
                        </div>
                      </div>
                    </div>
                  @empty
                    <p class="text-sm text-(--textsub1)">Belum ada currency untuk game ini.</p>
                  @endforelse
                </div>
              </div>
            </div>
          @empty
            <p class="text-sm text-(--textsub1)">Tambah game terlebih dahulu untuk mengelola currency.</p>
          @endforelse
        </div>

        {{-- modal tambah currency --}}
        @foreach ($games as $game)
          <dialog id="addCurrencyModal-{{ $game->id_game }}" class="modal">
            <div class="modal-box space-y-4">
              <h4 class="text-lg font-semibold text-(--textsub1)">Tambah Currency untuk {{ $game->nama_game }}</h4>
              <form method="POST" action="{{ route('admin.games.currencies.store', $game) }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="form-control">
                  <label class="label text-(--textsub1)"> <span class="label-text">Nama Currency</span> </label>
                  <input type="text" name="currency_name" class="input input-bordered text-(--textsub1)" required />
                </div>
                <div class="form-control">
                  <label class="label text-(--textsub1)"> <span class="label-text">Deskripsi</span> </label>
                  <textarea name="deskripsi" rows="3" class="textarea textarea-bordered text-(--textsub1)" placeholder="Tulis deskripsi singkat"></textarea>
                </div>
                <div class="form-control">
                  <label class="label text-(--textsub1)"> <span class="label-text">Gambar</span> </label>
                  <input type="file" name="gambar_currency" class="file-input file-input-bordered text-(--textsub1)" accept="image/*" />
                </div>
                <div class="modal-action">
                  <button type="button" class="btn" onclick="document.getElementById('addCurrencyModal-{{ $game->id_game }}').close()">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
              </form>
            </div>
          </dialog>
        @endforeach

        {{-- modal edit currency --}}
        @foreach ($games as $game)
          @foreach ($game->currencies as $currency)
            <dialog id="editCurrencyModal-{{ $currency->id_currency }}" class="modal">
              <div class="modal-box space-y-4">
                <h4 class="text-lg font-semibold text-(--textsub1)">Edit Currency: {{ $currency->currency_name }}</h4>
                <form method="POST" action="{{ route('admin.currencies.update', $currency) }}" enctype="multipart/form-data" class="space-y-3">
                  @csrf
                  @method('PUT')
                  <div class="form-control">
                    <label class="label text-(--textsub1)"> <span class="label-text">Nama Currency</span> </label>
                    <input type="text" name="currency_name" class="input input-bordered text-(--textsub1)" value="{{ old('currency_name', $currency->currency_name) }}" required />
                  </div>
                  <div class="form-control">
                    <label class="label text-(--textsub1)"> <span class="label-text">Deskripsi</span> </label>
                    <textarea name="deskripsi" rows="3" class="textarea textarea-bordered text-(--textsub1)" placeholder="Tulis deskripsi singkat">{{ old('deskripsi', $currency->deskripsi) }}</textarea>
                  </div>
                  <div class="form-control space-y-2">
                    <label class="label text-(--textsub1)"> <span class="label-text">Gambar</span> </label>
                    <input type="file" name="gambar_currency" class="file-input file-input-bordered text-(--textsub1)" accept="image/*" />
                    @if ($currency->gambar_currency_url)
                      <img src="{{ $currency->gambar_currency_url }}" alt="{{ $currency->currency_name }}" class="rounded-lg w-full max-h-40 object-cover" />
                    @endif
                  </div>
                  <div class="modal-action">
                    <button type="button" class="btn" onclick="document.getElementById('editCurrencyModal-{{ $currency->id_currency }}').close()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                  </div>
                </form>
              </div>
            </dialog>
          @endforeach
        @endforeach
      </div>

      {{-- tab game package --}}

      <input type="radio" name="management_tabs" role="tab" class="tab" aria-label="Game Package" data-management-tab="packages" @checked($activeManagementTab === 'packages') />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <div class="space-y-6">
          @forelse ($games as $game)
            <div class="card border border-base-300 shadow-sm">
              <div class="card-body space-y-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                  <div class="flex items-center gap-3">
                    <div class="avatar">
                      <div class="w-12 rounded-xl">
                        <img src="{{ $game->gambar_url ?? 'https://placehold.co/120x120?text=Game' }}" alt="{{ $game->nama_game }}" />
                      </div>
                    </div>
                    <div>
                      <p class="font-semibold text-(--textsub1)">{{ $game->nama_game }}</p>
                      <p class="text-xs text-(--textsub1)">{{ $game->currencies->count() }} currency terdaftar</p>
                    </div>
                  </div>
                </div>

                @foreach ($game->currencies as $currency)
                  <div class="border border-base-200 rounded-box p-4 space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                      <div class="flex items-center gap-3">
                        <div class="avatar">
                          <div class="w-12 rounded-xl">
                            <img src="{{ $currency->gambar_currency_url ?? 'https://placehold.co/120x120?text=Currency' }}" alt="{{ $currency->currency_name }}" />
                          </div>
                        </div>
                        <div>
                          <p class="font-semibold text-(--textsub1)">{{ $currency->currency_name }}</p>
                          <p class="text-xs text-(--textsub1)">{{ $currency->packages->count() }} paket · {{ \Illuminate\Support\Str::limit($currency->deskripsi, 60) ?: '-' }}</p>
                        </div>
                      </div>
                      <button class="btn btn-sm btn-primary" onclick="document.getElementById('addPackageModal-{{ $currency->id_currency }}').showModal()">Tambah Paket</button>
                    </div>

                    <div class="overflow-x-auto table-responsive">
                      <table class="table table-sm">
                        <thead>
                          <tr class="text-xs text-(--textsub1)">
                            <th>Jumlah</th>
                            <th>Harga</th>
                            <th>Deskripsi</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                          </tr>
                        </thead>
                        <tbody>
                          @forelse ($currency->packages as $package)
                            <tr class="text-(--textsub1)">
                              <td>{{ number_format($package->amount) }}</td>
                              <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                              <td class="text-xs text-(--textsub1)">{{ \Illuminate\Support\Str::limit($package->deskripsi, 80) ?: '-' }}</td>
                              <td class="text-xs">{{ $package->created_at?->format('d M Y H:i') ?? '-' }}</td>
                              <td>
                                <div class="flex flex-wrap gap-2">
                                  <button type="button" class="btn btn-xs btn-outline" onclick="document.getElementById('editPackageModal-{{ $package->id_package }}').showModal()">Edit</button>
                                  <form method="POST" action="{{ route('admin.packages.destroy', $package) }}" onsubmit="return confirm('Hapus paket ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-error">Hapus</button>
                                  </form>
                                </div>
                              </td>
                            </tr>
                          @empty
                            <tr>
                              <td colspan="5" class="text-center text-xs text-(--textsub1)">Belum ada paket untuk currency ini.</td>
                            </tr>
                          @endforelse
                        </tbody>
                      </table>
                    </div>
                  </div>
                @endforeach
              </div>
            </div>
          @empty
            <p class="text-sm text-(--textsub1)">Tambah game dan currency terlebih dahulu untuk mengatur paket.</p>
          @endforelse
        </div>

        {{-- modal tambah package  --}}
        @foreach ($games as $game)
          @foreach ($game->currencies as $currency)
            <dialog id="addPackageModal-{{ $currency->id_currency }}" class="modal">
              <div class="modal-box space-y-4">
                <h4 class="text-lg font-semibold text-(--textsub1)">Tambah Paket untuk {{ $currency->currency_name }}</h4>
                <form method="POST" action="{{ route('admin.currencies.packages.store', $currency) }}" class="space-y-3">
                  @csrf
                  <div class="form-control">
                    <label class="label text-(--textsub1)"> <span class="label-text">Jumlah</span> </label>
                    <input type="number" name="amount" class="input input-bordered text-(--textsub1)" min="1" required />
                  </div>
                  <div class="form-control">
                    <label class="label text-(--textsub1)"> <span class="label-text">Harga</span> </label>
                    <input type="number" name="price" class="input input-bordered text-(--textsub1)" min="0" step="0.01" required />
                  </div>
                  <div class="form-control">
                    <label class="label text-(--textsub1)"> <span class="label-text">Deskripsi</span> </label>
                    <textarea name="deskripsi" rows="3" class="textarea textarea-bordered text-(--textsub1)" placeholder="Contoh: Bonus item atau detail paket"></textarea>
                  </div>
                  <div class="modal-action">
                    <button type="button" class="btn" onclick="document.getElementById('addPackageModal-{{ $currency->id_currency }}').close()">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                  </div>
                </form>
              </div>
            </dialog>
          @endforeach
        @endforeach

        {{-- modal edit package --}}
        @foreach ($games as $game)
          @foreach ($game->currencies as $currency)
            @foreach ($currency->packages as $package)
              <dialog id="editPackageModal-{{ $package->id_package }}" class="modal">
                <div class="modal-box space-y-4">
                  <h4 class="text-lg font-semibold text-(--textsub1)">Edit Paket: {{ $currency->currency_name }} - {{ number_format($package->amount) }}</h4>
                  <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div class="form-control">
                      <label class="label text-(--textsub1)"> <span class="label-text">Jumlah</span> </label>
                      <input type="number" name="amount" class="input input-bordered text-(--textsub1)" min="1" value="{{ old('amount', $package->amount) }}" required />
                    </div>
                    <div class="form-control">
                      <label class="label text-(--textsub1)"> <span class="label-text">Harga</span> </label>
                      <input type="number" name="price" class="input input-bordered text-(--textsub1)" min="0" step="0.01" value="{{ old('price', $package->price) }}" required />
                    </div>
                    <div class="form-control">
                      <label class="label text-(--textsub1)"> <span class="label-text">Deskripsi</span> </label>
                      <textarea name="deskripsi" rows="3" class="textarea textarea-bordered text-(--textsub1)" placeholder="Contoh: Bonus item atau detail paket">{{ old('deskripsi', $package->deskripsi) }}</textarea>
                    </div>
                    <div class="modal-action">
                      <button type="button" class="btn" onclick="document.getElementById('editPackageModal-{{ $package->id_package }}').close()">Batal</button>
                      <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                  </form>
                </div>
              </dialog>
            @endforeach
          @endforeach
        @endforeach
      </div>
    </div>
  </section>

  {{-- form persetujuan transaksi --}}
  <section data-page="approval" class="{{ $activePage === 'approval' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Form Persetujuan</h2>
      <p class="text-sm text-(--textsub1)">Review dan setujui transaksi top up yang masuk.</p>
    </div>

    <div class="card shadow-lg">
      <div class="card-body gap-6">

        <div class="tabs tabs-lifted">
          <input type="radio" name="approval_tabs" role="tab" class="tab" aria-label="Top Up Koin" checked />
          <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <div class="overflow-x-auto rounded-2xl border border-base-200 table-responsive">
              <table class="table table-pin-rows">
                <thead>
                  <tr class="text-sm text-(--textsub1)">
                    <th colspan="7" class="text-base font-semibold text-(--textsub1)">Persetujuan Top Up Koin</th>
                  </tr>
                  <tr class="text-sm text-(--textsub1)">
                    <th>Kode</th>
                    <th>User</th>
                    <th>Koin</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Diajukan</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($pendingCoinPurchases as $purchase)
                    <tr class="text-(--textsub1)">
                      <td class="font-semibold">{{ $purchase->transaction_code }}</td>
                      <td>
                        <div class="flex flex-col">
                          <span class="font-medium">{{ $purchase->user->username }}</span>
                          <span class="text-xs text-(--textsub1)">{{ $purchase->user->email }}</span>
                        </div>
                      </td>
                      <td>{{ number_format($purchase->coin_amount) }} koin</td>
                      <td>Rp {{ number_format($purchase->price_idr, 0, ',', '.') }}</td>
                      <td>{{ config('coin.payment_methods.' . $purchase->payment_method . '.label') ?? strtoupper($purchase->payment_method) }}</td>
                      <td>{{ optional($purchase->created_at)->format('d M Y H:i') }}</td>
                      <td>
                        <div class="join">
                          <form method="POST" action="{{ route('admin.coin-purchases.approve', $purchase) }}" class="join-item">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success btn-sm">Approve</button>
                          </form>
                          <button type="button" class="btn btn-outline btn-error btn-sm join-item" onclick="document.getElementById('rejectCoinPurchaseModal-{{ $purchase->id_coin_purchase }}').showModal()">Tolak</button>
                        </div>
                      </td>
                    </tr>
                    <tr class="bg-base-200/40">
                      <td colspan="7">
                        <div class="grid gap-4 md:grid-cols-3">
                          <div class="space-y-1">
                            <h4 class="text-sm font-semibold text-(--textsub1)">Data Pengguna</h4>
                            <p class="text-xs text-(--textsub1)">Nama: {{ $purchase->user->nama_lengkap ?? $purchase->user->username }}</p>
                            <p class="text-xs text-(--textsub1)">Email: {{ $purchase->user->email }}</p>
                            <p class="text-xs text-(--textsub1)">No. Telepon: {{ $purchase->user->nomor_telepon ?? '-' }}</p>
                          </div>
                          <div class="space-y-1">
                            <h4 class="text-sm font-semibold text-(--textsub1)">Ringkasan Pembayaran</h4>
                            <p class="text-xs text-(--textsub1)">Metode: {{ config('coin.payment_methods.' . $purchase->payment_method . '.label') ?? strtoupper($purchase->payment_method) }}</p>
                            <p class="text-xs text-(--textsub1)">Jumlah: Rp {{ number_format($purchase->price_idr, 0, ',', '.') }}</p>
                            <p class="text-xs text-(--textsub1)">Koin: {{ number_format($purchase->coin_amount) }} koin</p>
                            @if ($purchase->payment_meta['proof_uploaded_at'] ?? false)
                              <p class="text-xs text-(--textsub1)">Bukti diunggah: {{ \Illuminate\Support\Carbon::parse($purchase->payment_meta['proof_uploaded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                            @endif
                          </div>
                          <div class="space-y-2">
                            <h4 class="text-sm font-semibold text-(--textsub1)">Bukti Pembayaran</h4>
                            @if ($purchase->payment_proof_url)
                              <img src="{{ $purchase->payment_proof_url }}" alt="Bukti pembayaran {{ $purchase->transaction_code }}" class="w-full max-w-xs rounded-xl border border-base-300 object-cover" />
                            @else
                              <p class="text-xs text-(--textsub1)">Belum ada bukti pembayaran diunggah.</p>
                            @endif
                          </div>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="7" class="text-center text-sm text-(--textsub1)">Belum ada transaksi koin yang menunggu persetujuan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @foreach ($pendingCoinPurchases as $purchase)
              <dialog id="rejectCoinPurchaseModal-{{ $purchase->id_coin_purchase }}" class="modal">
                <div class="modal-box space-y-4">
                  <h4 class="text-lg font-semibold text-(--textsub1)">Tolak Top Up Koin - {{ $purchase->transaction_code }}</h4>
                  <form method="POST" action="{{ route('admin.coin-purchases.reject', $purchase) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="form-control">
                      <label class="label text-(--textsub1)">
                        <span class="label-text">Alasan Penolakan</span>
                      </label>
                      <textarea name="rejection_reason" rows="4" class="textarea textarea-bordered text-(--textsub1)" placeholder="Tuliskan alasan penolakan" maxlength="500" required></textarea>
                    </div>
                    <div class="modal-action">
                      <button type="button" class="btn" onclick="document.getElementById('rejectCoinPurchaseModal-{{ $purchase->id_coin_purchase }}').close()">Batal</button>
                      <button type="submit" class="btn btn-error">Tolak Transaksi</button>
                    </div>
                  </form>
                </div>
              </dialog>
            @endforeach
          </div>

          <input type="radio" name="approval_tabs" role="tab" class="tab" aria-label="Top Up Game" />
          <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6">
            <div class="overflow-x-auto rounded-2xl border border-base-200 table-responsive">
              <table class="table table-pin-rows">
                <thead>
                  <tr class="text-sm text-(--textsub1)">
                    <th colspan="8" class="text-base font-semibold text-(--textsub1)">Persetujuan Top Up Game</th>
                  </tr>
                  <tr class="text-sm text-(--textsub1)">
                    <th>Kode</th>
                    <th>User</th>
                    <th>Game</th>
                    <th>Paket</th>
                    <th>Total</th>
                    <th>Metode</th>
                    <th>Diajukan</th>
                    <th>Aksi</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($pendingGameTopups as $topup)
                    <tr class="text-(--textsub1)">
                      <td class="font-semibold">{{ $topup->transaction_code }}</td>
                      <td>
                        @php
                          $customerName = $topup->user->username ?? 'Tamu';
                          $customerEmail = $topup->user->email ?? ($topup->contact_email ?? '-');
                        @endphp
                        <div class="flex flex-col">
                          <span class="font-medium">{{ $customerName }}</span>
                          <span class="text-xs text-(--textsub1)">{{ $customerEmail }}</span>
                        </div>
                      </td>
                      <td>{{ $topup->game?->nama_game ?? '-' }}</td>
                      <td>{{ number_format($topup->package?->amount ?? 0) }} {{ $topup->currency?->currency_name }}</td>
                      <td>Rp {{ number_format($topup->price_idr, 0, ',', '.') }}</td>
                      <td>{{ config('coin.payment_methods.' . $topup->payment_method . '.label') ?? strtoupper($topup->payment_method) }}</td>
                      <td>{{ optional($topup->created_at)->format('d M Y H:i') }}</td>
                      <td>
                        <div class="join">
                          <form method="POST" action="{{ route('admin.game-topups.approve', $topup) }}" class="join-item">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-success btn-sm">Approve</button>
                          </form>
                          <button type="button" class="btn btn-outline btn-error btn-sm join-item" onclick="document.getElementById('rejectGameTopupModal-{{ $topup->id_game_topup }}').showModal()">Tolak</button>
                        </div>
                      </td>
                    </tr>
                    <tr class="bg-base-200/40">
                      <td colspan="8">
                        <div class="grid gap-4 lg:grid-cols-3">
                          <div class="space-y-1">
                            <h4 class="text-sm font-semibold text-(--textsub1)">Data Akun Game</h4>
                            @php
                              $accountEntries = collect($topup->account_data ?? [])->filter(fn ($value) => filled($value));
                            @endphp
                            @if ($accountEntries->isEmpty())
                              <p class="text-xs text-(--textsub1)">Tidak ada data akun yang diisi.</p>
                            @else
                              <ul class="space-y-1 text-xs text-(--textsub1)">
                                @foreach ($accountEntries as $label => $value)
                                  <li><span class="font-medium text-(--textsub1)">{{ ucwords(str_replace('_', ' ', $label)) }}:</span> {{ $value }}</li>
                                @endforeach
                              </ul>
                            @endif
                          </div>
                          <div class="space-y-1">
                            <h4 class="text-sm font-semibold text-(--textsub1)">Kontak Pengguna</h4>
                            <p class="text-xs text-(--textsub1)">Nama: {{ $topup->user->nama_lengkap ?? $customerName }}</p>
                            <p class="text-xs text-(--textsub1)">Email: {{ $customerEmail }}</p>
                            <p class="text-xs text-(--textsub1)">WhatsApp: {{ $topup->contact_whatsapp ?? '-' }}</p>
                            <p class="text-xs text-(--textsub1)">No. Telepon: {{ $topup->user->nomor_telepon ?? '-' }}</p>
                            @if ($topup->payment_meta['proof_uploaded_at'] ?? false)
                              <p class="text-xs text-(--textsub1)">Bukti diunggah: {{ \Illuminate\Support\Carbon::parse($topup->payment_meta['proof_uploaded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                            @endif
                          </div>
                          <div class="space-y-2">
                            <h4 class="text-sm font-semibold text-(--textsub1)">Bukti Pembayaran</h4>
                            @if ($topup->payment_proof_url)
                              <img src="{{ $topup->payment_proof_url }}" alt="Bukti pembayaran {{ $topup->transaction_code }}" class="w-full max-w-xs rounded-xl border border-base-300 object-cover" />
                            @else
                              <p class="text-xs text-(--textsub1)">Belum ada bukti pembayaran diunggah.</p>
                            @endif
                          </div>
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="8" class="text-center text-sm text-(--textsub1)">Belum ada transaksi top up game yang menunggu persetujuan.</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>
            @foreach ($pendingGameTopups as $topup)
              <dialog id="rejectGameTopupModal-{{ $topup->id_game_topup }}" class="modal">
                <div class="modal-box space-y-4">
                  <h4 class="text-lg font-semibold text-(--textsub1)">Tolak Top Up Game - {{ $topup->transaction_code }}</h4>
                  <form method="POST" action="{{ route('admin.game-topups.reject', $topup) }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <div class="form-control">
                      <label class="label">
                        <span class="label-text text-(--textsub1)">Alasan Penolakan</span>
                      </label>
                      <textarea name="rejection_reason" rows="4" class="textarea textarea-bordered text-(--textsub1)" placeholder="Tuliskan alasan penolakan" maxlength="500" required></textarea>
                    </div>
                    <div class="modal-action">
                      <button type="button" class="btn" onclick="document.getElementById('rejectGameTopupModal-{{ $topup->id_game_topup }}').close()">Batal</button>
                      <button type="submit" class="btn btn-error">Tolak Transaksi</button>
                    </div>
                  </form>
                </div>
              </dialog>
            @endforeach
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- form riwayat pesanan --}}

  <section data-page="orders" class="{{ $activePage === 'orders' ? '' : 'hidden' }} space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Riwayat Pesanan User</h2>
      <p class="text-sm text-(--textsub1)">Lacak semua transaksi yang pernah dilakukan user.</p>
    </div>

    <div class="card shadow-lg">
      <div class="card-body gap-6">
        <form method="GET" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
          <input type="hidden" name="tab" value="orders" />
          <div class="form-control md:col-span-2 lg:col-span-1">
            <label class="label ">
              <span class="label-text text-(--textsub1)">Jenis Transaksi</span>
            </label>
            <select name="order_type" class="select select-bordered text-(--textsub1)" onchange="this.form.submit()">
              @foreach ($orderTypeOptions as $value => $label)
                <option value="{{ $value }}" @selected($orderType === $value)>{{ $label }}</option>
              @endforeach
            </select>
          </div>
        </form>

        <div class="overflow-x-auto table-responsive">
          <table class="table table-zebra">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Tanggal</th>
                <th>Order ID</th>
                <th>User</th>
                <th>Game / Jenis</th>
                <th>Detail</th>
                <th>Status</th>
                <th>Total</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($orders as $order)
                @php
                  $orderCode = $order->gameTopup?->transaction_code
                      ?? $order->coinPurchase?->transaction_code
                      ?? ('TRX-' . str_pad((string) $order->id_transaksi, 5, '0', STR_PAD_LEFT));

                  $userName = $order->user->username
                      ?? ($order->id_user ? 'User #' . $order->id_user : 'Tamu');
                  $userEmail = $order->user->email
                      ?? ($order->gameTopup?->contact_email ?? '-');

                  $gameName = $order->jenis_transaksi === 'topup'
                      ? ($order->gameTopup?->game?->nama_game ?? '-')
                      : ($order->jenis_transaksi === 'purchase' ? 'Top Up Koin' : ucfirst($order->jenis_transaksi));

                  if ($order->jenis_transaksi === 'topup') {
                      $amount = $order->gameTopup?->package?->amount;
                      $currencyName = $order->gameTopup?->currency?->currency_name;
                      $detail = $amount && $currencyName
                          ? number_format($amount) . ' ' . $currencyName
                          : 'Top up game';
                  } elseif ($order->jenis_transaksi === 'purchase') {
                      $coinAmount = $order->coinPurchase?->coin_amount;
                      $packageKey = $order->coinPurchase?->package_key;
                      $packageLabel = $packageKey && isset($coinPackages[$packageKey]['label'])
                          ? $coinPackages[$packageKey]['label']
                          : null;
                      $detailParts = [];
                      if ($packageLabel) {
                          $detailParts[] = $packageLabel;
                      }
                      if ($coinAmount) {
                          $detailParts[] = number_format($coinAmount) . ' koin';
                      }
                      $detail = implode(' • ', $detailParts) ?: 'Top up koin';
                  } else {
                      $detail = ucfirst($order->jenis_transaksi ?? '-');
                  }

                  $status = $order->status ?? '-';
                  $statusLabelMap = [
                      'completed' => 'Sukses',
                      'approved' => 'Disetujui',
                      'pending' => 'Menunggu',
                      'rejected' => 'Ditolak',
                      'failed' => 'Gagal',
                      'cancelled' => 'Dibatalkan',
                  ];
                  $statusLabel = $statusLabelMap[$status] ?? ucfirst($status);
                  $statusClass = match ($status) {
                      'completed', 'approved' => 'badge-success',
                      'pending' => 'badge-warning',
                      'rejected', 'failed', 'cancelled' => 'badge-error',
                      default => 'badge-ghost',
                  };

                  $orderDate = optional($order->tanggal_transaksi)->format('d M Y H:i') ?? '-';
                  $totalFormatted = 'Rp ' . number_format($order->harga ?? 0, 0, ',', '.');
                @endphp
                <tr class="text-(--textsub1)">
                  <td>{{ $orderDate }}</td>
                  <td>{{ $orderCode }}</td>
                  <td>
                    <div class="flex flex-col">
                      <span class="font-medium">{{ $userName }}</span>
                      <span class="text-xs text-(--textsub1)">{{ $userEmail }}</span>
                    </div>
                  </td>
                  <td>{{ $gameName }}</td>
                  <td>{{ $detail }}</td>
                  <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                  <td>{{ $totalFormatted }}</td>
                  <td>
                    @if (in_array($order->status, ['approved', 'completed'], true))
                      <button type="button" class="btn btn-ghost btn-sm gap-2 text-primary" data-invoice-url="{{ route('invoices.show', $order) }}">
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
                  <td colspan="8" class="text-center text-sm text-(--textsub1)">Belum ada transaksi untuk ditampilkan.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <div class="flex flex-col items-center justify-between gap-4 md:flex-row">
          <p class="text-sm text-(--textsub1)">
            @if ($orders->count())
              Menampilkan {{ $orders->firstItem() }}-{{ $orders->lastItem() }} dari {{ number_format($orders->total()) }} pesanan.
            @else
              Belum ada data pesanan.
            @endif
          </p>
          <div class="join">
            @php
              $prevUrl = $orders->previousPageUrl();
              $nextUrl = $orders->nextPageUrl();
            @endphp
            <a class="btn btn-sm join-item {{ $orders->onFirstPage() ? 'btn-disabled pointer-events-none' : '' }}" href="{{ $prevUrl ?? '#' }}">«</a>
            <span class="btn btn-sm join-item btn-ghost pointer-events-none">Hal {{ $orders->currentPage() }}</span>
            <a class="btn btn-sm join-item {{ $orders->hasMorePages() ? '' : 'btn-disabled pointer-events-none' }}" href="{{ $nextUrl ?? '#' }}">»</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</x-sidebar-admin>
@endsection

</x-layouts.app>

@include('components.invoice-modal')