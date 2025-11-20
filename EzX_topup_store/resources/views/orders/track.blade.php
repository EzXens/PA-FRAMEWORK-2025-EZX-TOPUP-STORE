<x-layouts.app>
    @section('content')
        <section class="min-h-[70vh] py-12" style="background-image: var(--bg-hero); background-size: cover;">
            <div class="mx-auto w-full max-w-4xl px-4 lg:px-6">
                <div class="space-y-6">
                    @if (session('status'))
                        <div class="alert alert-success shadow">
                            <span>{{ session('status') }}</span>
                        </div>
                    @endif

                    <div class="rounded-3xl border border-white/20 bg-black/10 p-6 backdrop-blur-lg shadow-lg text-(--textsub1)">
                        <div class="flex flex-wrap items-center justify-between gap-4">
                            <div>
                                <h1 class="text-3xl font-semibold">Lacak Pesanan</h1>
                                <p class="text-sm text-(--textsub1)/80">Masukkan ID transaksi Anda untuk melihat status pesanan.</p>
                            </div>
                            <span class="badge bg-(--p2) font-bold text-white badge-lg">Pesanan</span>
                        </div>

                        <form method="POST" action="{{ route('orders.track') }}" class="mt-6 space-y-4">
                            @csrf
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text text-(--textsub1)">ID Transaksi</span>
                                </label>
                                <input type="text" name="transaction_code" value="{{ old('transaction_code', $transactionCode) }}"
                                    class="input input-bordered bg-black/10 text-(--textsub1) placeholder:text-(--textsub1)/40" placeholder="Contoh: GT-ABC1234567" required />
                            </div>
                            @error('transaction_code', 'orderTrack')
                                <p class="text-sm text-error">{{ $message }}</p>
                            @enderror
                            <button type="submit" class="btn bg-(--p2) font-bold text-white">Cari Pesanan</button>
                        </form>
                    </div>

                    @if ($transactionCode && ! $topup)
                        <div class="alert alert-error shadow">
                            <span>Pesanan dengan ID {{ $transactionCode }} tidak ditemukan. Periksa kembali ID transaksi Anda.</span>
                        </div>
                    @endif

                    @if ($topup)
                        @php
                            $paymentConfig = config('coin.payment_methods.' . $topup->payment_method, []);
                            $statusClass = [
                                'pending' => 'badge-warning text-black',
                                'approved' => 'badge-success text-black',
                                'rejected' => 'badge-error',
                            ][$topup->status] ?? 'badge-ghost';
                        @endphp
                        <div class="rounded-3xl border border-white/20 bg-black/10 p-6 backdrop-blur-lg shadow-lg text-(--textsub1) space-y-6">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <h2 class="text-2xl font-semibold">Detail Pesanan</h2>
                                    <p class="text-sm text-(--textsub1)/80">Informasi lengkap mengenai pesanan Anda.</p>
                                </div>
                                <span class="badge {{ $statusClass }} badge-lg uppercase">{{ $topup->status }}</span>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2 text-sm text-(--textsub1)/80">
                                <div>
                                    <p class="uppercase text-xs tracking-wide">ID Transaksi</p>
                                    <p class="text-lg font-semibold text-(--textsub1)">{{ $topup->transaction_code }}</p>
                                </div>
                                <div>
                                    <p class="uppercase text-xs tracking-wide">Tanggal Pesanan</p>
                                    <p class="text-lg font-semibold text-(--textsub1)">{{ optional($topup->created_at)->format('d M Y H:i') ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="uppercase text-xs tracking-wide">Game</p>
                                    <p class="text-lg font-semibold text-(--textsub1)">{{ $topup->game?->nama_game ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="uppercase text-xs tracking-wide">Paket</p>
                                    <p class="text-lg font-semibold text-(--textsub1)">
                                        {{ number_format($topup->package?->amount ?? 0) }} {{ $topup->currency?->currency_name ?? '' }}
                                    </p>
                                </div>
                                <div>
                                    <p class="uppercase text-xs tracking-wide">Total Pembayaran</p>
                                    <p class="text-lg font-semibold text-primary">Rp {{ number_format($topup->price_idr, 0, ',', '.') }}</p>
                                </div>
                                <div>
                                    <p class="uppercase text-xs tracking-wide">Metode Pembayaran</p>
                                    <p class="text-lg font-semibold text-(--textsub1)">{{ $paymentConfig['label'] ?? strtoupper($topup->payment_method) }}</p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="rounded-2xl border border-white/20 bg-black/5 p-4">
                                    <h3 class="text-lg font-semibold text-(--textsub1) mb-2">Informasi Kontak</h3>
                                    <p class="text-sm text-(--textsub1)/80">Email: {{ $topup->contact_email ?? 'Tidak diisi' }}</p>
                                    <p class="text-sm text-(--textsub1)/80">WhatsApp: {{ $topup->contact_whatsapp ?? 'Tidak diisi' }}</p>
                                </div>
                                <div class="rounded-2xl border border-white/20 bg-black/5 p-4">
                                    <h3 class="text-lg font-semibold text-(--textsub1) mb-2">Detail Akun Game</h3>
                                    <ul class="space-y-1 text-sm text-(--textsub1)/80">
                                        @forelse ($topup->account_data ?? [] as $field => $value)
                                            <li class="flex justify-between gap-4">
                                                <span class="capitalize">{{ str_replace('_', ' ', $field) }}</span>
                                                <span class="font-semibold text-(--textsub1)">{{ $value }}</span>
                                            </li>
                                        @empty
                                            <li>Data akun tidak tersedia.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>

                            <div class="rounded-2xl border border-white/10 bg-black/5 p-4 text-xs text-(--textsub1)/70">
                                <p>Simpan ID transaksi ini untuk mempermudah pelacakan di masa mendatang. Jika membutuhkan bantuan, hubungi tim dukungan dengan menyertakan ID tersebut.</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endsection
</x-layouts.app>
