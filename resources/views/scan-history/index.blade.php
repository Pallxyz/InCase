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

    $lastAtIso = $lastScan ? $lastScan->scanned_at->toIso8601String() : null;
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

            // --- status perangkat & real-time ---
            lastAt: null,
            latestId: null,
            online: false,
            latestUrl: @json(route('scan-history.latest')),

            // --- mode pindai (Scan Berangkat / Scan Pulang) ---
            mode: null,          // null | 'packing' | 'return'
            modeLeft: 0,
            modeTimer: null,
            modeError: '',
            modeBusy: false,
            canPack: false,
            canReturn: false,
            returnOpensAt: null,
            modeUrl: @json(route('scan-history.mode')),
            csrf: @json(csrf_token()),

            start() {
                this.refreshOnline();
                this.poll();
                setInterval(() => this.refreshOnline(), 15000);
                setInterval(() => this.poll(), 3000);
            },

            initMode(mode, remaining, canPack, canReturn, returnOpensAt) {
                this.canPack = canPack;
                this.canReturn = canReturn;
                this.returnOpensAt = returnOpensAt;
                if (mode && remaining > 0) this.beginCountdown(mode, remaining);
            },

            beginCountdown(mode, seconds) {
                this.mode = mode;
                this.modeLeft = seconds;
                clearInterval(this.modeTimer);
                this.modeTimer = setInterval(() => {
                    this.modeLeft--;
                    if (this.modeLeft <= 0) this.endMode();
                }, 1000);
            },

            endMode() {
                clearInterval(this.modeTimer);
                this.mode = null;
                this.modeLeft = 0;
            },

            modeLabel() {
                return this.mode === 'return' ? 'Scan Pulang' : 'Scan Berangkat';
            },

            async sendMode(mode) {
                const res = await fetch(this.modeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                    body: JSON.stringify({ mode: mode }),
                });
                const data = await res.json().catch(() => ({}));
                return { ok: res.ok, data: data };
            },

            async selectMode(mode) {
                if (this.modeBusy) return;
                this.modeError = '';

                // klik tombol yang sedang aktif = batal
                if (this.mode === mode) {
                    await this.cancelMode();
                    return;
                }

                this.modeBusy = true;
                try {
                    const result = await this.sendMode(mode);
                    if (!result.ok) {
                        this.modeError = result.data.message || 'Gagal memilih mode pindai.';
                        return;
                    }
                    this.beginCountdown(mode, result.data.remaining);
                } catch (e) {
                    this.modeError = 'Gagal terhubung ke server.';
                } finally {
                    this.modeBusy = false;
                }
            },

            async cancelMode() {
                this.modeBusy = true;
                try {
                    await this.sendMode('none');
                } catch (e) {
                    // tetap batalkan di layar walau server gagal dihubungi
                } finally {
                    this.endMode();
                    this.modeBusy = false;
                }
            },

            // dipakai tombol "Mulai Pindai Pertama" di empty state
            startListening() {
                this.selectMode(this.canPack ? 'packing' : 'return');
            },

            async poll() {
                try {
                    const res = await fetch(this.latestUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) return;
                    const data = await res.json();

                    // panggilan pertama: cuma simpan baseline
                    if (this.latestId === null) {
                        this.latestId = data.latestId ?? 0;
                        if (data.latestAt) this.lastAt = data.latestAt;
                        this.refreshOnline();
                        return;
                    }

                    // ada scan baru -> muat ulang halaman biar semua angka & daftar ikut update
                    if ((data.latestId ?? 0) !== this.latestId) {
                        window.location.reload();
                    }
                } catch (e) {
                    // koneksi putus sebentar, abaikan
                }
            },

            refreshOnline() {
                this.online = this.lastAt
                    ? (Date.now() - new Date(this.lastAt).getTime()) < 5 * 60 * 1000
                    : false;
            },

            lastSyncText() {
                if (!this.lastAt) return 'Belum pernah';
                return new Date(this.lastAt).toLocaleString('id-ID', {
                    day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit',
                });
            },

            // --- filter & sort ---
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

<x-layouts.dashboard title="Pindai — InCase">
    <div x-data="scanHistoryPage()"
         x-init="items = @js($itemsForAlpine); lastAt = @js($lastAtIso); initMode(@js($scanMode), @js($scanModeRemaining), @js($canPack), @js($canReturn), @js($returnOpensAt)); start()"
         class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64 lg:mr-80">
            <x-mobile-topbar title="Pindai — InCase" />

            <div class="mx-auto max-w-4xl px-6 py-8 sm:px-8">
                {{-- Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Pindai
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Pilih Scan Berangkat atau Scan Pulang, lalu tempelkan barangmu ke box.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <a
                            href="{{ route('scan-history.export') }}"
                            class="inline-flex items-center justify-center gap-2 rounded-full border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                        >
                            <x-icon.arrow-down-tray class="h-4 w-4" />
                            Ekspor
                        </a>
                    </div>
                </div>

                {{-- Pilihan mode pindai --}}
                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    <button
                        type="button"
                        @click="selectMode('packing')"
                        :disabled="!canPack && mode !== 'packing'"
                        :class="mode === 'packing'
                            ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                            : 'border-border bg-card text-foreground hover:bg-muted'"
                        class="flex items-center gap-3 rounded-2xl border p-4 text-left transition-colors disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-card"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                              :class="mode === 'packing' ? '!bg-white/20 !text-primary-foreground' : ''">
                            <x-icon.backpack class="h-5 w-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold">Scan Berangkat</span>
                            <span class="block text-xs opacity-80"
                                  x-text="mode === 'packing' ? 'Aktif, klik lagi untuk batal' : (canPack ? 'Siapkan barang wajib' : 'Tidak tersedia sekarang')"></span>
                        </span>
                    </button>

                    <button
                        type="button"
                        @click="selectMode('return')"
                        :disabled="!canReturn && mode !== 'return'"
                        :class="mode === 'return'
                            ? 'border-primary bg-primary text-primary-foreground shadow-sm'
                            : 'border-border bg-card text-foreground hover:bg-muted'"
                        class="flex items-center gap-3 rounded-2xl border p-4 text-left transition-colors disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:bg-card"
                    >
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                              :class="mode === 'return' ? '!bg-white/20 !text-primary-foreground' : ''">
                            <x-icon.home class="h-5 w-5" />
                        </span>
                        <span class="min-w-0">
                            <span class="block text-sm font-bold">Scan Pulang</span>
                            <span class="block text-xs opacity-80"
                                  x-text="mode === 'return'
                                      ? 'Aktif, klik lagi untuk batal'
                                      : (canReturn ? 'Cek barang sebelum pulang'
                                          : (returnOpensAt ? 'Tersedia setelah pelajaran terakhir (' + returnOpensAt + ')' : 'Tidak ada pelajaran hari ini'))"></span>
                        </span>
                    </button>
                </div>

                <p x-show="modeError" x-cloak x-text="modeError"
                   class="mt-3 rounded-xl bg-destructive/10 px-4 py-2.5 text-sm font-medium text-destructive"></p>

                {{-- Mode menunggu pindai --}}
                <div x-show="mode" x-cloak
                     class="mt-4 flex items-center gap-4 rounded-2xl border border-primary/30 bg-primary/5 p-4">
                    <span class="relative flex h-3 w-3">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-75"></span>
                        <span class="relative inline-flex h-3 w-3 rounded-full bg-primary"></span>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-foreground">
                            <span x-text="modeLabel()"></span> aktif, menunggu pindai...
                        </p>
                        <p class="text-xs text-muted-foreground">
                            Tempelkan tag RFID barangmu ke box. Halaman otomatis diperbarui begitu terbaca.
                            (<span x-text="modeLeft"></span> detik lagi)
                        </p>
                    </div>
                    <button type="button" @click="cancelMode()"
                        class="shrink-0 rounded-full border border-border bg-card px-3.5 py-1.5 text-xs font-semibold text-foreground transition-colors hover:bg-muted">
                        Batal
                    </button>
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
                <div class="mt-8" @if($scans->isNotEmpty()) x-show="visibleCount() === 0" x-cloak @endif>
                    <x-empty-state
                        icon="viewfinder-circle"
                        title="Belum ada riwayat pindai"
                        description="Smart School Bag kamu belum pernah melakukan pemindaian RFID. Mulai pemindaian pertamamu sekarang."
                        button-label="Mulai Pindai Pertama"
                        button-click="startListening()"
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
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg"
                              :class="online ? 'bg-success/10 text-success' : 'bg-muted text-muted-foreground'">
                            <x-icon.wifi class="h-4 w-4" />
                        </span>
                        <div>
                            <p class="text-sm font-medium text-foreground"
                               x-text="online ? 'ESP32-01 Aktif' : 'ESP32-01 Tidak aktif'"></p>
                            <p class="text-xs text-muted-foreground">Box RFID Kamu</p>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-border pt-4 text-xs">
                        <span class="text-muted-foreground">Sinkronisasi Terakhir</span>
                        <span class="font-medium text-foreground" x-text="lastSyncText()"></span>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.dashboard>