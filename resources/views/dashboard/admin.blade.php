@php
    $hour = now()->hour;
    $greeting = match (true) {
        $hour < 11 => 'Selamat pagi',
        $hour < 15 => 'Selamat siang',
        $hour < 18 => 'Selamat sore',
        default => 'Selamat malam',
    };

    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $monthNames = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];
    $todayFull =
        $dayNames[now()->dayOfWeekIso] . ', ' . now()->day . ' ' . $monthNames[now()->month] . ' ' . now()->year;
@endphp

<x-layouts.dashboard title="Dasbor Admin — InCase">
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
                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.identification class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Guru</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $teacherCount }}</p>
                    </div>

                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.user class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Siswa</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $studentCount }}</p>
                    </div>

                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                            <x-icon.academic-cap class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Kelas</p>
                        <p class="mt-1 text-2xl font-bold text-foreground">{{ $classCount }}</p>
                    </div>

                    <div class="rounded-[24px] border border-success/20 bg-success/5 p-6">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-success/15 text-success">
                            <x-icon.calendar class="h-5 w-5" />
                        </span>
                        <p class="mt-4 text-sm text-muted-foreground">Tahun Ajaran Aktif</p>
                        <p class="mt-1 text-lg font-bold text-foreground">
                            {{ $activeYear?->label ?? 'Belum diset' }}
                        </p>
                    </div>
                </div>

                {{-- ============ QUICK LINKS ============ --}}
                <div class="mt-8">
                    <h2 class="text-base font-semibold text-foreground">Kelola</h2>

                    <div class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"> <a
                            href="{{ route('teachers.index') }}"
                           class="group flex flex-col gap-3 rounded-[24px] border border-border bg-card p-6 shadow-sm transition-colors hover:border-primary/30">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <x-icon.identification class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-semibold text-foreground">Akun Guru</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">Tambah & hapus akun guru</p>
                            </div>
                        </a>

                        <a href="{{ route('subjects.index') }}"
                            class="group flex flex-col gap-3 rounded-[24px] border border-border bg-card p-6 shadow-sm transition-colors hover:border-primary/30">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <x-icon.clock class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-semibold text-foreground">Jadwal Pelajaran</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">Tambah & kelola jadwal semua kelas</p>
                            </div>
                        </a>

                        <a href="{{ route('academic-years.index') }}"
                            class="group flex flex-col gap-3 rounded-[24px] border border-border bg-card p-6 shadow-sm transition-colors hover:border-primary/30">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <x-icon.academic-cap class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-semibold text-foreground">Tahun Ajaran</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">Kelola tahun ajaran & semester aktif</p>
                            </div>
                        </a>

                        <a href="{{ route('holidays.index') }}"
                            class="group flex flex-col gap-3 rounded-[24px] border border-border bg-card p-6 shadow-sm transition-colors hover:border-primary/30">
                            <span
                                class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                <x-icon.calendar class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="font-semibold text-foreground">Hari Libur</p>
                                <p class="mt-0.5 text-xs text-muted-foreground">{{ $holidayCount }} hari libur
                                    terdaftar</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>
