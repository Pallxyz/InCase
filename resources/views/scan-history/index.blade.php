@php
    $scans = [
        [
            'id' => 1,
            'time' => '07:14',
            'date' => '9 Jul 2026',
            'scanType' => 'Auto Scan',
            'status' => 'success',
            'duration' => '0.8 detik',
            'itemsDetected' => 8,
            'itemsTotal' => 8,
            'detectedItems' => ['Laptop', 'Buku Fisika', 'Kalkulator', 'Botol Minum', 'Buku Matematika', 'Buku Tulis', 'Pensil', 'Penggaris'],
            'missingItems' => [],
            'aiSummary' => 'Semua barang wajib hari ini terdeteksi lengkap. Tas siap dibawa ke sekolah.',
            'device' => 'ESP32-01',
            'location' => 'Box Kamar Nopal',
            'signal' => 'Kuat',
        ],
        [
            'id' => 2,
            'time' => '06:52',
            'date' => '9 Jul 2026',
            'scanType' => 'Manual Scan',
            'status' => 'warning',
            'duration' => '1.2 detik',
            'itemsDetected' => 6,
            'itemsTotal' => 8,
            'detectedItems' => ['Laptop', 'Buku Fisika', 'Kalkulator', 'Botol Minum', 'Buku Tulis', 'Pensil'],
            'missingItems' => ['Buku Matematika', 'Penggaris'],
            'aiSummary' => 'Ada 2 barang yang belum terdeteksi. Coba pindai ulang setelah semua barang dimasukkan ke tas.',
            'device' => 'ESP32-01',
            'location' => 'Box Kamar Nopal',
            'signal' => 'Sedang',
        ],
        [
            'id' => 3,
            'time' => '20:31',
            'date' => '8 Jul 2026',
            'scanType' => 'Bag Closed',
            'status' => 'success',
            'duration' => '0.6 detik',
            'itemsDetected' => 8,
            'itemsTotal' => 8,
            'detectedItems' => ['Laptop', 'Buku Fisika', 'Kalkulator', 'Botol Minum', 'Buku Matematika', 'Buku Tulis', 'Pensil', 'Penggaris'],
            'missingItems' => [],
            'aiSummary' => 'Tas ditutup dengan seluruh barang wajib untuk esok hari sudah lengkap.',
            'device' => 'ESP32-01',
            'location' => 'Box Kamar Nopal',
            'signal' => 'Kuat',
        ],
        [
            'id' => 4,
            'time' => '14:45',
            'date' => '8 Jul 2026',
            'scanType' => 'Auto Scan',
            'status' => 'missing',
            'duration' => '1.5 detik',
            'itemsDetected' => 5,
            'itemsTotal' => 8,
            'detectedItems' => ['Laptop', 'Kalkulator', 'Botol Minum', 'Buku Tulis', 'Pensil'],
            'missingItems' => ['Buku Fisika', 'Buku Matematika', 'Penggaris'],
            'aiSummary' => 'Tiga barang penting belum terdeteksi sejak pulang sekolah. Cek kembali isi tas.',
            'device' => 'ESP32-01',
            'location' => 'Box Kamar Nopal',
            'signal' => 'Lemah',
        ],
        [
            'id' => 5,
            'time' => '07:10',
            'date' => '8 Jul 2026',
            'scanType' => 'Auto Scan',
            'status' => 'success',
            'duration' => '0.9 detik',
            'itemsDetected' => 8,
            'itemsTotal' => 8,
            'detectedItems' => ['Laptop', 'Buku Fisika', 'Kalkulator', 'Botol Minum', 'Buku Matematika', 'Buku Tulis', 'Pensil', 'Penggaris'],
            'missingItems' => [],
            'aiSummary' => 'Semua barang wajib hari ini terdeteksi lengkap.',
            'device' => 'ESP32-01',
            'location' => 'Box Kamar Nopal',
            'signal' => 'Kuat',
        ],
        [
            'id' => 6,
            'time' => '19:20',
            'date' => '7 Jul 2026',
            'scanType' => 'Bag Open',
            'status' => 'warning',
            'duration' => '0.7 detik',
            'itemsDetected' => 7,
            'itemsTotal' => 8,
            'detectedItems' => ['Laptop', 'Buku Fisika', 'Kalkulator', 'Botol Minum', 'Buku Matematika', 'Buku Tulis', 'Pensil'],
            'missingItems' => ['Penggaris'],
            'aiSummary' => 'Tas dibuka untuk mengecek isi. 1 barang kecil belum terdeteksi, kemungkinan tertinggal di kamar.',
            'device' => 'ESP32-01',
            'location' => 'Box Kamar Nopal',
            'signal' => 'Sedang',
        ],
    ];

    $itemsForAlpine = collect($scans)->map(function ($scan) {
        return [
            'id' => $scan['id'],
            'scanType' => $scan['scanType'],
            'status' => $scan['status'],
            'timestamp' => strtotime($scan['date'] . ' ' . $scan['time']),
        ];
    })->values();

    // Analytics panel kanan
    $todayScans = collect($scans)->filter(fn ($s) => $s['date'] === '9 Jul 2026');
    $totalScansToday = $todayScans->count();
    $successScansToday = $todayScans->where('status', 'success')->count();
    $missingAlertsToday = $todayScans->whereIn('status', ['missing', 'warning'])->count();
    $avgDuration = $todayScans->isNotEmpty()
        ? round($todayScans->avg(fn ($s) => (float) str_replace(' detik', '', $s['duration'])), 1)
        : 0;
@endphp

@push('scripts')
<script>
    function scanHistoryPage() {
        return {
            items: [],
            search: '',
            status: 'all',
            sortBy: 'newest',

            matches(id) {
                const item = this.items.find(i => i.id === id);
                if (!item) return true;

                const searchMatch = item.scanType.toLowerCase().includes(this.search.toLowerCase());
                const statusMatch = this.status === 'all' || item.status === this.status;

                return searchMatch && statusMatch;
            },

            sortedIds() {
                let sorted = [...this.items];
                if (this.sortBy === 'newest') sorted.sort((a, b) => b.timestamp - a.timestamp);
                if (this.sortBy === 'oldest') sorted.sort((a, b) => a.timestamp - b.timestamp);
                return sorted.map(i => i.id);
            },

            order(id) {
                return this.sortedIds().indexOf(id);
            },

            visibleCount() {
                return this.items.filter(i => this.matches(i.id)).length;
            },
        };
    }
</script>
@endpush

<x-layouts.dashboard title="Riwayat Pindai — InCase">
    <div x-data="scanHistoryPage()" x-init="items = @js($itemsForAlpine)" class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64 lg:mr-80">
            <div class="mx-auto max-w-4xl px-6 py-8 sm:px-8">
                {{-- Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Riwayat Pindai
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Lihat setiap pemindaian RFID, analisis AI, dan aktivitas tas.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                        >
                            <x-icon.arrow-down-tray class="h-4 w-4" />
                            Ekspor
                        </button>
                        <button
                            type="button"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            <x-icon.viewfinder-circle class="h-4 w-4" />
                            Pindai Sekarang
                        </button>
                    </div>
                </div>

                {{-- Filter bar --}}
                <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-border bg-card p-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-muted-foreground">
                            <x-icon.magnifying-glass class="h-4 w-4" />
                        </span>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari jenis pindai..."
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>

                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.calendar class="h-4 w-4" />
                        </span>
                        <input
                            type="date"
                            class="rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>

                    <div class="relative">
                        <select
                            x-model="status"
                            class="appearance-none rounded-xl border border-border bg-background py-2.5 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="all">Semua Status</option>
                            <option value="success">Berhasil</option>
                            <option value="missing">Barang Kurang</option>
                            <option value="warning">Peringatan</option>
                        </select>
                        <x-icon.funnel class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>

                    <div class="relative">
                        <select
                            x-model="sortBy"
                            class="appearance-none rounded-xl border border-border bg-background py-2.5 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="newest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                        </select>
                        <x-icon.arrows-up-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>
                </div>

                {{-- Timeline --}}
                <div class="mt-8 flex flex-col" x-show="visibleCount() > 0">
                    @foreach ($scans as $scan)
                        <x-scan-history-card
                            :scan-id="$scan['id']"
                            :time="$scan['time']"
                            :date="$scan['date']"
                            :scan-type="$scan['scanType']"
                            :status="$scan['status']"
                            :duration="$scan['duration']"
                            :items-detected="$scan['itemsDetected']"
                            :items-total="$scan['itemsTotal']"
                            :detected-items="$scan['detectedItems']"
                            :missing-items="$scan['missingItems']"
                            :ai-summary="$scan['aiSummary']"
                            :device="$scan['device']"
                            :location="$scan['location']"
                            :signal="$scan['signal']"
                        />
                    @endforeach
                </div>

                {{-- Empty state --}}
                <div class="mt-8" x-show="visibleCount() === 0" x-cloak>
                    <x-empty-state
                        icon="viewfinder-circle"
                        title="Belum ada riwayat pindai"
                        description="Smart School Bag kamu belum pernah melakukan pemindaian RFID. Mulai pemindaian pertamamu sekarang."
                        button-label="Mulai Pindai Pertama"
                    />
                </div>
            </div>
        </main>

        {{-- Right analytics panel --}}
        <aside class="fixed inset-y-0 right-0 z-30 hidden w-80 shrink-0 overflow-y-auto scrollbar-none border-l border-border bg-background lg:block">
            <div class="flex flex-col gap-5 p-5">
                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="text-sm font-semibold text-foreground">Ringkasan Hari Ini</p>

                    <div class="mt-4 flex flex-col gap-4">
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-muted-foreground">
                                <x-icon.viewfinder-circle class="h-4 w-4 text-primary" />
                                Total Pindai
                            </span>
                            <span class="text-sm font-bold text-foreground">{{ $totalScansToday }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-muted-foreground">
                                <x-icon.check-circle class="h-4 w-4 text-success" />
                                Pindai Berhasil
                            </span>
                            <span class="text-sm font-bold text-foreground">{{ $successScansToday }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-muted-foreground">
                                <x-icon.exclamation-triangle class="h-4 w-4 text-warning" />
                                Peringatan Barang
                            </span>
                            <span class="text-sm font-bold text-foreground">{{ $missingAlertsToday }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-sm text-muted-foreground">
                                <x-icon.clock class="h-4 w-4 text-primary" />
                                Rata-rata Durasi
                            </span>
                            <span class="text-sm font-bold text-foreground">{{ $avgDuration }} detik</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="text-sm font-semibold text-foreground">Status Perangkat</p>

                    <div class="mt-4 flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-success/10 text-success">
                            <x-icon.wifi class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-foreground">ESP32-01 Terhubung</p>
                            <p class="text-xs text-muted-foreground">Box Kamar Nopal</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-border pt-4 text-xs">
                        <span class="text-muted-foreground">Sinkronisasi Terakhir</span>
                        <span class="font-medium text-foreground">5 detik lalu</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>
