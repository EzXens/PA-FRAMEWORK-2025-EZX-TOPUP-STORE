<x-layouts.app>
    @section('content')
        @php
            $paymentCollection = collect($paymentMethods ?? []);
            $coinRate = $coinRate ?? config('coin.coin_to_idr_rate', 100);
            $coinBalance = $coinBalance ?? (auth()->check() ? auth()->user()->coin_balance : 0);
            $coinMethod = $paymentCollection->firstWhere('type', 'coin');
            $coinMethodKey = $coinMethod ? $paymentCollection->search($coinMethod) : null;
            if ($coinMethodKey !== null) {
                $paymentCollection = $paymentCollection->except($coinMethodKey);
            }
            $qrisMethod = $paymentCollection->firstWhere('type', 'qris');
            $qrisKey = $qrisMethod ? $paymentCollection->search($qrisMethod) : null;
            $bankMethods = $paymentCollection->filter(fn ($method) => ($method['type'] ?? '') === 'bank_transfer');
            $ewalletMethods = $paymentCollection->filter(fn ($method) => ($method['type'] ?? '') === 'ewallet');
            $otherMethods = $paymentCollection->reject(fn ($method) => in_array($method['type'] ?? '', ['qris', 'bank_transfer', 'ewallet']));
            $premiumActive = $premiumActive ?? false;
            $premiumDiscount = $premiumDiscount ?? 0;

            $defaultCurrencyId = old('currency', optional($game->currencies->first())->id_currency);
            $defaultPackageId = old('package');
            if (! $defaultPackageId && $defaultCurrencyId) {
                $defaultCurrency = $game->currencies->firstWhere('id_currency', $defaultCurrencyId);
                $defaultPackageId = optional(optional($defaultCurrency)->packages->first())->id_package;
            }
            $defaultPaymentMethod = old('payment_method');
        @endphp

        <section class="min-h-screen py-12" style="background-image: var(--bg-hero); background-size: cover;">
            <div class="mx-auto w-full max-w-6xl px-4 xl:px-6">
                <div class="flex flex-col gap-6 xl:flex-row xl:items-start">
                    <div
                        class="w-full shrink-0 overflow-hidden rounded-3xl border border-white/20 bg-white/10 backdrop-blur-lg shadow-lg xl:w-80">
                        <div class="h-56 w-full overflow-hidden">
                            <img src="{{ $game->gambar_url ?? 'https://placehold.co/640x360?text=Game' }}"
                                alt="{{ $game->nama_game }}" class="h-full w-full object-cover" />
                        </div>
                        <div class="p-6 space-y-4 text-(--textsub1)">
                            <div>
                                <h1 class="text-3xl font-semibold">{{ $game->nama_game }}</h1>
                                @if ($game->deskripsi)
                                    <p class="mt-2 text-sm text-(--textsub1)/80 leading-relaxed">{{ $game->deskripsi }}</p>
                                @endif
                            </div>
                            <div class="flex flex-wrap gap-2 text-xs uppercase tracking-wide">
                                <span class="badge badge-outline badge-primary badge-md">{{ $game->currencies->count() }} produk</span>
                                <span class="badge badge-outline badge-secondary badge-md">Top-up Instan</span>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('game-topups.store', $game) }}" id="gameTopupForm"
                        class="flex-1 space-y-6"
                        data-coin-rate="{{ $coinRate }}"
                        data-coin-balance="{{ $coinBalance }}"
                        @if ($coinMethodKey) data-coin-method="{{ $coinMethodKey }}" @endif>
                        @csrf

                        @if ($premiumActive && $premiumDiscount > 0)
                            <div class="alert alert-success shadow">
                                <span>Diskon premium {{ $premiumDiscount }}% telah diterapkan pada semua harga.</span>
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-error shadow">
                                <span>{{ $errors->first() }}</span>
                            </div>
                        @endif

                        <input type="hidden" name="game_id" value="{{ $game->id_game }}" />

                        <div class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur-lg shadow-lg p-6 text-(--textsub1)">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h2 class="text-2xl font-semibold">Pilih Produk</h2>
                                    <p class="text-sm text-(--textsub1)/80">Pilih mata uang game dan paket yang ingin dibeli.</p>
                                </div>
                                <span class="badge bg-(--p) text-white font-bold badge-lg">Step 1</span>
                            </div>

                            <div class="mt-6 space-y-6">
                                <div class="space-y-3">
                                    <h3 class="text-lg font-semibold">Mata Uang Game</h3>
                                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                        @forelse ($game->currencies as $currency)
                                            <label
                                                class="group relative block cursor-pointer rounded-2xl border border-white/20 bg-black/10 p-4 backdrop-blur transition hover:border-primary/80 hover:bg-primary/10">
                                                <input type="radio" name="currency" value="{{ $currency->id_currency }}"
                                                    class="peer sr-only" @checked($defaultCurrencyId == $currency->id_currency) {{ $loop->first ? 'required' : '' }} />
                                                <div class="flex items-center gap-4">
                                                    <div class="h-16 w-16 overflow-hidden rounded-xl border border-white/10">
                                                        <img src="{{ $currency->gambar_currency_url ?? 'https://placehold.co/128x128?text=Currency' }}"
                                                            alt="{{ $currency->currency_name }}"
                                                            class="h-full w-full object-cover" />
                                                    </div>
                                                    <div class="flex-1">
                                                        <p class="text-lg font-semibold text-(--textsub1) peer-checked:text-primary">
                                                            {{ $currency->currency_name }}</p>
                                                        <p class="text-xs text-(--textsub1)/70">{{ $currency->deskripsi ?? 'Paket top-up untuk mata uang ini tersedia.' }}</p>
                                                    </div>
                                                </div>
                                                <span
                                                    class="absolute right-4 top-4 hidden rounded-full border border-primary bg-primary/20 p-1 text-primary peer-checked:inline-flex">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M5 13l4 4L19 7" />
                                                    </svg>
                                                </span>
                                            </label>
                                        @empty
                                            <p class="col-span-full text-sm text-(--textsub1)/70">Produk belum tersedia untuk game ini.</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="space-y-3">
                                    <h3 class="text-lg font-semibold">Game Package</h3>
                                    <div id="packages-panel" class="space-y-4">
                                        @foreach ($game->currencies as $currency)
                                            @php
                                                $currencyImage = $currency->gambar_currency_url ?? 'https://placehold.co/128x128?text=Currency';
                                                $isActiveGroup = $defaultCurrencyId == $currency->id_currency;
                                                $shouldShowGroup = $defaultCurrencyId ? $isActiveGroup : $loop->first;
                                            @endphp
                                            <div @class([
                                                'package-group grid gap-4 sm:grid-cols-2 xl:grid-cols-3',
                                                'hidden' => ! $shouldShowGroup,
                                            ]) data-currency-id="{{ $currency->id_currency }}">
                                                @forelse ($currency->packages as $package)
                                                    @php
                                                        $isChecked = $defaultPackageId == $package->id_package;
                                                        if ($defaultCurrencyId === null && $loop->parent->first && $loop->first) {
                                                            $isChecked = true;
                                                        }
                                                    $priceValue = (float) $package->price;
                                                    $finalPriceValue = $priceValue;
                                                    $hasDiscount = false;
                                                    if ($premiumActive && $premiumDiscount > 0) {
                                                        $discountAmount = round($priceValue * ($premiumDiscount / 100));
                                                        $finalPriceValue = max(0, $priceValue - $discountAmount);
                                                        $hasDiscount = $finalPriceValue < $priceValue;
                                                    }
                                                    @endphp
                                                    <label
                                                        class="group relative block cursor-pointer rounded-2xl border border-white/20 bg-black/10  p-4 backdrop-blur transition hover:border-secondary/80 hover:bg-secondary/10">
                                                        <input type="radio" name="package" value="{{ $package->id_package }}"
                                                            class="peer sr-only" @checked($isChecked) {{ $loop->parent->first && $loop->first ? 'required' : '' }}
                                                        data-price-idr="{{ $finalPriceValue }}"
                                                        data-price-original="{{ $priceValue }}"
                                                        data-discount-percentage="{{ $hasDiscount ? $premiumDiscount : 0 }}"
                                                            data-amount="{{ $package->amount }}"
                                                            data-currency-name="{{ $currency->currency_name }}"
                                                            data-package-label="{{ number_format($package->amount) }} {{ $currency->currency_name }}" />
                                                        <div class="flex items-center gap-4">
                                                            <div class="h-16 w-16 overflow-hidden rounded-xl border border-white/10">
                                                                <img src="{{ $currencyImage }}" alt="{{ $currency->currency_name }}"
                                                                    class="h-full w-full object-cover" />
                                                            </div>
                                                            <div class="flex-1 text-left">
                                                            <p class="text-base font-semibold text-(--textsub1) peer-checked:text-secondary">
                                                                @if ($hasDiscount)
                                                                    <span class="line-through text-(--textsub1)/60">Rp {{ number_format($priceValue, 0, ',', '.') }}</span>
                                                                    <span class="ml-2 text-secondary">Rp {{ number_format($finalPriceValue, 0, ',', '.') }}</span>
                                                                @else
                                                                    Rp {{ number_format($priceValue, 0, ',', '.') }}
                                                                @endif
                                                            </p>
                                                                <p class="text-xs text-(--textsub1)/70">{{ $package->deskripsi ?? 'Paket top-up siap diproses.' }}</p>
                                                                <p class="text-xs text-(--textsub1)/60">Jumlah: {{ number_format($package->amount) }} {{ $currency->currency_name }}</p>
                                                            </div>
                                                        </div>
                                                        <span
                                                            class="absolute right-4 top-4 hidden rounded-full border border-secondary bg-secondary/20 p-1 text-secondary peer-checked:inline-flex">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                                                viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </span>
                                                    </label>
                                                @empty
                                                    <p class="col-span-full text-sm text-(--textsub1)/70">Belum ada paket untuk mata uang ini.</p>
                                                @endforelse
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur-lg shadow-lg p-6 text-(--textsub1)">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h2 class="text-2xl font-semibold">Metode Pembayaran</h2>
                                    <p class="text-sm text-(--textsub1)/80">Pilih metode pembayaran favorit Anda.</p>
                                </div>
                                <span class="badge bg-(--p) text-white font-bold badge-lg">Step 2</span>
                            </div>

                            <div class="mt-6 grid gap-4 lg:grid-cols-2" id="paymentMethodList">
                                @if ($coinMethod && auth()->check())
                                    <label
                                        class="rounded-2xl border border-white/20 bg-black/10 p-5 backdrop-blur transition hover:border-primary/80 hover:bg-primary/10 cursor-pointer flex items-start gap-4">
                                        <input type="radio" name="payment_method" value="{{ $coinMethodKey }}"
                                            class="radio radio-primary mt-1" data-method-type="coin" data-label="{{ $coinMethod['label'] ?? 'Saldo Koin Website' }}"
                                            @checked($defaultPaymentMethod === $coinMethodKey)
                                            {{ $defaultPaymentMethod ? '' : 'required' }} />
                                        <div class="flex-1">
                                            <p class="text-lg font-semibold">{{ $coinMethod['label'] ?? 'Saldo Koin Website' }}</p>
                                            <p class="text-sm text-(--textsub1)/70">Saldo Anda: <span id="coinBalanceValue">{{ number_format($coinBalance) }}</span> koin</p>
                                            <p class="text-xs text-(--textsub1)/60">Koin dibutuhkan: <span id="coinNeededValue">-</span> koin</p>
                                            <p id="coinWarning" class="mt-1 hidden text-xs text-warning">Saldo koin Anda belum mencukupi untuk paket ini.</p>
                                        </div>
                                    </label>
                                @elseif ($coinMethod)
                                    <div class="rounded-2xl border border-dashed border-white/20 bg-black/10 p-5 backdrop-blur text-(--textsub1)/70">
                                        <p class="text-sm font-semibold">{{ $coinMethod['label'] ?? 'Saldo Koin Website' }}</p>
                                        <p class="text-xs">Masuk ke akun Anda untuk menggunakan pembayaran koin.</p>
                                    </div>
                                @endif

                                @if ($qrisMethod && $qrisKey !== null)
                                    <label
                                        class="rounded-2xl border border-white/20 bg-black/10  p-5 backdrop-blur transition hover:border-primary/80 hover:bg-primary/10 cursor-pointer flex items-start gap-4">
                                        <input type="radio" name="payment_method" value="{{ $qrisKey }}"
                                            class="radio radio-primary mt-1" data-method-type="qris" data-label="{{ $qrisMethod['label'] ?? 'QRIS' }}"
                                            @checked($defaultPaymentMethod === $qrisKey || (! $defaultPaymentMethod && ! $coinMethod))
                                            {{ $coinMethod ? '' : 'required' }} />
                                        <div>
                                            <p class="text-lg font-semibold">{{ $qrisMethod['label'] ?? 'QRIS' }}</p>
                                            <p class="text-sm text-(--textsub1)/70">Scan QRIS semua bank & e-wallet untuk pembayaran cepat.</p>
                                        </div>
                                    </label>
                                @endif

                                @foreach ($bankMethods as $key => $method)
                                    <label
                                        class="rounded-2xl border border-white/20 bg-black/10  p-5 backdrop-blur transition hover:border-primary/80 hover:bg-primary/10 cursor-pointer flex items-start gap-4">
                                        <input type="radio" name="payment_method" value="{{ $key }}"
                                            class="radio radio-primary mt-1" data-method-type="bank_transfer" data-label="{{ $method['label'] ?? strtoupper($key) }}"
                                            @checked($defaultPaymentMethod === $key)
                                            {{ ! $coinMethod && ! $qrisMethod && $loop->first && ! $defaultPaymentMethod ? 'required' : '' }} />
                                        <div>
                                            <p class="text-lg font-semibold">{{ $method['label'] ?? strtoupper($key) }}</p>
                                            <p class="text-sm text-(--textsub1)/70">Nama: {{ $method['account_name'] ?? '-' }}</p>
                                            <p class="text-sm font-mono text-(--textsub1)/80">{{ $method['account_number'] ?? '-' }}</p>
                                        </div>
                                    </label>
                                @endforeach

                                @foreach ($otherMethods as $key => $method)
                                    <label
                                        class="rounded-2xl border border-white/20 bg-black/10  p-5 backdrop-blur transition hover:border-primary/80 hover:bg-primary/10 cursor-pointer flex items-start gap-4">
                                        <input type="radio" name="payment_method" value="{{ $key }}"
                                            class="radio radio-primary mt-1" data-method-type="{{ $method['type'] ?? 'other' }}" data-label="{{ $method['label'] ?? strtoupper($key) }}"
                                            @checked($defaultPaymentMethod === $key)
                                            {{ ! $coinMethod && ! $qrisMethod && ! $bankMethods->count() && $loop->first && ! $defaultPaymentMethod ? 'required' : '' }} />
                                        <div>
                                            <p class="text-lg font-semibold">{{ $method['label'] ?? strtoupper($key) }}</p>
                                            <p class="text-sm text-(--textsub1)/70">Metode pembayaran alternatif.</p>
                                        </div>
                                    </label>
                                @endforeach

                                @if ($ewalletMethods->isNotEmpty())
                                    <div class="rounded-2xl border border-white/20 bg-black/10  p-4 backdrop-blur">
                                        <div class="collapse collapse-arrow bg-transparent">
                                            <input type="checkbox" />
                                            <div class="collapse-title text-lg font-semibold">E-Wallet</div>
                                            <div class="collapse-content space-y-3 text-sm text-(--textsub1)/80">
                                                @foreach ($ewalletMethods as $key => $method)
                                                    <label class="flex items-center gap-3 rounded-xl border border-white/10 bg-white/5 px-3 py-2">
                                                        <input type="radio" name="payment_method" value="{{ $key }}"
                                                            class="radio radio-secondary" data-method-type="ewallet" data-label="{{ $method['label'] ?? strtoupper($key) }}"
                                                            @checked($defaultPaymentMethod === $key)
                                                            {{ ! $coinMethod && ! $qrisMethod && ! $bankMethods->count() && $loop->first && ! $defaultPaymentMethod ? 'required' : '' }} />
                                                        <span>{{ $method['label'] ?? strtoupper($key) }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur-lg shadow-lg p-6 text-(--textsub1)">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h2 class="text-2xl font-semibold">Informasi Akun</h2>
                                    <p class="text-sm text-(--textsub1)/80">Isi data sesuai kebutuhan game.</p>
                                </div>
                                <span class="badge bg-(--p) text-white font-bold badge-lg">Step 3</span>
                            </div>

                            <div class="mt-6 space-y-4">
                                <div>
                                    <p class="text-lg font-semibold">{{ $accountFieldDefinition['title'] ?? 'Detail Akun' }}</p>
                                    @if (!empty($accountFieldDefinition['description']))
                                        <p class="text-sm text-(--textsub1)/80">{{ $accountFieldDefinition['description'] }}</p>
                                    @endif
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    @foreach ($accountFieldDefinition['fields'] ?? [] as $field)
                                        @php
                                            $fieldName = $field['name'];
                                            $fieldValue = old("account.$fieldName");
                                            $fieldId = 'account-' . $fieldName;
                                        @endphp
                                        <div class="flex flex-col gap-2">
                                            <label class="text-sm font-semibold" for="{{ $fieldId }}">
                                                {{ $field['label'] }}
                                            </label>
                                            @if (($field['type'] ?? 'text') === 'select')
                                                <select id="{{ $fieldId }}" name="account[{{ $fieldName }}]"
                                                    class="select select-bordered bg-black/10  text-(--textsub1)" required data-account-field>
                                                    <option value="" disabled {{ $fieldValue ? '' : 'selected' }}>{{ $field['placeholder'] ?? 'Pilih opsi' }}</option>
                                                    @foreach ($field['options'] ?? [] as $option)
                                                        <option value="{{ $option }}" @selected($fieldValue === $option)>{{ $option }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <input id="{{ $fieldId }}"
                                                    name="account[{{ $fieldName }}]"
                                                    type="{{ $field['type'] ?? 'text' }}"
                                                    value="{{ $fieldValue }}"
                                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                                    class="input input-bordered bg-black/10 text-(--textsub1) placeholder:text-(--textsub1)/40" required data-account-field />
                                            @endif
                                            @if (!empty($field['helper']))
                                                <p class="text-xs text-(--textsub1)/70">{{ $field['helper'] }}</p>
                                            @endif
                                            @error("account.$fieldName")
                                                <p class="text-xs text-error">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur-lg shadow-lg p-6 text-(--textsub1)">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h2 class="text-2xl font-semibold">Informasi Kontak</h2>
                                    <p class="text-sm text-(--textsub1)/80">Opsional, untuk mengonfirmasi pesanan Anda.</p>
                                </div>
                                <span class="badge bg-(--p) text-white font-bold badge-lg">Step 4</span>
                            </div>

                            <div class="mt-6 grid gap-4 md:grid-cols-2">
                                <label class="form-control w-full">
                                    <span class="label-text text-(--textsub1)">Email (Opsional)</span>
                                    <input type="email" name="email"
                                        class="input input-bordered bg-black/10 text-(--textsub1) placeholder:text-(--textsub1)/40"
                                        placeholder="nama@email.com" value="{{ old('email') }}" />
                                </label>
                                <label class="form-control w-full">
                                    <span class="label-text text-(--textsub1)">No. WhatsApp (Opsional)</span>
                                    <input 
                                        type="tel" 
                                        name="whatsapp"
                                        class="input input-bordered bg-black/10 text-(--textsub1) placeholder:text-(--textsub1)/40"
                                        placeholder="08xxxxxxxxxx"
                                        value="{{ old('whatsapp') }}"
                                        minlength="10"
                                        maxlength="15"
                                        oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                    />

                                </label>
                            </div>
                        </div>

                        <div class="rounded-3xl border border-white/20 bg-white/10 backdrop-blur-lg shadow-lg p-6 text-(--textsub1)">
                            <div class="flex items-center justify-between flex-wrap gap-4">
                                <div>
                                    <h2 class="text-2xl font-semibold">Ringkasan Pesanan</h2>
                                    <p class="text-sm text-(--textsub1)/80">Periksa kembali detail sebelum melanjutkan.</p>
                                </div>
                                <span class="badge bg-(--p) text-white font-bold badge-lg">Step 5</span>
                            </div>

                            <div class="mt-6 grid gap-4 lg:grid-cols-2">
                                <dl class="space-y-3 text-sm text-(--textsub1)/80">
                                    <div class="flex justify-between gap-4">
                                        <dt>Game</dt>
                                        <dd class="font-semibold text-(--textsub1)">{{ $game->nama_game }}</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt>Paket</dt>
                                        <dd id="summaryPackageValue" class="font-semibold text-(--textsub1)">-</dd>
                                    </div>
                                    @if ($premiumActive && $premiumDiscount > 0)
                                        <div class="flex justify-between gap-4">
                                            <dt>Harga Awal</dt>
                                            <dd id="summaryOriginalPriceValue" class="font-semibold text-(--textsub1)/60 line-through">-</dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between gap-4">
                                        <dt>Total Harga</dt>
                                        <dd id="summaryPriceValue" class="font-semibold text-primary">-</dd>
                                    </div>
                                    @if ($premiumActive && $premiumDiscount > 0)
                                        <div class="flex justify-between gap-4">
                                            <dt>Diskon Premium</dt>
                                            <dd id="summaryDiscountValue" class="font-semibold text-success">-</dd>
                                        </div>
                                    @endif
                                    <div class="flex justify-between gap-4">
                                        <dt>Metode Pembayaran</dt>
                                        <dd id="summaryPaymentValue" class="font-semibold text-(--textsub1)">-</dd>
                                    </div>
                                    <div class="flex justify-between gap-4">
                                        <dt>Koin Digunakan</dt>
                                        <dd id="summaryCoinValue" class="font-semibold text-(--textsub1)">-</dd>
                                    </div>
                                </dl>

                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-(--textsub1)/70 space-y-2">
                                    <p>Harga akan dikonversi ke koin dengan rasio <strong>1 koin = Rp {{ number_format($coinRate, 0, ',', '.') }}</strong>.</p>
                                    @if (auth()->check())
                                        <p>Sisa saldo setelah transaksi akan ditampilkan pada dashboard Anda.</p>
                                    @else
                                        <p>Masuk untuk menyimpan riwayat transaksi dan menggunakan saldo koin.</p>
                                    @endif
                                    <p class="text-(--textsub1)/60">Dengan melanjutkan, Anda menyetujui syarat & ketentuan layanan EzX.</p>
                                </div>
                            </div>
                        </div>

                        <div class="mt-8 flex justify-end">
                            <button type="submit" id="submitTopupButton"
                                class="btn btn-primary btn-lg px-8 shadow-lg shadow-primary/40 btn-disabled" disabled>Beli Sekarang</button>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('gameTopupForm');
                if (!form) {
                    return;
                }

                const submitButton = document.getElementById('submitTopupButton');
                const currencyRadios = form.querySelectorAll('input[name="currency"]');
                const packageRadios = form.querySelectorAll('input[name="package"]');
                const paymentRadios = form.querySelectorAll('input[name="payment_method"]');
                const accountFields = form.querySelectorAll('[data-account-field]');
                const packageGroups = document.querySelectorAll('.package-group');

                const coinRate = parseInt(form.dataset.coinRate || '100', 10);
                const coinBalance = parseInt(form.dataset.coinBalance || '0', 10);
                const coinMethodValue = form.dataset.coinMethod || null;
                const coinWarning = document.getElementById('coinWarning');
                const coinNeededLabel = document.getElementById('coinNeededValue');
                const coinSummary = document.getElementById('summaryCoinValue');
                const summaryPackage = document.getElementById('summaryPackageValue');
                const summaryPrice = document.getElementById('summaryPriceValue');
                const summaryPayment = document.getElementById('summaryPaymentValue');
                const summaryOriginalPrice = document.getElementById('summaryOriginalPriceValue');
                const summaryDiscount = document.getElementById('summaryDiscountValue');

                const formatIdr = (value) => new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                }).format(value || 0);

                const formatNumber = (value) => new Intl.NumberFormat('id-ID').format(value || 0);

                const showPackages = (currencyId) => {
                    packageGroups.forEach((group) => {
                        if (group.dataset.currencyId === currencyId) {
                            group.classList.remove('hidden');
                            const firstInput = group.querySelector('input[name="package"]');
                            if (firstInput && !group.querySelector('input[name="package"]:checked')) {
                                firstInput.checked = true;
                            }
                        } else {
                            group.classList.add('hidden');
                            const checked = group.querySelector('input[name="package"]:checked');
                            if (checked) {
                                checked.checked = false;
                            }
                        }
                    });
                };

                const computeCoinsNeeded = (price) => {
                    if (!price || !coinRate) {
                        return null;
                    }
                    return Math.ceil(price / coinRate);
                };

                const updateSummary = () => {
                    const selectedPackage = form.querySelector('input[name="package"]:checked');
                    const selectedPayment = form.querySelector('input[name="payment_method"]:checked');

                    const price = selectedPackage ? parseFloat(selectedPackage.dataset.priceIdr || '0') : null;
                    const priceOriginal = selectedPackage ? parseFloat(selectedPackage.dataset.priceOriginal || selectedPackage.dataset.priceIdr || '0') : null;
                    const discountPercent = selectedPackage ? parseFloat(selectedPackage.dataset.discountPercentage || '0') : 0;
                    const discountAmount = priceOriginal && price !== null ? Math.max(0, priceOriginal - price) : 0;
                    const packageLabel = selectedPackage ? selectedPackage.dataset.packageLabel : null;
                    const coinsNeeded = computeCoinsNeeded(price);

                    if (coinNeededLabel) {
                        coinNeededLabel.textContent = coinsNeeded !== null ? formatNumber(coinsNeeded) : '-';
                    }
                    if (coinSummary) {
                        coinSummary.textContent = coinsNeeded !== null ? `${formatNumber(coinsNeeded)} koin` : '-';
                    }
                    if (summaryPackage) {
                        summaryPackage.textContent = packageLabel || '-';
                    }
                    if (summaryPrice) {
                        summaryPrice.textContent = price ? formatIdr(price) : '-';
                    }
                    if (summaryOriginalPrice) {
                        summaryOriginalPrice.textContent = priceOriginal && priceOriginal > 0 ? formatIdr(priceOriginal) : '-';
                    }
                    if (summaryDiscount) {
                        summaryDiscount.textContent = discountPercent > 0 && discountAmount > 0
                            ? `${discountPercent}% (Rp ${formatNumber(discountAmount)})`
                            : '-';
                    }
                    if (summaryPayment) {
                        summaryPayment.textContent = selectedPayment ? (selectedPayment.dataset.label || selectedPayment.value) : '-';
                    }

                    if (coinMethodValue) {
                        const coinRadio = form.querySelector(`input[name="payment_method"][value="${coinMethodValue}"]`);
                        if (coinRadio) {
                            const hasEnoughCoins = coinsNeeded === null || coinsNeeded <= coinBalance;
                            if (!hasEnoughCoins) {
                                coinRadio.disabled = true;
                                coinRadio.checked = false;
                            } else {
                                coinRadio.disabled = false;
                            }
                            if (coinWarning) {
                                coinWarning.classList.toggle('hidden', hasEnoughCoins);
                            }
                        }
                    }
                };

                const validateForm = () => {
                    updateSummary();

                    const currencySelected = !!form.querySelector('input[name="currency"]:checked');
                    const packageSelected = !!form.querySelector('input[name="package"]:checked');
                    const paymentSelected = !!form.querySelector('input[name="payment_method"]:checked');

                    let accountValid = true;
                    accountFields.forEach((field) => {
                        if (!field.value || field.value.trim() === '') {
                            accountValid = false;
                        }
                    });

                    if (currencySelected && packageSelected && paymentSelected && accountValid) {
                        submitButton.disabled = false;
                        submitButton.classList.remove('btn-disabled');
                    } else {
                        submitButton.disabled = true;
                        submitButton.classList.add('btn-disabled');
                    }
                };

                currencyRadios.forEach((radio) => {
                    radio.addEventListener('change', () => {
                        showPackages(radio.value);
                        validateForm();
                    });
                });

                packageRadios.forEach((radio) => {
                    radio.addEventListener('change', validateForm);
                });

                paymentRadios.forEach((radio) => {
                    radio.addEventListener('change', validateForm);
                });

                accountFields.forEach((field) => {
                    field.addEventListener('input', validateForm);
                    field.addEventListener('change', validateForm);
                });

                const initialCurrency = Array.from(currencyRadios).find((radio) => radio.checked);
                if (initialCurrency) {
                    showPackages(initialCurrency.value);
                } else if (currencyRadios.length) {
                    showPackages(currencyRadios[0].value);
                    currencyRadios[0].checked = true;
                }

                validateForm();
            });
        </script>
    @endsection
</x-layouts.app>
