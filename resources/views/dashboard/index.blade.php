@php
    // ============================================
    // Semua variabel di bawah ini DIAMBIL/DITURUNKAN dari data asli
    // yang dikirim DashboardController: $user, $todaySubjects, $todayScans,
    // $items, $packedCount, $totalItems, $progress.
    // ============================================

    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $todayName = $dayNames[now()->dayOfWeekIso] ?? 'Senin';

    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $todayFull = $todayName . ', ' . now()->day . ' ' . $monthNames[now()->month] . ' ' . now()->year;

    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    // Peta kategori Item (enum asli: Book, Stationery, Electronics, Sports, Personal, Others) ke icon
    $categoryIcons = [
        'Book' => 'book-open',
        'Stationery' => 'document-text',
        'Electronics' => 'device-phone-mobile',
        'Sports' => 'tag',
        'Personal' => 'user',
        'Others' => 'cube',
    ];

    // Barang yang udah kescan hari ini, diturunkan dari $todayScans (bukan array baru)
    $scannedItemIds = $todayScans->pluck('item_id')->unique();

    // Barang yang BELUM discan hari ini
    $missingItems = $items->reject(fn ($item) => $scannedItemIds->contains($item->id))->values();

    // Terbaru duluan
    $latestScansSorted = $todayScans->sortByDesc('scanned_at')->values();

    $lastScan = $latestScansSorted->first();
    $lastScanTime = $lastScan ? \Carbon\Carbon::parse($lastScan->scanned_at)->format('H:i') : null;
    $lastScanNote = $lastScan
        ? 'Hari ini · ' . \Carbon\Carbon::parse($lastScan->scanned_at)->diffForHumans(null, true) . ' lalu'
        : 'Belum ada pindaian hari ini';

    // Pengingat Guru = turunan langsung dari $todaySubjects, bukan tabel/array reminder terpisah.
    // Satu subject bisa nyumbang 2 baris (PR + Ujian) kalau dua-duanya ada.
    $teacherReminders = collect();
    foreach ($todaySubjects as $subject) {
        if (! empty($subject->homework)) {
            $teacherReminders->push(['subject' => $subject->name, 'text' => 'PR: ' . $subject->homework]);
        }
        if ($subject->has_exam) {
            $teacherReminders->push(['subject' => $subject->name, 'text' => 'Ujian hari ini']);
        }
    }
@endphp

<x-layouts.dashboard title="Dasbor — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        {{-- Backdrop buat nutup sidebar mobile pas diklik di luar --}}
        <div
            id="mobile-sidebar-backdrop"
            onclick="toggleMobileSidebar()"
            class="fixed inset-0 z-30 hidden bg-slate-900/40 backdrop-blur-sm lg:hidden"
        ></div>

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            {{-- Top bar mobile --}}
            <div class="sticky top-0 z-20 flex items-center gap-3 border-b border-border bg-background/95 px-4 py-3 backdrop-blur lg:hidden">
                <button
                    type="button"
                    onclick="toggleMobileSidebar()"
                    class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card text-foreground"
                    aria-label="Buka menu navigasi"
                >
                    <x-icon.bars-3 class="h-5 w-5" />
                </button>
                <span class="text-sm font-bold text-foreground">InCase</span>
            </div>

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

                {{-- ============ SECTION 1 — GREETING ============ --}}
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        {{ $greeting }}, {{ $user->name }}
                    </h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        {{ $todayFull }}
                    </p>
                </div>

                {{-- ============ SECTION 2 — STAT CARDS ============ --}}
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="rounded-[24px] border border-success/20 bg-success/5 p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-success/15 text-success">
                            <x-icon.check-circle class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Barang Lengkap</p>
                        <div class="mt-1 flex flex-wrap items-center gap-2">
                            <p class="text-2xl font-bold text-foreground">{{ $packedCount }}/{{ $totalItems }}</p>
                            <span class="rounded-full bg-success/10 px-2.5 py-1 text-xs font-semibold text-success">
                                {{ $progress }}% siap
                            </span>
                        </div>
                    </div>

                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.viewfinder-circle class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Scan Terakhir</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $lastScanTime ?? '--:--' }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">{{ $lastScanNote }}</p>
                    </div>
                </div>

                {{-- ============ SECTION 2.5 — BARANG TERTINGGAL ============ --}}
                <div class="mt-6 rounded-[24px] border border-warning/30 bg-warning/5 p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2 text-warning">
                            <x-icon.exclamation-triangle class="h-4 w-4" />
                            <h2 class="text-sm font-semibold text-foreground">Barang Tertinggal</h2>
                        </div>
                        @if ($missingItems->count() > 0)
                            <span class="rounded-full bg-warning/10 px-3 py-1 text-xs font-semibold text-warning">
                                {{ $missingItems->count() }} barang
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 flex flex-col gap-3">
                        @forelse ($missingItems as $item)
                            <div class="flex items-center gap-3 rounded-xl bg-card px-4 py-3 shadow-sm">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                    <x-dynamic-component
                                        :component="'icon.' . ($categoryIcons[$item->category] ?? 'cube')"
                                        class="h-4 w-4"
                                    />
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-foreground">{{ $item->name }}</p>
                                    <p class="text-xs text-muted-foreground">Belum terdeteksi hari ini</p>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center gap-2 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
                                <x-icon.check-circle class="h-4 w-4" />
                                Semua barang wajib sudah masuk tas kamu.
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-5 h-2 w-full overflow-hidden rounded-full bg-warning/10">
                        <div class="h-full rounded-full bg-primary transition-all duration-500 ease-out" style="width: {{ $progress }}%"></div>
                    </div>
                </div>

                {{-- ============ SECTION 2.6 — BARANG HARI INI ============ --}}
                <div class="mt-6 rounded-[24px] border border-border bg-card p-6 shadow-sm">
                    <h2 class="text-sm font-semibold text-muted-foreground">Barang Hari Ini</h2>

                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                        @forelse ($items as $item)
                            @php $packed = $scannedItemIds->contains($item->id); @endphp
                            <div class="flex items-center gap-3 rounded-xl border border-border px-4 py-3">
                                <x-dynamic-component
                                    :component="'icon.' . ($categoryIcons[$item->category] ?? 'cube')"
                                    class="h-4 w-4 shrink-0 text-primary"
                                />
                                <span class="flex-1 truncate text-sm font-medium text-foreground">{{ $item->name }}</span>
                                @if ($packed)
                                    <x-icon.check-circle class="h-4 w-4 shrink-0 text-success" />
                                @else
                                    <x-icon.exclamation-triangle class="h-4 w-4 shrink-0 text-warning" />
                                @endif
                            </div>
                        @empty
                            <p class="col-span-2 py-4 text-center text-sm text-muted-foreground">
                                Belum ada barang terdaftar. Tambahkan dulu di halaman Barang.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- ============ SECTION 3 — TODAY'S SCHEDULE ============ --}}
                <div class="mt-6 rounded-[24px] border border-border bg-card p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-foreground">Jadwal Hari Ini</h2>

                    <div class="mt-5 flex flex-col">
                        @forelse ($todaySubjects as $subject)
                            <div class="relative flex gap-4">
                                <div class="flex flex-col items-center">
                                    <span class="flex h-2.5 w-2.5 shrink-0 rounded-full bg-primary"></span>
                                    @if (! $loop->last)
                                        <span class="mt-1 w-px flex-1 bg-border"></span>
                                    @endif
                                </div>

                                <div class="mb-5 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-sm font-bold text-foreground">{{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }}</span>
                                        <span class="text-sm font-medium text-foreground">{{ $subject->name }}</span>

                                        @if (! empty($subject->homework))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-warning/10 px-2 py-0.5 text-[11px] font-semibold text-warning">
                                                <x-icon.document-text class="h-3 w-3" />
                                                PR
                                            </span>
                                        @endif

                                        @if ($subject->has_exam)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2 py-0.5 text-[11px] font-semibold text-destructive">
                                                <x-icon.exclamation-triangle class="h-3 w-3" />
                                                Ujian
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-1 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                        <span class="inline-flex items-center gap-1">
                                            <x-icon.user class="h-3.5 w-3.5" />
                                            {{ $subject->teacher->name ?? '—' }}
                                        </span>
                                        @if ($subject->location)
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon.map-pin class="h-3.5 w-3.5" />
                                                {{ $subject->location }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="py-4 text-center text-sm text-muted-foreground">
                                Gak ada kelas hari ini.
                            </p>
                        @endforelse
                    </div>
                </div>

                {{-- ============ SECTION 4, 5, 6 — REMINDER / LATEST SCAN / AI PLACEHOLDER ============ --}}
                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    {{-- Teacher Reminder --}}
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                <x-icon.megaphone class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-foreground">Pengingat dari Guru</h2>
                        </div>

                        <ul class="mt-4 flex flex-col gap-3">
                            @forelse ($teacherReminders as $reminder)
                                <li class="flex items-start gap-2 text-sm leading-snug text-foreground">
                                    <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-muted-foreground"></span>
                                    <span><span class="font-semibold">{{ $reminder['subject'] }}</span> — {{ $reminder['text'] }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-muted-foreground">Belum ada pengingat dari guru.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Latest Scan --}}
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <x-icon.viewfinder-circle class="h-4 w-4" />
                            </span>
                            <h2 class="text-sm font-semibold text-foreground">Pindai Terbaru</h2>
                        </div>

                        <ul class="mt-4 flex flex-col gap-3">
                            @forelse ($latestScansSorted as $scan)
                                <li class="flex items-center justify-between text-sm">
                                    <span class="flex items-center gap-2 text-foreground">
                                        <x-icon.check-circle class="h-4 w-4 text-success" />
                                        {{ $scan->item->name ?? 'Barang' }} discan
                                    </span>
                                    <span class="text-xs text-muted-foreground">{{ \Carbon\Carbon::parse($scan->scanned_at)->format('H:i') }}</span>
                                </li>
                            @empty
                                <li class="text-sm text-muted-foreground">Belum ada aktivitas pindai hari ini.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- AI Assistant Placeholder --}}
                    <div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card p-6 text-center shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.sparkles class="h-5 w-5" />
                        </span>
                        <h2 class="mt-3 text-sm font-semibold text-foreground">Asisten AI</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Segera Hadir</p>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleMobileSidebar() {
            var sidebar = document.querySelector('aside');
            var backdrop = document.getElementById('mobile-sidebar-backdrop');
            if (!sidebar || !backdrop) return;

            var isOpen = sidebar.classList.contains('flex');

            if (isOpen) {
                sidebar.classList.remove('flex');
                sidebar.classList.add('hidden');
                backdrop.classList.add('hidden');
            } else {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('flex');
                backdrop.classList.remove('hidden');
            }
        }
    </script>
</x-layouts.dashboard>