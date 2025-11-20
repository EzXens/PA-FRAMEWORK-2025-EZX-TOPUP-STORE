@php
    $statusLabels = [
        'pending' => 'Menunggu Pembayaran',
        'approved' => 'Disetujui',
        'completed' => 'Selesai',
        'failed' => 'Gagal',
        'rejected' => 'Ditolak',
    ];
    $statusText = $statusLabels[$transaksi->status] ?? ucfirst($transaksi->status ?? '-');
    $createdAt = optional($transaksi->tanggal_transaksi ?? $transaksi->created_at)->format('d M Y H:i');
    $subtotal = collect($items)->sum('total');
    $taxRate = 0;
    $taxAmount = $subtotal * $taxRate;
    $grandTotal = $subtotal + $taxAmount;
@endphp

<style>
    #invoiceRoot {
        font-family: 'Inter', 'Segoe UI', sans-serif;
        width: 100%;
        max-width: 360px;
        margin: 0 auto;
        background: #ffffff;
    }

    #invoiceRoot .receipt-header {
        background: linear-gradient(135deg, rgba(37, 99, 235, 0.95), rgba(59, 130, 246, 0.8));
        color: #ffffff;
    }

    #invoiceRoot table {
        border-collapse: collapse;
        width: 100%;
    }

    #invoiceRoot table th,
    #invoiceRoot table td {
        font-size: 12px;
    }

    @media print {
        body {
            margin: 0;
            background: #ffffff;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }

        #invoiceRoot {
            box-shadow: none !important;
            border-radius: 0;
            margin: 0;
            max-width: 360px;
            transform: translateZ(0);
        backface-visibility: hidden;
        -webkit-font-smoothing: antialiased;

        }

        #invoiceRoot .receipt-header {
            background: #1f2937 !important;
            color: #ffffff !important;
        }

          #invoiceContainer * {
        box-sizing: border-box !important;
    }
    
    }
</style>

{{-- <div id="invoiceRoot" data-invoice-number="{{ $invoiceNumber }}" class="mx-auto rounded-3xl bg-white p-0 text-gray-800 shadow-2xl">
    <div class="receipt-header rounded-t-3xl p-6 text-white">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="rounded-full bg-white/20 p-2">
                    <img src="{{ asset('images/logo_ezx.png') }}" alt="EzX Store" class="h-12 w-12" />
                </div>
                <div>
                    <p class="text-sm uppercase tracking-wide text-white/70">Struk Pembelian</p>
                    <h1 class="text-2xl font-semibold">EzX Store</h1>
                </div>
            </div>
            <div class="text-right">
                <p class="text-xs text-white/70">Nomor Struk</p>
                <p class="text-lg font-semibold">{{ $invoiceNumber }}</p>
            </div>
        </div>
    </div>

    <div class="px-6 pt-6">
        <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-5 text-sm md:flex-row md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Pelanggan</p>
                <p class="mt-1 text-base font-semibold text-gray-900">{{ $billingName }}</p>
                @if ($billingEmail)
                    <p class="text-gray-600">{{ $billingEmail }}</p>
                @endif
                @if ($billingPhone)
                    <p class="text-gray-600">{{ $billingPhone }}</p>
                @endif
            </div>
            <div class="text-left md:text-right">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Tanggal</p>
                <p class="text-base font-semibold text-gray-900">{{ $createdAt }}</p>
                <p class="mt-3 text-xs font-semibold uppercase tracking-wide text-gray-500">Metode Pembayaran</p>
                <p class="text-base text-gray-900">{{ $paymentMethod }}</p>
                <div class="mt-3 inline-flex items-center gap-2 rounded-full border border-emerald-500 bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-emerald-600">
                    <span class="h-2 w-2 rounded-full bg-emerald-500"></span>{{ $statusText }}
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pt-6">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-100 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Deskripsi</th>
                        <th class="px-5 py-3 text-center">Qty</th>
                        <th class="px-5 py-3 text-right">Harga</th>
                        <th class="px-5 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $item)
                        <tr class="text-gray-700">
                            <td class="px-5 py-4">
                                <p class="font-medium text-gray-900">{{ $item['description'] }}</p>
                            </td>
                            <td class="px-5 py-4 text-center">{{ $item['quantity'] }}</td>
                            <td class="px-5 py-4 text-right">Rp {{ number_format($item['unit_price'], 0, ',', '.') }}</td>
                            <td class="px-5 py-4 text-right font-semibold text-gray-900">Rp {{ number_format($item['total'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="px-6 pt-6">
        <div class="flex flex-col gap-4 rounded-2xl bg-gray-50 p-5 text-sm md:flex-row md:items-start md:justify-between">
            <div class="w-full md:w-2/3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Catatan</p>
                <p class="mt-2 text-sm text-gray-600">Simpan struk ini sebagai bukti transaksi resmi. Hubungi dukungan EzX Store bila terdapat masalah pada pesanan Anda.</p>
            </div>
            <div class="w-full rounded-2xl border border-gray-100 bg-white p-4 text-sm md:w-64">
                <div class="flex items-center justify-between text-gray-600">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="mt-2 flex items-center justify-between text-gray-500">
                    <span>Pajak (0%)</span>
                    <span>Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
                </div>
                <div class="mt-3 border-t border-dashed border-gray-200 pt-3 text-base font-semibold text-gray-900">
                    <div class="flex items-center justify-between">
                        <span>Total</span>
                        <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 pt-4">
        <div class="overflow-hidden rounded-2xl border border-gray-100">
            <img src="{{ asset('images/barcode.png') }}" alt="Barcode" class="h-24 w-full object-cover" />
        </div>
        <p class="mt-3 text-center text-xs uppercase tracking-[0.3em] text-gray-400">Terima kasih telah bertransaksi di EzX Store</p>
    </div>

    <div class="rounded-b-3xl bg-gray-900 p-4 text-center text-xs text-gray-300">
        EzX Store • Jl. Esports No. 99, Jakarta • +62 812-0000-1234
    </div>
</div> --}}

<div id="invoiceRoot" data-invoice-number="{{ $invoiceNumber }}" 
     class="mx-auto bg-white p-6 text-gray-900 shadow rounded-xl border border-gray-200"
     style="max-width: 320px;">

    <!-- LOGO -->
    <div class="flex justify-center mb-3 bg-red-500/70 rounded-2xl backdrop-blur-md">
        <img src="{{ asset('images/logo_ezx.png') }}" alt="EzX Logo"
             style="height: 55px; object-fit: contain;">
    </div>

    <!-- HEADER -->
    <div class="text-center border-b pb-3">
        <p class="text-xs tracking-widest">******************************</p>
        <h2 class="text-sm font-bold mt-1">RECEIPT</h2>
        <p class="text-xs tracking-widest mt-1">******************************</p>

        <h1 class="text-base font-bold mt-3">EZX STORE</h1>

        <!-- STORE INFO -->
        <div class="text-left text-xs mt-4 leading-4">
            <p><b>Address:</b> Jl. Esports No. 99</p>
            <p><b>Date:</b> {{ $createdAt }}</p>
            <p><b>Manager:</b> Admin EzX</p>
        </div>
    </div>

    <!-- CUSTOMER INFO -->
    <div class="mt-3 text-xs border-b pb-3">
        <p><b>Customer:</b> {{ $transaksi->user->username ?? '-' }}</p>
        <p><b>Email:</b> {{ $transaksi->user->email ?? '-' }}</p>
        <p><b>Payment:</b> {{ $paymentMethod ?? 'Unknown' }}</p>
        <p><b>Status:</b> {{ $statusText }}</p>
    </div>

    <!-- ITEMS -->
    <div class="mt-4">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b">
                    <th class="py-2 text-left">Description</th>
                    <th class="py-2 text-right">Price</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($items as $item)
                <tr class="border-b">
                    <td class="py-2">
                        {{ $item['description'] }} × {{ $item['quantity'] }}
                    </td>
                    <td class="py-2 text-right">
                        Rp {{ number_format($item['total'], 0, ',', '.') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- TOTAL -->
    <div class="text-xs mt-4">
        {{-- <div class="flex justify-between">
            <span>Tax</span>
            <span>Rp {{ number_format($taxAmount, 0, ',', '.') }}</span>
        </div> --}}

        <div class="flex justify-between font-bold text-sm border-t mt-2 pt-2">
            <span>TOTAL</span>
            <span>Rp {{ number_format($grandTotal, 0, ',', '.') }}</span>
        </div>
    </div>

    <!-- THANK YOU -->
    <p class="text-center text-xs mt-4 font-semibold">
        THANK YOU
    </p>

    <!-- BARCODE -->
    <div class="mt-3 border-t pt-3">
        <img src="{{ asset('images/barcode.png') }}" 
             class="w-full"
             style="height: 90px; object-fit: contain;"
             alt="barcode" />

        <p class="text-center mt-1 text-xs tracking-widest">
            {{ $invoiceNumber }}
        </p>
    </div>
</div>


@if (request()->query('mode') === 'print')
<style>
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: #fff !important;
    }

    #invoiceRoot {
        box-shadow: none !important;
        border-radius: 0 !important;
        border: none !important;
        width: 100% !important;
        max-width: 320px !important;
        margin: 0 auto !important;
    }

    /* Hilangkan semua elemen di luar invoiceRoot */
    body > *:not(#invoiceRoot) {
        display: none !important;
    }
</style>

<script>
window.addEventListener('load', () => {
    setTimeout(() => {
        window.print();
    }, 200);
});
</script>
@endif


