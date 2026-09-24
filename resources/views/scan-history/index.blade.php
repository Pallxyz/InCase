@php
    $itemsForAlpine = $scans->map(function ($scan) {
        return [
            'id' => $scan['id'],
            'scanType' => $scan['scanType'],
            'status' => $scan['status'],
            'dateRaw' => $scan['dateRaw'],
            'timestamp' => $scan['timestamp'],
        ];
    })->values();

    $lastSyncLabel = $lastScan ? $lastScan->scanned_at->diffForHumans() : 'Belum pernah';
@endphp

@push('scripts')
<script>
    function scanHistoryPage() {
        return {
            items: [],
            search: '',
            status: 'all',
            dateFilter: '',
            sortBy: 'newest',

            matches(id) {
                const item = this.items.find(i => i.id === id);
                if (!item) return true;

                const searchMatch = item.scanType.toLowerCase().includes(this.search.toLowerCase());
                const statusMatch = this.status === 'all' || item.status === this.status;
                const dateMatch = this.dateFilter === '' || item.dateRaw === this.dateFilter;

                return searchMatch && statusMatch && dateMatch;
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
            <x-mobile-topbar title="Riwayat Pindai — InCase" />

            <div class="mx-auto max-w-4xl px-6 py-8 sm:px-8">
                {{-- Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Riwayat Pindai
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Lihat setiap pemindaian RFID, dicocokkan dengan barang wajib di jadwal kamu.
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
                            x-model="dateFilter"
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
                        <div 
                            x-show="matches({{ $scan['id'] }})" 
                            :style="'order: ' + order({{ $scan['id'] }})"
                            class="flex flex-col"
                        >
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
                        </div>
                    @endforeach
                </div>

                {{-- Empty state --}}
                <div class="mt-8" @if($scans->isNotEmpty()) x-show="visibleCount() === 0" x-cloak @endif>
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
                            <p class="text-xs text-muted-foreground">Box RFID Kamu</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-border pt-4 text-xs">
                        <span class="text-muted-foreground">Sinkronisasi Terakhir</span>
                        <span class="font-medium text-foreground">{{ $lastSyncLabel }}</span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>