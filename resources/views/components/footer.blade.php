@php
    $columns = [
        [
            'title' => 'Produk',
            'links' => ['Fitur', 'Cara Kerja', 'Dashboard', 'Teknologi'],
        ],
        [
            'title' => 'Perusahaan',
            'links' => ['Tentang Kami', 'Karier', 'Blog', 'Kontak'],
        ],
        [
            'title' => 'Bantuan',
            'links' => ['Pusat Bantuan', 'FAQ', 'Panduan', 'Status Sistem'],
        ],
    ];
@endphp

<footer class="border-t border-border bg-card">
    <div class="mx-auto max-w-[1280px] px-6 py-16">
        {{-- CTA --}}
        <div class="flex flex-col items-center gap-6 rounded-[24px] bg-primary px-8 py-12 text-center">
            <h2 class="text-balance text-2xl font-bold tracking-tight text-primary-foreground sm:text-3xl">
                Siap memastikan tidak ada barang yang tertinggal?
            </h2>
            <p class="max-w-xl text-pretty leading-relaxed text-primary-foreground/80">
                Mulai gunakan InCase hari ini dan biarkan RFID, IoT, dan AI menjaga kelengkapan tasmu.
            </p>
            <x-ui.button size="lg" class="rounded-full bg-secondary px-7 font-semibold text-secondary-foreground hover:bg-secondary/90">
                Mulai Sekarang
                <x-icon.arrow-right class="h-4 w-4" />
            </x-ui.button>
        </div>

        {{-- Links --}}
        <div class="mt-16 grid gap-10 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <a href="#" class="flex items-center gap-2">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <x-icon.scan-line class="h-5 w-5" />
                    </span>
                    <span class="text-lg font-bold text-foreground">InCase</span>
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-muted-foreground">
                    Smart School Bag Management System berbasis RFID, IoT, dan AI untuk siswa Indonesia.
                </p>
            </div>

            @foreach ($columns as $column)
                <div>
                    <h3 class="text-sm font-semibold text-foreground">{{ $column['title'] }}</h3>
                    <ul class="mt-4 flex flex-col gap-3">
                        @foreach ($column['links'] as $link)
                            <li>
                                <a href="#" class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                                    {{ $link }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="mt-12 flex flex-col items-center justify-between gap-4 border-t border-border pt-8 sm:flex-row">
            <p class="text-sm text-muted-foreground">
                &copy; {{ now()->year }} InCase. Semua hak dilindungi.
            </p>
            <div class="flex gap-6">
                <a href="#" class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                    Kebijakan Privasi
                </a>
                <a href="#" class="text-sm text-muted-foreground transition-colors hover:text-foreground">
                    Syarat &amp; Ketentuan
                </a>
            </div>
        </div>
    </div>
</footer>
