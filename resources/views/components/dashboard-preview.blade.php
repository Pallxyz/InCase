@php
    $sidebarItems = [
        ['icon' => 'layout-dashboard', 'label' => 'Dashboard', 'active' => true],
        ['icon' => 'backpack', 'label' => 'Barang', 'active' => false],
        ['icon' => 'calendar-days', 'label' => 'Jadwal', 'active' => false],
        ['icon' => 'history', 'label' => 'Riwayat', 'active' => false],
        ['icon' => 'settings', 'label' => 'Pengaturan', 'active' => false],
    ];

    $todayItems = [
        ['icon' => 'book-open', 'label' => 'Buku Matematika', 'detected' => true],
        ['icon' => 'ruler', 'label' => 'Alat Tulis', 'detected' => true],
        ['icon' => 'laptop', 'label' => 'Laptop', 'detected' => true],
        ['icon' => 'backpack', 'label' => 'Baju Olahraga', 'detected' => false],
    ];

    $schedule = [
        ['time' => '07:00', 'subject' => 'Matematika', 'room' => 'Ruang 3A'],
        ['time' => '09:30', 'subject' => 'Bahasa Indonesia', 'room' => 'Ruang 2B'],
        ['time' => '13:00', 'subject' => 'Olahraga', 'room' => 'Lapangan'],
    ];

    $notifications = [
        [
            'icon' => 'alert-triangle',
            'tone' => 'warning',
            'title' => 'Baju olahraga belum terdeteksi',
            'time' => 'Baru saja',
        ],
        [
            'icon' => 'check-circle-2',
            'tone' => 'success',
            'title' => 'Laptop berhasil dipindai',
            'time' => '2 menit lalu',
        ],
        [
            'icon' => 'bell',
            'tone' => 'primary',
            'title' => 'Pengingat: pelajaran Olahraga pukul 13.00',
            'time' => '10 menit lalu',
        ],
    ];

    $toneStyles = [
        'warning' => 'bg-warning/10 text-warning',
        'success' => 'bg-success/10 text-success',
        'primary' => 'bg-primary/10 text-primary',
    ];
@endphp

<section id="dashboard" class="mx-auto max-w-[1280px] scroll-mt-20 px-6 py-24">
    <div class="mx-auto max-w-2xl text-center">
        <span class="text-sm font-semibold uppercase tracking-wide text-primary">
            Dashboard
        </span>
        <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            Pantau semuanya dari satu dashboard
        </h2>
        <p class="mt-4 text-pretty text-lg leading-relaxed text-muted-foreground">
            Dashboard web InCase menampilkan status tas, barang hari ini, dan pengingat secara
            real-time.
        </p>
    </div>

    <div class="mt-14 overflow-hidden rounded-[24px] border border-border bg-card shadow-[0_30px_80px_-30px_rgba(15,23,42,0.25)]">
        {{-- Browser chrome --}}
        <div class="flex items-center gap-2 border-b border-border bg-muted px-4 py-3">
            <span class="h-3 w-3 rounded-full bg-destructive/70"></span>
            <span class="h-3 w-3 rounded-full bg-warning"></span>
            <span class="h-3 w-3 rounded-full bg-success"></span>
            <div class="ml-4 hidden flex-1 items-center rounded-full bg-card px-3 py-1 text-xs text-muted-foreground sm:flex">
                app.incase.id/dashboard
            </div>
        </div>

        <div class="flex">
            {{-- Sidebar --}}
            <aside class="hidden w-56 shrink-0 flex-col border-r border-border bg-card p-4 md:flex">
                <div class="flex items-center gap-2 px-2 pb-6">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                        <x-icon.scan-line class="h-4 w-4" />
                    </span>
                    <span class="font-bold text-foreground">InCase</span>
                </div>
                <nav class="flex flex-col gap-1">
                    @foreach ($sidebarItems as $item)
                        <span
                            @class([
                                'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                                'bg-primary/10 text-primary' => $item['active'],
                                'text-muted-foreground' => ! $item['active'],
                            ])
                        >
                            <x-dynamic-component :component="'icon.' . $item['icon']" class="h-4 w-4" />
                            {{ $item['label'] }}
                        </span>
                    @endforeach
                </nav>

                {{-- Connected Scanner Box --}}
                <div class="mt-auto rounded-2xl border border-border bg-background p-4">
                    <div class="flex items-center gap-2">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-success/10 text-success">
                            <x-icon.wifi class="h-4 w-4" />
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-xs font-semibold text-foreground">Scanner Box</p>
                            <p class="flex items-center gap-1 text-[11px] text-success">
                                <span class="h-1.5 w-1.5 rounded-full bg-success"></span>
                                Terhubung
                            </p>
                        </div>
                    </div>
                    <p class="mt-3 text-[11px] text-muted-foreground">Box Kamar Rania · 98% sinyal</p>
                </div>
            </aside>

            {{-- Main --}}
            <div class="flex-1 bg-background p-5 sm:p-6">
                {{-- Topbar --}}
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-foreground">Selamat pagi, Rania</h3>
                        <p class="text-sm text-muted-foreground">Senin, 6 Juli 2026</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="hidden h-9 w-40 items-center gap-2 rounded-full border border-border bg-card px-3 text-sm text-muted-foreground sm:flex">
                            <x-icon.search class="h-4 w-4" />
                            Cari barang
                        </span>
                        <span class="relative flex h-9 w-9 items-center justify-center rounded-full border border-border bg-card text-muted-foreground">
                            <x-icon.bell class="h-4 w-4" />
                            <span class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-destructive"></span>
                        </span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                            R
                        </span>
                    </div>
                </div>

                {{-- Quick stat row --}}
                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div class="rounded-[20px] border border-success/20 bg-success/5 p-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-success/10 text-success">
                            <x-icon.shield-check class="h-4 w-4" />
                        </span>
                        <p class="mt-3 text-xs font-medium text-muted-foreground">Barang Lengkap</p>
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-2xl font-bold text-foreground">3/4</span>
                            <span class="rounded-full bg-success/10 px-2 py-0.5 text-[11px] font-semibold text-success">
                                75% siap
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[20px] border border-border bg-card p-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.timer class="h-4 w-4" />
                        </span>
                        <p class="mt-3 text-xs font-medium text-muted-foreground">Scan Terakhir</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">06:42</p>
                        <p class="text-[11px] text-muted-foreground">Hari ini · 0,8 detik</p>
                    </div>

                    <div class="rounded-[20px] border border-border bg-card p-4">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-success/10 text-success">
                            <x-icon.wifi class="h-4 w-4" />
                        </span>
                        <p class="mt-3 text-xs font-medium text-muted-foreground">Scanner Box</p>
                        <p class="mt-1 flex items-center gap-1.5 text-lg font-bold text-foreground">
                            <span class="h-2 w-2 rounded-full bg-success"></span>
                            Terhubung
                        </p>
                        <p class="text-[11px] text-muted-foreground">Box Kamar Rania</p>
                    </div>
                </div>

                {{-- Widgets --}}
                <div class="mt-4 grid gap-4 lg:grid-cols-3">
                    {{-- Missing Items Alert --}}
                    <div class="rounded-[20px] border border-warning/30 bg-warning/5 p-5 lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-warning">
                                <x-icon.alert-triangle class="h-4 w-4" />
                                Barang Tertinggal
                            </span>
                            <span class="rounded-full bg-warning/10 px-3 py-1 text-xs font-semibold text-warning">
                                1 barang
                            </span>
                        </div>
                        <div class="mt-4 flex items-center gap-3 rounded-xl border border-warning/30 bg-card px-3 py-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                <x-icon.backpack class="h-4 w-4" />
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Baju Olahraga</p>
                                <p class="text-xs text-muted-foreground">
                                    Dibutuhkan untuk pelajaran pukul 13.00
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-muted">
                            <div class="h-full w-3/4 rounded-full bg-primary"></div>
                        </div>
                    </div>

                    {{-- Notification Panel --}}
                    <div class="rounded-[20px] border border-border bg-card p-5">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-muted-foreground">Notifikasi</span>
                            <span class="text-xs font-medium text-primary">Lihat semua</span>
                        </div>
                        <div class="mt-3 flex flex-col gap-3">
                            @foreach ($notifications as $n)
                                <div class="flex items-start gap-3">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $toneStyles[$n['tone']] }}">
                                        <x-dynamic-component :component="'icon.' . $n['icon']" class="h-4 w-4" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-xs font-medium leading-snug text-foreground">
                                            {{ $n['title'] }}
                                        </p>
                                        <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $n['time'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Barang Hari Ini --}}
                    <div class="rounded-[20px] border border-border bg-card p-5 lg:col-span-2">
                        <span class="text-sm font-medium text-muted-foreground">Barang Hari Ini</span>
                        <div class="mt-3 grid gap-2.5 sm:grid-cols-2">
                            @foreach ($todayItems as $item)
                                <div class="flex items-center gap-2.5 rounded-xl border border-border bg-background px-3 py-2.5">
                                    <x-dynamic-component :component="'icon.' . $item['icon']" class="h-4 w-4 shrink-0 text-primary" />
                                    <span class="truncate text-sm font-medium text-foreground">
                                        {{ $item['label'] }}
                                    </span>
                                    @if ($item['detected'])
                                        <x-icon.check-circle-2 class="ml-auto h-4 w-4 shrink-0 text-success" />
                                    @else
                                        <x-icon.alert-triangle class="ml-auto h-4 w-4 shrink-0 text-warning" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Today's Schedule --}}
                    <div class="rounded-[20px] border border-border bg-card p-5">
                        <span class="text-sm font-medium text-muted-foreground">Jadwal Hari Ini</span>
                        <div class="mt-3 flex flex-col gap-3">
                            @foreach ($schedule as $item)
                                <div class="flex items-start gap-3">
                                    <span class="mt-0.5 flex items-center gap-1 rounded-md bg-primary/10 px-2 py-0.5 text-[11px] font-semibold text-primary">
                                        <x-icon.clock class="h-3 w-3" />
                                        {{ $item['time'] }}
                                    </span>
                                    <div>
                                        <p class="text-sm font-medium text-foreground">{{ $item['subject'] }}</p>
                                        <p class="text-[11px] text-muted-foreground">{{ $item['room'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
