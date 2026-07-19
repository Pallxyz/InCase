@php
    // ============================================
    // Semua variabel di bawah ini DIAMBIL/DITURUNKAN dari data asli
    // yang dikirim DashboardController: $user, $todaySubjects, $todayScans,
    // $items, $packedCount, $totalItems, $progress.
    // Gak ada array/data fiktif lagi di sini.
    // ============================================

    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $todayName = $dayNames[now()->dayOfWeekIso] ?? 'Senin';

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

    // Terbaru duluan
    $latestScansSorted = $todayScans->sortByDesc('scanned_at')->values();
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
            {{-- Top bar mobile: cuma nongol di bawah breakpoint lg, isinya tombol buka sidebar --}}
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
                        Selamat Pagi, Nopal
                    </h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        Hari ini {{ $todayName }}. Kamu punya {{ $todaySubjects->count() }} kelas hari ini.
                    </p>
                </div>

                {{-- ============ SECTION 2 — CHECKLIST + PROGRESS ============ --}}
                <div class="mt-6 grid gap-6 lg:grid-cols-3">
                    {{-- Checklist (fitur utama) --}}
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm lg:col-span-2">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-bold text-foreground">Checklist Tas Hari Ini</h2>
                            <span class="text-xs font-medium text-muted-foreground">Otomatis dari RFID</span>
                        </div>

                        <div class="mt-5 flex flex-col gap-1">
                            @forelse ($items as $item)
                                @php $packed = $scannedItemIds->contains($item->id); @endphp
                                <div class="flex items-center gap-3 rounded-xl px-3 py-3 transition-colors duration-300 {{ $packed ? 'bg-primary/5' : '' }}">
                                    {{-- Kotak checklist custom, bukan checkbox asli — statusnya cuma ditampilkan, gak bisa diklik user --}}
                                    <span
                                        class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg border-2 transition-all duration-300 {{ $packed ? 'border-primary bg-primary' : 'border-border bg-background' }}"
                                        aria-hidden="true"
                                    >
                                        @if ($packed)
                                            <x-icon.check class="h-4 w-4 text-primary-foreground" />
                                        @endif
                                    </span>

                                    <x-dynamic-component
                                        :component="'icon.' . ($categoryIcons[$item->category] ?? 'cube')"
                                        class="h-4 w-4 shrink-0 transition-colors duration-300 {{ $packed ? 'text-muted-foreground/50' : 'text-primary' }}"
                                    />

                                    <span
                                        class="text-sm font-medium transition-all duration-300 {{ $packed ? 'text-muted-foreground/60 line-through' : 'text-foreground' }}"
                                    >
                                        {{ $item->name }}
                                    </span>
                                </div>
                            @empty
                                <p class="py-4 text-center text-sm text-muted-foreground">
                                    Belum ada barang terdaftar. Tambahkan dulu di halaman Barang.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    {{-- Progress Card --}}
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <h2 class="text-sm font-semibold text-foreground">Tas Hari Ini</h2>
                        <p class="mt-1 text-2xl font-bold text-foreground">
                            {{ $packedCount }} / {{ $totalItems }} <span class="text-sm font-medium text-muted-foreground">Barang Masuk</span>
                        </p>

                        <div class="mt-4 h-2.5 w-full overflow-hidden rounded-full bg-muted">
                            <div
                                class="h-full rounded-full bg-primary transition-all duration-500 ease-out"
                                style="width: {{ $progress }}%"
                            ></div>
                        </div>
                        <p class="mt-2 text-right text-xs font-semibold text-primary">{{ $progress }}%</p>

                        @if ($progress === 100)
                            <div class="mt-4 flex items-center gap-2 rounded-xl bg-success/10 px-3 py-2.5 text-xs font-medium text-success">
                                <x-icon.check-circle class="h-4 w-4 shrink-0" />
                                Semua barang wajib udah masuk tas kamu.
                            </div>
                        @endif
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
                                        @if ($subject->room)
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon.map-pin class="h-3.5 w-3.5" />
                                                {{ $subject->room }}
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