@php
    $steps = [
        [
            'icon' => 'nfc',
            'title' => 'Tempelkan RFID pada barang',
            'description' => 'Pasang RFID sticker pada setiap perlengkapan sekolah yang ingin dipantau.',
        ],
        [
            'icon' => 'package-plus',
            'title' => 'Masukkan barang ke tas',
            'description' => 'Susun seluruh perlengkapan ke dalam tas seperti biasa, tanpa langkah tambahan.',
        ],
        [
            'icon' => 'scan-line',
            'title' => 'Dekatkan tas ke Scanner Box',
            'description' => 'Letakkan tas di dekat Scanner Box untuk memulai pemindaian otomatis.',
        ],
        [
            'icon' => 'cpu',
            'title' => 'AI memindai seluruh RFID',
            'description' => 'AI membaca semua RFID dan mencocokkannya dengan daftar barang hari ini.',
        ],
        [
            'icon' => 'bell',
            'title' => 'Pengguna menerima notifikasi',
            'description' => 'Kamu langsung tahu apakah tas sudah lengkap atau ada barang yang tertinggal.',
        ],
    ];
    $lastIndex = count($steps) - 1;
@endphp

<section id="cara-kerja" class="scroll-mt-20 bg-card">
    <div class="mx-auto max-w-[1280px] px-6 py-24">
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-semibold uppercase tracking-wide text-primary">
                Cara Kerja
            </span>
            <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                Lima langkah sederhana menuju tas yang selalu lengkap
            </h2>
        </div>

        <ol class="mx-auto mt-16 max-w-2xl">
            @foreach ($steps as $index => $step)
                <li class="relative flex gap-5 pb-10 last:pb-0">
                    @if ($index !== $lastIndex)
                        <span
                            class="absolute left-6 top-14 h-[calc(100%-3.5rem)] w-px bg-border"
                            aria-hidden="true"
                        ></span>
                    @endif

                    <div class="relative z-10 flex flex-col items-center">
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-primary text-primary-foreground shadow-sm">
                            <x-dynamic-component :component="'icon.' . $step['icon']" class="h-5 w-5" :stroke-width="1.75" />
                        </div>
                        <span class="mt-2 flex h-6 w-6 items-center justify-center rounded-full border border-border bg-background text-xs font-bold text-primary">
                            {{ $index + 1 }}
                        </span>
                    </div>

                    <div class="pt-1.5">
                        <h3 class="text-lg font-semibold text-foreground">{{ $step['title'] }}</h3>
                        <p class="mt-1.5 text-pretty text-sm leading-relaxed text-muted-foreground">
                            {{ $step['description'] }}
                        </p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
