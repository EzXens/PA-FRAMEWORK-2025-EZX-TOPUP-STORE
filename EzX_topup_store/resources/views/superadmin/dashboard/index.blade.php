<x-layouts.app>

@section('sidebar-superadmin')
<x-sidebar-superadmin>
  @php
    $monthlyFinancials = collect($monthlyFinancials ?? []);
    $monthlyChartData = $monthlyChartData ?? ['labels' => collect(), 'revenue' => collect(), 'pending' => collect()];
    $monthlyChartLabels = collect($monthlyChartData['labels'] ?? [])->values();
    $monthlyChartRevenue = collect($monthlyChartData['revenue'] ?? [])->map(fn ($value) => (int) $value)->values();
    $monthlyChartPending = collect($monthlyChartData['pending'] ?? [])->map(fn ($value) => (int) $value)->values();
    $transactionTypeChart = collect($transactionTypeChartData ?? [])->values();
    $transactionTypeLabels = $transactionTypeChart->pluck('type');
    $transactionTypeTotals = $transactionTypeChart->pluck('total');
    $transactionTypeRevenue = $transactionTypeChart->pluck('revenue');
    $peakRevenueMonth = $monthlyFinancials->sortByDesc('revenue')->first();
    $latestMonth = $monthlyFinancials->first();
    $currentMonthAverageDaily = $latestMonth
        ? (int) round(($latestMonth['transaction_count'] ?? 0) / max(now()->daysInMonth, 1))
        : 0;
    $totalChartRevenue = $monthlyChartRevenue->sum();
  @endphp
  <section data-page="dashboard" class="space-y-6">
    <div class="flex flex-col gap-2">
      <h1 class="text-2xl font-semibold text-(--textsub1)">Dashboard Super Admin</h1>
      <p class="text-sm text-(--textsub1)">Statistik utama platform dan aktivitas operasional.</p>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4 stat-grid">
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Total User Terdaftar</div>
        <div class="stat-value text-primary">{{ number_format($stats['totalUsers']) }}</div>
        <div class="stat-desc text-success">Semua akun role user</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Admin Aktif</div>
        <div class="stat-value text-secondary">{{ number_format($stats['totalAdmins']) }}</div>
        <div class="stat-desc text-(--textsub1)">Termasuk admin regional</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Total Transaksi</div>
        <div class="stat-value">{{ number_format($stats['totalTransactions']) }}</div>
        <div class="stat-desc text-success">Seluruh jenis transaksi</div>
      </div>
      <div class="stat bg-base-100 shadow">
        <div class="stat-title">Pendapatan</div>
        <div class="stat-value text-accent">Rp {{ number_format($stats['totalRevenue'], 0, ',', '.') }}</div>
        <div class="stat-desc text-success">Status transaksi selesai</div>
      </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3 dashboard-grid cols-3">
      <div class="card shadow-lg xl:col-span-2">
        <div class="card-body gap-6">
          <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="card-title text-xl text-(--textsub1)">Tren Transaksi &amp; Pendapatan</h2>
            <span class="badge badge-secondary badge-outline ">{{ $monthlyChartLabels->count() }} periode terakhir</span>
          </div>
          <p class="text-sm text-(--textsub1)">Perbandingan pendapatan selesai dan nilai pending berdasarkan periode transaksi.</p>
          <div class="h-64 w-full">
            <canvas id="superadminRevenueChart"></canvas>
          </div>
          <div class="grid gap-4 lg:grid-cols-2">
            <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-1">
              <p class="text-xs text-(--textsub1)">Pendapatan Tertinggi</p>
              <p class="text-lg font-semibold text-(--textsub1)">{{ data_get($peakRevenueMonth, 'label', 'Tidak ada data') }}</p>
              <p class="text-sm text-(--textsub1)">Rp {{ number_format(data_get($peakRevenueMonth, 'revenue', 0), 0, ',', '.') }}</p>
            </div>
            <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-1">
              <p class="text-xs text-(--textsub1)">Transaksi Bulan Terbaru</p>
              <p class="text-lg font-semibold text-(--textsub1)">{{ data_get($latestMonth, 'label', 'Tidak ada data') }}</p>
              <p class="text-sm text-(--textsub1)">{{ number_format(data_get($latestMonth, 'transaction_count', 0)) }} transaksi · {{ $currentMonthAverageDaily }} per hari</p>
            </div>
            <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-1">
              <p class="text-xs text-(--textsub1)">Total Pendapatan Tercatat</p>
              <p class="text-lg font-semibold text-(--textsub1)">Rp {{ number_format($totalChartRevenue, 0, ',', '.') }}</p>
              <p class="text-xs text-(--textsub1)">Akumulasi dari seluruh periode pada grafik</p>
            </div>
            <div class="rounded-box border border-base-200 bg-base-200/40 p-4 space-y-1">
              <p class="text-xs text-(--textsub1)">Nilai Pending Tertinggi</p>
              @php
                $peakPending = $monthlyFinancials->sortByDesc('pending')->first();
              @endphp
              <p class="text-lg font-semibold text-(--textsub1)">{{ data_get($peakPending, 'label', 'Tidak ada data') }}</p>
              <p class="text-sm text-(--textsub1)">Rp {{ number_format(data_get($peakPending, 'pending', 0), 0, ',', '.') }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="space-y-6">
        <div class="card shadow-lg">
          <div class="card-body gap-4">
            <h2 class="card-title text-xl text-(--textsub1)">Distribusi Jenis Transaksi</h2>
            <p class="text-sm text-(--textsub1)">Proporsi volume transaksi berdasarkan kategori layanan.</p>
            <div class="h-64">
              <canvas id="superadminTransactionPie"></canvas>
            </div>
            <div class="space-y-3">
              @forelse ($transactionTypeChart as $type)
                <div class="flex items-center justify-between text-sm">
                  <div>
                    <p class="font-medium capitalize text-(--textsub1)">{{ str_replace('_', ' ', $type['type']) }}</p>
                    <p class="text-xs text-(--textsub1)">Rp {{ number_format($type['revenue'] ?? 0, 0, ',', '.') }}</p>
                  </div>
                  <span class="badge badge-primary badge-outline">{{ number_format($type['total'] ?? 0) }} trx</span>
                </div>
              @empty
                <p class="text-sm text-(--textsub1)">Belum ada data transaksi.</p>
              @endforelse
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- tab manajemen akun --}}

  <section data-page="accounts" class="hidden space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Manajemen Akun</h2>
      <p class="text-sm text-(--textsub1)">Kelola akun user dan admin dengan cepat.</p>
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

    {{-- tab user --}}
    <div class="tabs tabs-lifted">
      <input type="radio" name="account_tabs" role="tab" class="tab" aria-label="User" checked />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-(--textsub1)">Daftar User</h3>
          <p class="text-sm text-(--textsub1)">Total: {{ number_format($stats['totalUsers']) }} user</p>
        </div>
        <div class="overflow-x-auto table-responsive">
          <table class="table table-zebra">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Nama</th>
                <th>Email</th>
                <th>Terdaftar</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($users as $user)
                <tr class="text-(--textsub1)">
                  <td class="font-medium">{{ $user->username }}</td>
                  <td>{{ $user->email }}</td>
                  <td>{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
              @empty
                <tr class="text-(--textsub1)">
                  <td colspan="3" class="text-center text-sm text-(--textsub1)">Belum ada data user.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>

      {{-- tab admin --}}
      <input type="radio" name="account_tabs" role="tab" class="tab" aria-label="Admin" />
      <div role="tabpanel" class="tab-content bg-base-100 border-base-300 rounded-box p-6 space-y-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <h3 class="text-lg font-semibold text-(--textsub1)">Daftar Admin</h3>
          <button class="btn btn-primary btn-sm" onclick="document.getElementById('createAdminModal').showModal()">Tambah Admin</button>
        </div>

        {{-- modal tambah admin --}}
        <dialog id="createAdminModal" class="modal">
          <div class="modal-box space-y-4">
            <h4 class="text-lg font-semibold text-(--textsub1)">Tambah Admin Baru</h4>
            <form method="POST" action="{{ route('superadmin.admins.store') }}" class="space-y-3">
              @csrf
              <div class="form-control text-(--textsub1)">
                <label class="label"> <span class="label-text">Username</span> </label>
                <input type="text" name="username" class="input input-bordered" required />
              </div>
              <div class="form-control text-(--textsub1)">
                <label class="label"> <span class="label-text">Email</span> </label>
                <input type="email" name="email" class="input input-bordered" required />
              </div>
              <div class="form-control text-(--textsub1)">
                <label class="label"> <span class="label-text">Password</span> </label>
                <input type="password" name="password" class="input input-bordered" required />
              </div>
              <div class="modal-action">
                <button type="button" class="btn" onclick="document.getElementById('createAdminModal').close()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
              </div>
            </form>
          </div>
        </dialog>

        <div class="overflow-x-auto table-responsive">
          <table class="table">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Nama</th>
                <th>Email</th>
                <th>Status</th>
                <th>Dibuat</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($admins as $admin)
                <tr class="text-(--textsub1)">
                  <td class="font-medium">{{ $admin->username }}</td>
                  <td>{{ $admin->email }}</td>
                  <td><span class="badge badge-success">Aktif</span></td>
                  <td>{{ $admin->created_at?->format('d M Y H:i') ?? '-' }}</td>
                  <td>
                    <div class="flex flex-wrap gap-2">
                      <button type="button" class="btn btn-xs btn-outline" onclick="document.getElementById('editAdminModal-{{ $admin->id_user }}').showModal()">Edit</button>
                      <form method="POST" action="{{ route('superadmin.admins.destroy', $admin) }}" onsubmit="return confirm('Hapus admin ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-xs btn-error">Hapus</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @empty
                <tr class="text-(--textsub1)">
                  <td colspan="5" class="text-center text-sm text-(--textsub1)">Belum ada data admin.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        {{-- edit admin modal --}}
        @foreach ($admins as $admin)
          <dialog id="editAdminModal-{{ $admin->id_user }}" class="modal">
            <div class="modal-box space-y-4">
              <h4 class="text-lg font-semibold text-(--textsub1)">Edit Admin: {{ $admin->username }}</h4>
              <form method="POST" action="{{ route('superadmin.admins.update', $admin) }}" class="space-y-3">
                @csrf
                @method('PUT')
                <div class="form-control text-(--textsub1)">
                  <label class="label"> <span class="label-text">Username</span> </label>
                  <input type="text" name="username" class="input input-bordered" value="{{ old('username', $admin->username) }}" required />
                </div>
                <div class="form-control text-(--textsub1)">
                  <label class="label"> <span class="label-text">Email</span> </label>
                  <input type="email" name="email" class="input input-bordered" value="{{ old('email', $admin->email) }}" required />
                </div>
                <div class="form-control text-(--textsub1)">
                  <label class="label"> <span class="label-text">Password Baru (opsional)</span> </label>
                  <input type="password" name="password" class="input input-bordered" placeholder="Kosongkan jika tidak diubah" />
                </div>
                <div class="modal-action">
                  <button type="button" class="btn" onclick="document.getElementById('editAdminModal-{{ $admin->id_user }}').close()">Batal</button>
                  <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
              </form>
            </div>
          </dialog>
        @endforeach
      </div>
    </div>
  </section>

  <section data-page="reports" class="hidden space-y-6">
    <div class="flex flex-col gap-2">
      <h2 class="text-2xl font-semibold text-(--textsub1)">Laporan Keuangan</h2>
      <p class="text-sm text-(--textsub1)">Ringkasan performa finansial dan status audit.</p>
    </div>

    <div class="card shadow-lg">
      <div class="card-body gap-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div>
            <h3 class="card-title text-xl text-(--textsub1)">Ikhtisar Keuangan</h3>
            <p class="text-sm text-(--textsub1)">Periode {{ $financialOverview['periodLabel'] }}</p>
          </div>
        </div>

        <div class="grid gap-4 lg:grid-cols-4">
          <div class="bg-base-200 rounded-box p-4 space-y-1">
            <p class="text-sm text-(--textsub1)">Pendapatan Kotor</p>
            <p class="text-xl font-semibold text-(--textsub1)">Rp {{ number_format($financialOverview['grossRevenue'] ?? 0, 0, ',', '.') }}</p>
          </div>
          <div class="bg-base-200 rounded-box p-4 space-y-1">
            <p class="text-sm text-(--textsub1)">Transaksi Pending</p>
            <p class="text-xl font-semibold text-warning ">Rp {{ number_format($financialOverview['pendingAmount'] ?? 0, 0, ',', '.') }}</p>
          </div>
          <div class="bg-base-200 rounded-box p-4 space-y-1">
            <p class="text-sm text-(--textsub1)">Pendapatan Sementara</p>
            <p class="text-xl font-semibold text-(--textsub1)">Rp {{ number_format(($financialOverview['grossRevenue'] ?? 0) + ($financialOverview['pendingAmount'] ?? 0), 0, ',', '.') }}</p>
          </div>
          <div class="bg-base-200 rounded-box p-4 space-y-1">
            <p class="text-sm text-(--textsub1)">Rata-rata Pendapatan Bulanan</p>
            <p class="text-xl font-semibold text-(--textsub1)">Rp {{ number_format($financialOverview['averageMonthlyRevenue'] ?? 0, 0, ',', '.') }}</p>
          </div>
        </div>

        <div class="overflow-x-auto table-responsive">
          <table class="table table-zebra">
            <thead>
              <tr class="text-sm text-(--textsub1)">
                <th>Bulan</th>
                <th>Pendapatan</th>
                <th>Pending</th>
                <th>Total Transaksi</th>
                <th>Pendapatan + Pending</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($monthlyFinancials as $financial)
                <tr class="text-(--textsub1)">
                  <td>{{ $financial['label'] }}</td>
                  <td>Rp {{ number_format($financial['revenue'], 0, ',', '.') }}</td>
                  <td>Rp {{ number_format($financial['pending'], 0, ',', '.') }}</td>
                  <td>{{ number_format($financial['transaction_count']) }}</td>
                  <td>Rp {{ number_format($financial['revenue'] + $financial['pending'], 0, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="5" class="text-center text-sm text-(--textsub1)">Belum ada data transaksi.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
        </div>

        <a href="{{ route('superadmin.reports.export-csv') }}" class="btn btn-primary">
    Export CSV
      </a>


        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
          <div>
            <p class="font-semibold text-(--textsub1)">Catatan Penting</p>
            <ul class="list-disc pl-5 text-sm text-(--textsub1) space-y-1">
              @foreach ($financialNotes as $note)
                <li>{{ $note }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
</section>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.6/dist/chart.umd.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const revenueLabels = @json($monthlyChartLabels);
      const revenueData = @json($monthlyChartRevenue);
      const pendingData = @json($monthlyChartPending);
      const revenueCanvas = document.getElementById('superadminRevenueChart');
      if (revenueCanvas && Array.isArray(revenueLabels) && revenueLabels.length) {
        new Chart(revenueCanvas, {
          type: 'bar',
          data: {
            labels: revenueLabels,
            datasets: [
              {
                label: 'Pendapatan Selesai',
                data: revenueData,
                backgroundColor: '#2563eb',
                borderRadius: 6,
              },
              {
                label: 'Nilai Pending',
                data: pendingData,
                backgroundColor: '#f97316',
                borderRadius: 6,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                ticks: {
                  callback: (value) => {
                    if (value >= 1000000000) {
                      return 'Rp ' + (value / 1000000000).toFixed(1) + 'B';
                    }
                    if (value >= 1000000) {
                      return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                    }
                    return 'Rp ' + Number(value).toLocaleString('id-ID');
                  },
                },
              },
            },
            plugins: {
              legend: {
                display: true,
              },
            },
          },
        });
      }

      const pieLabels = @json($transactionTypeLabels);
      const pieData = @json($transactionTypeTotals);
      const pieCanvas = document.getElementById('superadminTransactionPie');
      if (pieCanvas && Array.isArray(pieLabels) && pieLabels.length) {
        const palette = ['#2563eb', '#f97316', '#22c55e', '#a855f7', '#facc15', '#ec4899'];
        new Chart(pieCanvas, {
          type: 'pie',
          data: {
            labels: pieLabels.map((label) => (label ?? 'lainnya').replaceAll('_', ' ')),
            datasets: [
              {
                data: pieData,
                backgroundColor: pieLabels.map((_, index) => palette[index % palette.length]),
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
          },
        });
      }
    });
  </script>
</x-sidebar-superadmin>
@endsection

</x-layouts.app>