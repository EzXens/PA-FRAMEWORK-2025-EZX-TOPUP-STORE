<x-layouts.app>
    @section('content')
        <section class="min-h-screen py-12" style="background-image: var(--bg-hero); background-size: cover;">
            <div class="mx-auto w-full max-w-5xl px-4 lg:px-6">
                @if (session('status'))
                    <div class="alert alert-success shadow mb-6">
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($purchase->status === 'rejected' && $purchase->rejection_reason)
                    <div class="alert alert-error shadow mb-6">
                        <span>{{ $purchase->rejection_reason }}</span>
                    </div>
                @endif

                @php
                    $statusLabelMap = [
                        'pending' => 'Menunggu Bukti Pembayaran',
                        'waiting_verification' => 'Menunggu Verifikasi Admin',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ];
                    $statusClassMap = [
                        'pending' => 'badge-warning text-black',
                        'waiting_verification' => 'badge-info text-black',
                        'approved' => 'badge-success text-(--textsub1)',
                        'rejected' => 'badge-error text-(--textsub1)',
                    ];
                    $statusLabel = $statusLabelMap[$purchase->status] ?? ucwords(str_replace('_', ' ', $purchase->status));
                    $statusClass = $statusClassMap[$purchase->status] ?? 'badge-ghost text-(--textsub1)';

                    $adminPhoneRaw = config('services.whatsapp_admin.number', '085821968930');
                    $adminPhoneDigits = preg_replace('/\D+/', '', $adminPhoneRaw ?? '');
                    if ($adminPhoneDigits === '') {
                        $adminPhoneDigits = '085821968930';
                    }
                    if (str_starts_with($adminPhoneDigits, '0')) {
                        $adminPhoneInternational = '62' . substr($adminPhoneDigits, 1);
                    } elseif (str_starts_with($adminPhoneDigits, '62')) {
                        $adminPhoneInternational = $adminPhoneDigits;
                    } else {
                        $adminPhoneInternational = '62' . $adminPhoneDigits;
                    }

                    $paymentTimeRaw = $purchase->payment_meta['proof_uploaded_at'] ?? null;
                    $paymentTimeObject = $paymentTimeRaw
                        ? \Illuminate\Support\Carbon::parse($paymentTimeRaw)
                        : ($purchase->updated_at ?? $purchase->created_at ?? now());
                    if (! $paymentTimeObject instanceof \Illuminate\Support\Carbon) {
                        $paymentTimeObject = \Illuminate\Support\Carbon::parse($paymentTimeObject);
                    }
                    $paymentTimeFormatted = $paymentTimeObject->timezone(config('app.timezone'))->format('d/m/Y, H.i.s');

                    $paymentLabel = $paymentConfig['label'] ?? strtoupper($purchase->payment_method);

                    $whatsappMessageLines = array_filter([
                        'Hello, I want to confirm my payment:',
                        'Transaction Number: ' . ($purchase->transaction_code ?? '-'),
                        'Coin Package: ' . number_format($purchase->coin_amount ?? 0) . ' koin',
                        'Total Price: Rp' . number_format($purchase->price_idr ?? 0, 0, ',', '.'),
                        'Payment Method: ' . $paymentLabel,
                        'Payment Time: ' . $paymentTimeFormatted,
                        'Please confirm my payment. Thank you.',
                    ]);
                    $whatsappMessage = implode("\n", $whatsappMessageLines);
                    $whatsappUrl = 'https://wa.me/' . $adminPhoneInternational . '?text=' . rawurlencode($whatsappMessage);

                    $paymentProofUploaded = filled($purchase->payment_meta['proof_uploaded_at'] ?? null) || filled($purchase->payment_proof_url);
                    $showWhatsappButton = (($paymentConfig['type'] ?? null) === 'coin') || $paymentProofUploaded;
                @endphp

                <div class="grid gap-6 lg:grid-cols-[2fr_1fr]">
                    <div class="space-y-6">
                        <div class="rounded-3xl border border-white/20 bg-white/10 p-6 backdrop-blur-lg shadow-lg text-(--textsub1)">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div>
                                    <h1 class="text-3xl font-semibold">Konfirmasi Pembayaran Koin</h1>
                                    <p class="text-sm text-(--textsub1)/80">Silakan selesaikan pembayaran agar admin dapat memproses penambahan koin.</p>
                                </div>
                                <span class="badge badge-lg {{ $statusClass }}">{{ $statusLabel }}</span>
                            </div>

                            <dl class="mt-6 grid gap-4 sm:grid-cols-2 text-sm text-(--textsub1)/80">
                                <div>
                                    <dt class="uppercase text-xs tracking-wide">ID Transaksi</dt>
                                    <dd class="text-lg font-extrabold text-(--p2)"></dd>
                                  <span id="transactionCodeValue">{{ $purchase->transaction_code }}</span>
                                        <button type="button" class="btn btn-ghost btn-xs border border-white/20 text-(--textsub1)" data-copy-transaction="{{ $purchase->transaction_code }}" aria-label="Salin ID Transaksi">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                                            </svg>
                                        </button>
                                </div>
                                <div>
                                    <dt class="uppercase text-xs tracking-wide">Waktu Permintaan</dt>
                                    <dd class="text-lg font-semibold text-(--textsub1)">{{ optional($purchase->created_at)->format('d M Y H:i') }}</dd>
                                </div>
                                <div>
                                    <dt class="uppercase text-xs tracking-wide">Jenis</dt>
                                    <dd class="text-lg font-semibold text-(--textsub1)">Top Up Koin</dd>
                                </div>
                                <div>
                                    <dt class="uppercase text-xs tracking-wide">Jumlah Pembayaran</dt>
                                    <dd class="text-lg font-semibold text-(--p2)">Rp {{ number_format($purchase->price_idr, 0, ',', '.') }}</dd>
                                </div>
                                <div>
                                    <dt class="uppercase text-xs tracking-wide">Jumlah Koin</dt>
                                    <dd class="text-lg font-semibold text-(--textsub1)">{{ number_format($purchase->coin_amount) }} koin</dd>
                                </div>
                                <div>
                                    <dt class="uppercase text-xs tracking-wide">Metode Pembayaran</dt>
                                    <dd class="text-lg font-semibold text-(--textsub1)">{{ $paymentConfig['label'] ?? strtoupper($purchase->payment_method) }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 p-6 backdrop-blur-lg shadow-lg text-(--textsub1) space-y-6">
                            <h2 class="text-2xl font-semibold">Instruksi Pembayaran</h2>

                            @if (($paymentConfig['type'] ?? '') === 'qris')
                                <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur">
                                    <p class="text-sm text-(--textsub1)/80">Scan QR di bawah ini menggunakan aplikasi bank atau e-wallet Anda.</p>
                                    <img src="{{ asset($paymentConfig['qr_image_url']) ?? 'https://placehold.co/320x320?text=QRIS' }}" alt="QRIS" class="mx-auto mt-4 w-56 rounded-xl border border-white/10 shadow" />
                                </div>
                            @else
                                <div class="rounded-2xl border border-white/20 bg-white/10 p-4 backdrop-blur">
                                    <p class="text-sm text-(--textsub1)/80">Lakukan transfer ke rekening atau e-wallet berikut:</p>
                                    <div class="mt-3 space-y-1 text-sm">
                                        <p>Nama Penerima: <span class="font-semibold text-(--textsub1)">{{ $paymentConfig['account_name'] ?? 'EzX Store' }}</span></p>
                                        <p>Nomor Tujuan: <span class="font-mono text-(--textsub1) text-base">{{ $paymentConfig['account_number'] ?? '-' }}</span></p>
                                    </div>
                                </div>
                            @endif

                            @if (!empty($paymentConfig['instructions']))
                                <ol class="list-decimal space-y-2 pl-6 text-sm text-(--textsub1)/80">
                                    @foreach ($paymentConfig['instructions'] as $step)
                                        <li>{{ $step }}</li>
                                    @endforeach
                                </ol>
                            @endif

                            <div class="alert alert-info text-xs text-black">
                                <span>Setelah melakukan pembayaran, tunggu admin menyetujui transaksi. Koin otomatis masuk setelah status disetujui.</span>
                            </div>

                            @if ($showWhatsappButton)
                            
                                <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener" class="btn btn-success w-full">
                                   <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 432 432"><path fill="#000000" d="M364.5 65Q427 127 427 214.5T364.5 364T214 426q-54 0-101-26L0 429l30-109Q2 271 2 214q0-87 62-149T214 3t150.5 62zM214 390q73 0 125-51.5T391 214T339 89.5T214 38T89.5 89.5T38 214q0 51 27 94l4 6l-18 65l67-17l6 3q42 25 90 25zm97-132q9 5 10 7q4 6-3 25q-3 8-15 15.5t-21 9.5q-18 2-33-2q-17-6-30-11q-8-4-15.5-8.5t-14.5-9t-13-9.5t-11.5-10t-10.5-10.5t-8.5-9.5t-7-8.5t-5.5-7t-3.5-5L128 222q-22-29-22-55q0-24 19-44q6-7 14-7q6 0 10 1q8 0 12 9q2 3 6 13l7 17.5l3 8.5q3 5 1 9q-3 7-5 9l-3 3l-3 3.5l-2 2.5q-6 6-3 11q13 22 30 37q13 11 43 26q7 3 11-1q12-15 17-21q4-6 12-3q6 3 36 17z"/></svg>
                                    </span>
                                    Konfirmasi melalui Admin di WhatsApp
                                </a>
                            @endif
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 p-6 backdrop-blur-lg shadow-lg text-(--textsub1) space-y-5">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h3 class="text-2xl font-semibold">Unggah Bukti Pembayaran</h3>
                                    <p class="text-sm text-(--textsub1)/70">Unggah bukti transfer untuk mempercepat verifikasi admin.</p>
                                </div>
                                @if ($purchase->payment_proof_url)
                                    <a href="{{ $purchase->payment_proof_url }}" target="_blank" class="btn btn-ghost btn-sm border border-white/30 text-(--textsub1)">Lihat Bukti</a>
                                @endif
                            </div>

                            @if ($purchase->payment_meta['proof_uploaded_at'] ?? false)
                                <p class="text-xs text-(--textsub1)/60">Terakhir diunggah: {{ \Illuminate\Support\Carbon::parse($purchase->payment_meta['proof_uploaded_at'])->timezone(config('app.timezone'))->format('d M Y H:i') }}</p>
                            @endif

                            @if (! in_array($purchase->status, ['approved', 'rejected'], true))
                                <form method="POST" action="{{ route('coins.purchases.payment-proof', $purchase) }}" enctype="multipart/form-data" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="label text-sm font-medium">Bukti Pembayaran</label>
                                        <input type="file" name="payment_proof" accept="image/*" class="file-input file-input-bordered w-full" required>
                                        @error('payment_proof')
                                            <p class="mt-2 text-xs text-red-300">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <button type="submit" class="btn btn-primary">Unggah Bukti</button>
                                </form>
                            @else
                                <p class="text-sm text-(--textsub1)/70">Transaksi telah diproses, tidak dapat mengunggah bukti baru.</p>
                            @endif
                        </div>
                    </div>

                    <aside class="space-y-4">
                        <div class="rounded-3xl border border-white/20 bg-white/10 p-5 backdrop-blur-lg shadow-lg text-(--textsub1) space-y-3">
                            <h3 class="text-xl font-semibold">Detail Pemesan</h3>
                            <p class="text-sm text-(--textsub1)/80">{{ $purchase->user->nama_lengkap ?? $purchase->user->username }}</p>
                            <p class="text-xs text-(--textsub1)/60">Email: {{ $purchase->user->email }}</p>
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 p-5 backdrop-blur-lg shadow-lg text-(--textsub1) space-y-3">
                            <h3 class="text-xl font-semibold">Status Proses</h3>
                            <ul class="space-y-2 text-sm text-(--textsub1)/80">
                                <li>• Permintaan dibuat: {{ optional($purchase->created_at)->format('d M Y H:i') }}</li>
                                <li>• Menunggu pembayaran dari pelanggan.</li>
                                <li>• Admin akan memverifikasi bukti pembayaran.</li>
                                <li>• Koin otomatis ditambahkan setelah disetujui.</li>
                            </ul>
                        </div>

                        {{-- <a href="{{ route('user.dashboard', ['tab' => 'transactions']) }}" class="btn btn-outline btn-sm w-full">Lihat Riwayat Transaksi</a> --}}
                    </aside>
                </div>
            </div>
        </section>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const copyButton = document.querySelector('[data-copy-transaction]');
                if (!copyButton) {
                    return;
                }

                const defaultClasses = copyButton.className;

                copyButton.addEventListener('click', async () => {
                    const code = copyButton.dataset.copyTransaction;
                    if (!code || !navigator.clipboard) {
                        return;
                    }

                    try {
                        await navigator.clipboard.writeText(code);
                        copyButton.className = 'btn btn-success btn-xs text-(--textsub1)';
                        setTimeout(() => {
                            copyButton.className = defaultClasses;
                        }, 2000);
                    } catch (error) {
                        console.error(error);
                    }
                });
            });
        </script>
    @endsection
</x-layouts.app>
