@php
    // ============================================
    // Variabel di bawah ini DITURUNKAN dari data yang dikirim DashboardController
    // untuk role guru: $user, $todaySubjects (jadwal milik guru ini, hari ini).
    // ============================================

    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $todayName = $dayNames[now()->dayOfWeekIso] ?? 'Senin';

    $monthNames = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $todayFull = $todayName . ', ' . now()->day . ' ' . $monthNames[now()->month] . ' ' . now()->year;

    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $todaySubjects = $todaySubjects ?? collect();

    // Statistik singkat, semua diturunkan langsung dari $todaySubjects — bukan query/data baru.
    $totalClassesToday = $todaySubjects->count();
    $homeworkCount = $todaySubjects->filter(fn($s) => !empty($s->homework))->count();
    $examCount = $todaySubjects->filter(fn($s) => $s->has_exam)->count();

    $currentTimeString = now()->format('H:i:s');
    $nextUpcomingSubject = $todaySubjects->first(fn($s) => (string) $s->start_time >= $currentTimeString);
@endphp

<x-layouts.dashboard title="Dasbor — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="InCase" />

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

                {{-- ============ GREETING ============ --}}
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            {{ $greeting }}, {{ $user->name }}
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            {{ $todayFull }}
                        </p>
                    </div>

                    <a href="{{ route('profile.edit') }}"
                        class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                        @if ($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}"
                                class="h-10 w-10 rounded-full object-cover">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </a>
                </div>

                {{-- ============ STAT CARDS ============ --}}
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.calendar class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Kelas Hari Ini</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $totalClassesToday }}</p>
                    </div>

                    <div class="rounded-[24px] border border-warning/20 bg-warning/5 p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/15 text-warning">
                            <x-icon.document-text class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">PR Diberikan</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $homeworkCount }}</p>
                    </div>

                    <div class="rounded-[24px] border border-destructive/20 bg-destructive/5 p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-destructive/15 text-destructive">
                            <x-icon.exclamation-triangle class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Ujian Hari Ini</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $examCount }}</p>
                    </div>
                </div>

                {{-- ============ JADWAL MENGAJAR HARI INI ============ --}}
                <div class="mt-8">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <x-icon.clock class="h-4 w-4" />
                            </span>
                            <h2 class="text-base font-bold text-foreground sm:text-lg">Jadwal Mengajar Hari Ini</h2>
                            <span class="rounded-full border border-border bg-card px-2.5 py-0.5 text-xs font-semibold text-muted-foreground">
                                {{ $todayName }}
                            </span>
                        </div>

                        <a href="{{ route('subjects.index') }}"
                            class="inline-flex items-center gap-1.5 rounded-full border border-border bg-card px-4 py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted">
                            <x-icon.pencil class="h-3.5 w-3.5" />
                            Kelola Jadwal
                        </a>
                    </div>

                    @if ($nextUpcomingSubject)
                        <p class="mt-2 pl-[42px] text-xs font-medium text-muted-foreground">
                            Kelas berikutnya:
                            <span class="text-primary">{{ $nextUpcomingSubject->name }}</span>
                            pukul {{ \Illuminate\Support\Str::of((string) $nextUpcomingSubject->start_time)->substr(0, 5) }}
                        </p>
                    @endif

                    <div class="mt-4">
                        @if ($todaySubjects->isEmpty())
                            <x-schedule.empty-state
                                class="rounded-3xl border border-dashed border-border bg-card/70 py-10 shadow-sm sm:py-12"
                                title="Gak ada jadwal mengajar hari ini"
                                description="Nikmati harimu, atau cek jadwal hari lain di halaman Jadwal." />
                        @else
                            <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                                @foreach ($todaySubjects as $subject)
                                    @php $isPast = (string) $subject->end_time < $currentTimeString; @endphp
                                    <div class="{{ $isPast ? 'opacity-60' : '' }}">
                                        <x-schedule.subject-card :subject="$subject" :is-teacher="true"
                                            :can-edit="false" :can-delete="false" variant="today"
                                            class="h-full rounded-2xl border border-border bg-card p-4 shadow-sm sm:p-5" />
                                    </div>
                                @endforeach
                            </div>
                            <p class="mt-3 text-xs text-muted-foreground">
                                Mau ubah kelas, PR, atau barang wajib? Klik "Kelola Jadwal" di atas.
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>