@php
    $features = [
        [
            'icon' => 'nfc',
            'title' => 'RFID Otomatis',
            'description' => 'Setiap barang dengan RFID sticker terdeteksi otomatis saat tas didekatkan ke Scanner Box, tanpa perlu mengecek satu per satu.',
        ],
        [
            'icon' => 'bell-ring',
            'title' => 'Pengingat Pintar',
            'description' => 'Smart Reminder berbasis AI memberi tahu barang yang belum masuk sebelum kamu berangkat ke sekolah.',
        ],
        [
            'icon' => 'calendar-days',
            'title' => 'Jadwal Fleksibel',
            'description' => 'Atur daftar barang berdasarkan jadwal pelajaran harian, sehingga InCase tahu barang wajib setiap hari.',
        ],
        [
            'icon' => 'history',
            'title' => 'Riwayat Pemindaian',
            'description' => 'Lihat catatan lengkap setiap pemindaian tas untuk memantau kebiasaan dan kelengkapan barang.',
        ],
    ];
@endphp

<section id="fitur" class="mx-auto max-w-[1280px] scroll-mt-20 px-6 py-24">
    <div class="mx-auto max-w-2xl text-center">
        <span class="text-sm font-semibold uppercase tracking-wide text-primary">Fitur</span>
        <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            Semua yang dibutuhkan agar tidak ada barang tertinggal
        </h2>
        <p class="mt-4 text-pretty text-lg leading-relaxed text-muted-foreground">
            InCase menggabungkan RFID, IoT, dan AI dalam satu sistem yang sederhana untuk digunakan
            setiap hari.
        </p>
    </div>

    <div class="mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($features as $feature)
            <div class="group rounded-[20px] border border-border bg-card p-6 shadow-sm transition-all duration-[250ms] hover:-translate-y-1 hover:shadow-lg">
                <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                    <x-dynamic-component :component="'icon.' . $feature['icon']" class="h-6 w-6" :stroke-width="1.75" />
                </span>
                <h3 class="mt-5 text-lg font-semibold text-foreground">{{ $feature['title'] }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    {{ $feature['description'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>
