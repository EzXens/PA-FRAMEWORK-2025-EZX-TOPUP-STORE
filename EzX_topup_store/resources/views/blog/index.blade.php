<x-layouts.app>
    @section('carousel')

        <div class="relative w-full flex flex-col items-center justify-center py-8 overflow-hidden"
            style="background-image: var(--bg-gif); background-size: contain;">
            <div id="carousel3d" class="relative flex items-center justify-center w-full h-[340px] sm:h-[400px]"
                style="perspective:2000px; transform-style:preserve-3d;">
                @php
                    $carouselGames = [
                        'Genshin Impact' => 'https://play-lh.googleusercontent.com/SYpVTbN4Nd08YpPFobzs0XfIdRIEVKwPiy7JFBTQM2X08tbJsPDkcusEhcctwB-Fons6CLRVmF_PkNBgt0vk6A=w526-h296-rw',
                        'Wuthering Waves' => 'https://gaming-cdn.com/images/products/18945/orig/wuthering-waves-pc-game-steam-cover.jpg?v=1749478082',
                        'Mobile Legends' => 'https://kabarnusa.id/wp-content/uploads/2024/03/Mobile-Legends-Bang-Bang-MLBB-Mengulas-Game-Mobile-yang-Mendunia-kabarnusa.id_.webp',
                        'PUBG Mobile' => 'https://wallpapersok.com/images/high/cool-hd-pubg-game-logo-cover-zt3c2cr2j5irmxu5.jpg',
                        'Free Fire' => 'https://scontent-sin11-1.xx.fbcdn.net/v/t1.6435-9/55929432_2087779617996948_5529147812320641024_n.jpg?_nc_cat=105&ccb=1-7&_nc_sid=cc71e4&_nc_eui2=AeFgXUz7ukABeyieD7C51uFrs7zLuiwKsxWzvMu6LAqzFRQlXd75VVh7icAtrNTPjf7xg1drPcUCF9GqkYTLMxQh&_nc_ohc=t0JdBvwjRN8Q7kNvwHN-pTX&_nc_oc=Adk8Bu8BNs_ms5MoQDrmBVc1cCrh4aE4blvY1yiTTQLjoqwtuQMCpBHNlVOEqK8FztWQQGEefC0duyHrgzxNrxQS&_nc_zt=23&_nc_ht=scontent-sin11-1.xx&_nc_gid=Ys1_uQS_eaOxSU19qUEHFQ&oh=00_AfiX58r1aCKUhc7z_3_9WJl-LMJzGEpbrF0sKp1TDcauXw&oe=693A08A6',
                        'Roblox' => 'https://www.mithrie.com/blogs/roblox-unveiled-exploring-vibrant-world-infinite-play/roblox-cover-image.jpg',
                    ];

                    $carouselData = collect($carouselGames)
                        ->map(function ($imageUrl, $name) use ($games) {
                            $game = $games->firstWhere('nama_game', $name);
                            return [
                                'image' => $imageUrl,
                                'route' => $game ? route('games.show', $game) : null,
                            ];
                        })
                        ->values();
                @endphp

                @foreach ($carouselData as $index => $item)
                    <div class="absolute transition-all duration-700 ease-in-out transform" data-index="{{ $index }}">
                        @if ($item['route'])
                            <a href="{{ $item['route'] }}" class="block">
                                <img src="{{ $item['image'] }}"
                                    class="rounded-2xl shadow-2xl w-[700px] mx-auto object-cover" />
                            </a>
                        @else
                            <img src="{{ $item['image'] }}" class="rounded-2xl shadow-2xl w-[700px] mx-auto object-cover" />
                        @endif
                    </div>
                @endforeach

            </div>

            <!-- Tombol Navigasi -->
            <button id="prevBtn"
                class="btn btn-circle absolute left-6 top-1/2 -translate-y-1/2 z-20 bg-(--p) text-white border-none hover:bg-(--p-focus)">❮</button>
            <button id="nextBtn"
                class="btn btn-circle absolute right-6 top-1/2 -translate-y-1/2 z-20 bg-(--p) text-white border-none hover:bg-(--p-focus)">❯</button>

            <!-- Dots -->
            <div class="flex justify-center space-x-2 mt-6">
                @for ($i = 0; $i < 6; $i++)
                    <button class="dot w-3 h-3 rounded-full bg-gray-400" onclick="goTo3D({{ $i }})"></button>
                @endfor
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const slides3D = document.querySelectorAll('#carousel3d [data-index]');
                const dots3D = document.querySelectorAll('.dot');
                const nextBtn = document.getElementById('nextBtn');
                const prevBtn = document.getElementById('prevBtn');
                let current3D = 0;

                function render3D() {
                    slides3D.forEach((slide, i) => {
                        const diff = i - current3D;

                        // supaya looping ke kiri-kanan tetap rapat
                        const offset = (diff > slides3D.length / 2) ? diff - slides3D.length :
                            (diff < -slides3D.length / 2) ? diff + slides3D.length : diff;

                        slide.style.opacity = (i === current3D) ? 1 : 0.85;
                        slide.style.zIndex = (i === current3D) ? 20 : 10;
                        slide.style.transform = `translateX(${offset * 65}%)  scale(${i === current3D ? 1 : 0.85}) rotateY(${offset * -45}deg) `;
                        slide.style.filter = (i === current3D) ? 'none' : 'blur(1.5px) brightness(0.9)';
                    });

                    dots3D.forEach((dot, i) => {
                        dot.classList.toggle('bg-white', i === current3D);
                        dot.classList.toggle('bg-gray-400', i !== current3D);
                    });
                }

                function next3D() {
                    current3D = (current3D + 1) % slides3D.length;
                    render3D();
                }

                function prev3D() {
                    current3D = (current3D - 1 + slides3D.length) % slides3D.length;
                    render3D();
                }

                window.goTo3D = function (i) {
                    current3D = i;
                    render3D();
                }

                // Tombol event listener (supaya pasti aktif)
                nextBtn.addEventListener('click', next3D);
                prevBtn.addEventListener('click', prev3D);

                // Auto slide
                setInterval(next3D, 5000);

                render3D();
            });
        </script>
    @endsection

    @section('content')
        <div class="hero min-h-screen" style="background-image: var(--bg-hero); background-size: cover; ">
            <div class="hero-content flex-col lg:flex-row-reverse">
                <img src="images/heroL.png"
                    class="max-w-sm rounded-lg shadow-2xl" />
                <div>
                    <h1 class="text-5xl text-(--textsub1) font-bold">
                        Selamat Datang di EzX Store!
                    </h1>

                    <p class="py-6 text-(--textsub1)">
                        Tempat top up game tercepat, aman, dan terpercaya.  
                        Pilih game favoritmu, lakukan pembayaran, dan diamond akan langsung masuk ke akun kamu.  
                        Ayo mulai petualanganmu sekarang juga!
                    </p>

                    <button onclick="document.getElementById('list_all_games').scrollIntoView({ behavior: 'smooth' })"
                        class="btn bg-(--p) text-white font-bold">
                        Get Started
                    </button>

                </div>
            </div>
        </div>
        <div class="segitiga-bergerak">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>

    @endsection

    @section('list_all_games')
        <div class="container mx-auto py-10">
            <!-- Section Title -->
            <div class="relative w-full flex items-center justify-between px-6 py-3 mb-8 
        bg-(--p) backdrop-blur-md border border-white/20 
        rounded-full shadow-md overflow-hidden">

                <!-- Kiri: Ikon -->
                <div id="list_all_games"  class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="white" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v9m0 0l3.5-3.5M12 12L8.5 8.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl sm:text-2xl font-bold text-white drop-shadow">
                        🎮 List All Games
                    </h2>
                </div>

                <!-- Kanan: Garis dekorasi gradasi -->
                <div
                    class="absolute right-0 top-0 bottom-0 w-32 bg-gradient-to-l from-red-600/70 via-purple-700/50 to-transparent rounded-r-full">
                </div>
            </div>
            {{-- end section tiitllee --}}


            <!-- Wrapper Glass Panel -->
            <div class="backdrop-blur-md bg-white/10 border border-white/20 rounded-2xl shadow-lg p-6">
                <!-- Grid Game Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                    @forelse ($games as $game)
                        <a href="{{ route('games.show', $game) }}"
                            class="card bg-base-100/80 backdrop-blur-sm shadow-md hover:shadow-xl transition-transform transform hover:-translate-y-1 hover:scale-105 border border-white/10">
                            <figure class="px-6 pt-6">
                                <img src="{{ $game->gambar_url ?? 'https://placehold.co/320x200?text=Game' }}"
                                    alt="{{ $game->nama_game }}" class="rounded-xl object-cover w-full h-44" />
                            </figure>
                            <div class="card-body text-(--textsub1)">
                                <h2 class="card-title justify-between">
                                    <span>{{ $game->nama_game }}</span>
                                    <span class="badge badge-outline">{{ $game->currencies_count }} paket</span>
                                </h2>
                                <p class="text-sm opacity-80 leading-relaxed">{{ \Illuminate\Support\Str::limit($game->deskripsi, 120) ?: 'Belum ada deskripsi.' }}</p>
                            </div>
                        </a>
                    @empty
                        <p class="text-center text-(--textsub1) col-span-full">Belum ada game terdaftar. Silakan tambahkan melalui dashboard admin.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endsection


</x-layouts.app>