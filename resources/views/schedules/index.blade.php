@php
    $dayLabels = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    $englishDayNames = [1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday'];
    $todayDay = $englishDayNames[now()->dayOfWeekIso] ?? 'Monday';

    $todaySubjects = $subjects->where('day', $todayDay)->values();

    $dayCounts = $subjects->groupBy('day')->map->count();

    $isTeacher = auth()->check() && (auth()->user()->role ?? null) === 'teacher';
    $isStudent = auth()->check() && (auth()->user()->role ?? null) === 'student';
    $canAddSchedule = $isTeacher || $isStudent;

    $availableItems = $canAddSchedule
        ? \App\Models\Item::where('user_id', auth()->id())->orderBy('name')->get(['id', 'name'])
        : collect();

    // ============ ADDITIONAL PRESENTATIONAL-ONLY COMPUTATIONS ============
    // Nothing here touches the database, routes, or controllers — these are
    // purely derived from the collections that already exist above, so the
    // underlying logic / data-flow of the page is unchanged.
    $totalJadwal = $subjects->count();
    $totalMataPelajaran = $subjects->pluck('name')->unique()->count();
    $jadwalMingguIni = $subjects->count();
    $jadwalHariIniCount = $todaySubjects->count();

    $currentTimeString = now()->format('H:i:s');
    $nextUpcomingSubject = $todaySubjects->first(function ($s) use ($currentTimeString) {
        return (string) $s->start_time >= $currentTimeString;
    });

    $greetingHour = now()->hour;
    if ($greetingHour < 11) {
        $greetingText = 'Selamat pagi';
    } elseif ($greetingHour < 15) {
        $greetingText = 'Selamat siang';
    } elseif ($greetingHour < 19) {
        $greetingText = 'Selamat sore';
    } else {
        $greetingText = 'Selamat malam';
    }
    $greetingName = auth()->user()->name ?? null;
@endphp

<x-layouts.dashboard title="Jadwal — InCase">
    <style>
        @keyframes schedule-fade-in-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes schedule-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes schedule-toast-in {
            from { opacity: 0; transform: translate(-50%, -12px); }
            to { opacity: 1; transform: translate(-50%, 0); }
        }
        @keyframes schedule-fab-pop {
            from { opacity: 0; transform: scale(0.6); }
            to { opacity: 1; transform: scale(1); }
        }
        @keyframes schedule-shimmer {
            0% { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
        @keyframes schedule-ripple {
            to { transform: scale(3); opacity: 0; }
        }
        .schedule-card-enter {
            animation: schedule-fade-in-up 0.35s ease-out both;
        }
        .schedule-fade-in {
            animation: schedule-fade-in 0.4s ease-out both;
        }
        .schedule-toast-enter {
            animation: schedule-toast-in 0.35s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .schedule-fab-enter {
            animation: schedule-fab-pop 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        .schedule-skeleton {
            background: linear-gradient(90deg, rgba(148,163,184,0.12) 25%, rgba(148,163,184,0.24) 37%, rgba(148,163,184,0.12) 63%);
            background-size: 400px 100%;
            animation: schedule-shimmer 1.4s ease-in-out infinite;
        }
        .schedule-ripple-btn {
            position: relative;
            overflow: hidden;
        }
        .schedule-ripple-span {
            position: absolute;
            border-radius: 9999px;
            background: rgba(255,255,255,0.55);
            transform: scale(0);
            pointer-events: none;
            animation: schedule-ripple 0.6s ease-out;
        }
        .schedule-stat-card:hover {
            transform: translateY(-3px);
        }
        .schedule-subject-wrap {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .schedule-subject-wrap:hover {
            transform: translateY(-3px);
        }
        .schedule-day-tab-track {
            scrollbar-width: none;
        }
        .schedule-day-tab-track::-webkit-scrollbar {
            display: none;
        }
        html {
            scroll-behavior: smooth;
        }
        @media (prefers-reduced-motion: reduce) {
            .schedule-card-enter,
            .schedule-fade-in,
            .schedule-toast-enter,
            .schedule-fab-enter,
            .schedule-skeleton { animation: none; }
            .schedule-stat-card:hover,
            .schedule-subject-wrap:hover { transform: none; }
        }
    </style>

    <div class="flex h-screen bg-slate-50">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="Jadwal — InCase" />

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">

                {{-- ============ FLASH MESSAGE (TOAST) ============ --}}
                @if (session('success'))
                    <div
                        id="schedule-toast"
                        class="schedule-toast-enter fixed left-1/2 top-5 z-[60] flex w-[92%] max-w-sm -translate-x-1/2 items-center gap-3 rounded-2xl border border-emerald-100 bg-white/95 px-4 py-3.5 shadow-[0_12px_32px_-8px_rgba(16,185,129,0.35)] backdrop-blur"
                    >
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <x-icon.check-circle class="h-4.5 w-4.5" />
                        </span>
                        <p class="flex-1 text-sm font-medium text-slate-700">{{ session('success') }}</p>
                        <button type="button" onclick="document.getElementById('schedule-toast')?.remove()" class="shrink-0 rounded-full p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                            <x-icon.x-mark class="h-4 w-4" />
                        </button>
                    </div>
                    <script>
                        setTimeout(function () {
                            var t = document.getElementById('schedule-toast');
                            if (t) {
                                t.style.transition = 'opacity .3s ease, transform .3s ease';
                                t.style.opacity = '0';
                                t.style.transform = 'translate(-50%, -12px)';
                                setTimeout(function () { t.remove(); }, 300);
                            }
                        }, 4000);
                    </script>
                @endif

                {{-- ============ HEADER ============ --}}
                <div class="relative overflow-hidden rounded-[28px] border border-slate-200/70 bg-gradient-to-br from-blue-600 via-blue-600 to-indigo-700 px-6 py-8 shadow-[0_20px_50px_-20px_rgba(37,99,235,0.55)] sm:px-9 sm:py-10">
                    <div class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl"></div>
                    <div class="pointer-events-none absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl"></div>

                    <div class="relative flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-4">
                            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-inner ring-1 ring-white/20 backdrop-blur">
                                <x-icon.calendar class="h-7 w-7" />
                            </span>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wider text-white/70">
                                    {{ $greetingText }}{{ $greetingName ? ', ' . $greetingName : '' }} 👋
                                </p>
                                <h1 class="mt-1 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                                    Jadwal Pelajaran
                                </h1>
                                <p class="mt-1.5 max-w-md text-sm leading-relaxed text-white/75">
                                    Pantau semua kelas, tenggat PR, dan barang wajib kamu dalam satu tampilan yang rapi.
                                </p>
                            </div>
                        </div>

                        @if ($canAddSchedule)
                            <button
                                type="button"
                                onclick="openAddModal()"
                                class="schedule-ripple-btn inline-flex items-center justify-center gap-2 self-start rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-900/20 transition-all hover:-translate-y-0.5 hover:shadow-xl active:scale-[0.97]"
                            >
                                <x-icon.plus class="h-4 w-4" />
                                Tambah Jadwal
                            </button>
                        @endif
                    </div>

                    {{-- ============ DASHBOARD SUMMARY (STAT CARDS) ============ --}}
                    <div class="relative mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur transition-transform">
                            <div class="flex items-center justify-between">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 text-white">
                                    <x-icon.book-open class="h-4.5 w-4.5" />
                                </span>
                            </div>
                            <p class="mt-3 text-2xl font-bold text-white">{{ $totalJadwal }}</p>
                            <p class="text-xs font-medium text-white/70">Total Jadwal</p>
                        </div>

                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur transition-transform" style="animation-delay: 60ms">
                            <div class="flex items-center justify-between">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 text-white">
                                    <x-icon.clock class="h-4.5 w-4.5" />
                                </span>
                            </div>
                            <p class="mt-3 text-2xl font-bold text-white">{{ $jadwalHariIniCount }}</p>
                            <p class="text-xs font-medium text-white/70">Jadwal Hari Ini</p>
                        </div>

                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur transition-transform" style="animation-delay: 120ms">
                            <div class="flex items-center justify-between">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 text-white">
                                    <x-icon.calendar class="h-4.5 w-4.5" />
                                </span>
                            </div>
                            <p class="mt-3 text-2xl font-bold text-white">{{ $jadwalMingguIni }}</p>
                            <p class="text-xs font-medium text-white/70">Jadwal Minggu Ini</p>
                        </div>

                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-4 shadow-sm backdrop-blur transition-transform" style="animation-delay: 180ms">
                            <div class="flex items-center justify-between">
                                <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/15 text-white">
                                    <x-icon.academic-cap class="h-4.5 w-4.5" />
                                </span>
                            </div>
                            <p class="mt-3 text-2xl font-bold text-white">{{ $totalMataPelajaran }}</p>
                            <p class="text-xs font-medium text-white/70">Total Mata Pelajaran</p>
                        </div>
                    </div>
                </div>

                {{-- ============ TODAY'S CLASSES ============ --}}
                <div class="sticky top-0 z-10 -mx-4 mt-8 bg-slate-50/90 px-4 pb-1 pt-2 backdrop-blur sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                            <x-icon.clock class="h-4 w-4" />
                        </span>
                        <h2 class="text-lg font-bold text-slate-900">
                            Kelas Hari Ini
                        </h2>
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-500">
                            {{ $dayLabels[$todayDay] ?? $todayDay }}
                        </span>
                        @if ($todaySubjects->isNotEmpty())
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[11px] font-semibold text-white">
                                {{ $todaySubjects->count() }}
                            </span>
                        @endif
                    </div>

                    @if ($nextUpcomingSubject)
                        <p class="mt-1 pl-[42px] text-xs font-medium text-slate-500">
                            Kelas berikutnya:
                            <span class="text-blue-600">{{ $nextUpcomingSubject->name }}</span>
                            pukul {{ \Illuminate\Support\Str::of((string) $nextUpcomingSubject->start_time)->substr(0, 5) }}
                        </p>
                    @endif
                </div>

                <div class="mt-3">
                    @if ($todaySubjects->isEmpty())
                        <x-schedule.empty-state
                            class="schedule-fade-in mt-1 rounded-3xl border border-dashed border-slate-200 bg-white/70 py-12 shadow-sm"
                            title="Gak ada kelas hari ini"
                            description="Nikmati harimu, atau cek jadwal hari lain di bawah."
                        />
                    @else
                        <div class="grid gap-4 sm:grid-cols-2">
                            @foreach ($todaySubjects as $subject)
                                @php
                                    $isPast = (string) $subject->end_time < $currentTimeString;
                                @endphp
                                <div class="schedule-subject-wrap schedule-card-enter rounded-2xl {{ $isPast ? 'opacity-60' : '' }}" style="animation-delay: {{ $loop->index * 40 }}ms">
                                    <x-schedule.subject-card
                                        :subject="$subject"
                                        :is-teacher="$isTeacher"
                                        variant="today"
                                        class="h-full rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-shadow hover:shadow-[0_16px_32px_-16px_rgba(15,23,42,0.18)]"
                                    />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ============ WEEKLY SCHEDULE ============ --}}
                <div class="mt-10">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                            <x-icon.calendar class="h-4 w-4" />
                        </span>
                        <h2 class="text-lg font-bold text-slate-900">Jadwal Mingguan</h2>
                    </div>

                    <div class="schedule-day-tab-track sticky top-0 z-10 mt-4 flex gap-1.5 overflow-x-auto rounded-full border border-slate-200 bg-white p-1.5 shadow-sm">
                        @foreach ($dayLabels as $dayValue => $dayLabel)
                            <button
                                type="button"
                                onclick="switchScheduleDay('{{ $dayValue }}', this)"
                                data-day="{{ $dayValue }}"
                                class="day-tab inline-flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition-all duration-200 {{ $dayValue === $todayDay ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-primary-foreground shadow-md shadow-blue-600/25' : 'border border-border bg-card text-muted-foreground hover:bg-slate-50 hover:text-foreground' }}"
                            >
                                {{ $dayLabel }}
                                @if (($dayCounts[$dayValue] ?? 0) > 0)
                                    <span class="text-[11px] font-medium opacity-70">
                                        {{ $dayCounts[$dayValue] }}
                                    </span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    @foreach ($dayLabels as $dayValue => $dayLabel)
                        <div
                            id="schedule-day-{{ $dayValue }}"
                            class="schedule-day-panel mt-5 flex flex-col gap-4 {{ $dayValue === $todayDay ? '' : 'hidden' }}"
                        >
                            @php
                                $dayItems = $subjects->where('day', $dayValue)->values();
                            @endphp

                            @if ($dayItems->isEmpty())
                                <x-schedule.empty-state
                                    class="schedule-fade-in rounded-3xl border border-dashed border-slate-200 bg-white/70 py-12 shadow-sm"
                                    title="Belum ada jadwal"
                                    :description="$canAddSchedule
                                        ? 'Tambahkan jadwal buat hari ' . $dayLabel . '.'
                                        : 'Belum ada kelas yang dijadwalkan di hari ' . $dayLabel . '.'"
                                />
                            @else
                                @foreach ($dayItems as $subject)
                                    <div class="schedule-subject-wrap schedule-card-enter rounded-2xl" style="animation-delay: {{ $loop->index * 40 }}ms">
                                        <x-schedule.subject-card
                                            :subject="$subject"
                                            :is-teacher="$isTeacher"
                                            variant="weekly"
                                            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-shadow hover:shadow-[0_16px_32px_-16px_rgba(15,23,42,0.18)]"
                                        />
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- spacer so content doesn't hide behind the FAB on mobile --}}
                @if ($canAddSchedule)
                    <div class="h-20 sm:h-4"></div>
                @endif
            </div>
        </main>
    </div>

    {{-- ============ FLOATING ACTION BUTTON ============ --}}
    @if ($canAddSchedule)
        <button
            type="button"
            onclick="openAddModal()"
            title="Tambah Jadwal"
            class="schedule-ripple-btn schedule-fab-enter fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-[0_16px_32px_-8px_rgba(37,99,235,0.55)] transition-transform hover:-translate-y-0.5 active:scale-95 sm:hidden"
        >
            <x-icon.plus class="h-6 w-6" />
        </button>
    @endif

    {{-- ============ ADD SCHEDULE DRAWER (buat teacher & student) ============ --}}
    @if ($canAddSchedule)
        <div id="add-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
            <div onclick="closeModal('add-subject-modal')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <div class="modal-panel drawer-panel fixed inset-y-0 right-0 z-10 flex h-full w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300 ease-out sm:rounded-l-[28px]">
                <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 px-6 py-5 sm:px-8">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <x-icon.plus class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-slate-900">Tambah Jadwal</h3>
                        <p class="text-xs text-slate-400">Isi detail kelas baru kamu</p>
                    </div>
                    <button type="button" onclick="closeModal('add-subject-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form id="add-subject-form" method="POST" action="{{ route('subjects.store') }}" class="schedule-form flex flex-1 flex-col overflow-hidden">
                    @csrf
                    <input type="hidden" name="is_active" value="1">

                    <div class="flex flex-1 flex-col gap-6 overflow-y-auto scrollbar-none px-6 py-6 sm:px-8">
                        @if ($errors->any())
                            <div class="flex items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
                                <x-icon.exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" />
                                <div>
                                    <p class="font-semibold">Ada {{ $errors->count() }} kesalahan pada form:</p>
                                    <ul class="mt-1 list-disc space-y-0.5 pl-4">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Detail Pelajaran</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Pelajaran</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.book-open class="h-4 w-4" />
                                        </span>
                                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Matematika" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm placeholder:text-muted-foreground/70 transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('name') border-destructive @enderror">
                                    </div>
                                    @error('name')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                            <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.academic-cap class="h-4 w-4" />
                                        </span>
                                        <select name="class_id" class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                                            <option value="" disabled selected>Pilih kelas</option>
                                            @foreach ($classes as $class)
                                                <option value="{{ $class->id }}" @selected((string) old('class_id') === (string) $class->id)>
                                                    {{ $class->grade }} {{ $class->major }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('class_id')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.map-pin class="h-4 w-4" />
                                        </span>
                                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: A203" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm placeholder:text-muted-foreground/70 transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('location') border-destructive @enderror">
                                    </div>
                                    @error('location')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                            <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Waktu</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.calendar class="h-4 w-4" />
                                        </span>
                                        <select name="day" class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('day') border-destructive @enderror">
                                            @foreach ($dayLabels as $dayValue => $dayLabel)
                                                <option value="{{ $dayValue }}" @selected(old('day') === $dayValue)>{{ $dayLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('day')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                            <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Mulai</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                                <x-icon.clock class="h-4 w-4" />
                                            </span>
                                            <input type="time" name="start_time" value="{{ old('start_time') }}" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('start_time') border-destructive @enderror">
                                        </div>
                                        @error('start_time')
                                            <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                                <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                                <x-icon.clock class="h-4 w-4" />
                                            </span>
                                            <input type="time" name="end_time" value="{{ old('end_time') }}" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('end_time') border-destructive @enderror">
                                        </div>
                                        @error('end_time')
                                            <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                                <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                                {{ $message }}
                                            </p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Tambahan</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">PR (opsional)</label>
                                    <textarea name="homework" rows="2" placeholder="Contoh: Kerjakan halaman 42" class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground shadow-sm placeholder:text-muted-foreground/70 transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('homework') border-destructive @enderror">{{ old('homework') }}</textarea>
                                    @error('homework')
                                        <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                            <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                            {{ $message }}
                                        </p>
                                    @enderror
                                </div>

                                <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-3 transition-colors hover:bg-slate-50">
                                    <input type="checkbox" name="has_exam" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30" @checked(old('has_exam'))>
                                    <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <div class="mb-3 flex items-center justify-between">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Barang Wajib</p>
                                <span class="add-items-selected-count text-xs font-semibold text-blue-600">0 dipilih</span>
                            </div>
                            <div class="flex flex-col divide-y divide-border overflow-hidden rounded-xl border border-border">
                                @forelse ($availableItems as $item)
                                    <label class="item-checkbox-row flex cursor-pointer items-center gap-3 px-3.5 py-3 transition-colors hover:bg-muted">
                                        <span class="item-checkbox-box flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-slate-300 text-white transition-colors">
                                            <x-icon.check-circle class="pointer-events-none h-3.5 w-3.5 opacity-0" />
                                        </span>
                                        <input type="checkbox" name="items[]" value="{{ $item->id }}" class="item-checkbox sr-only" @checked(collect(old('items'))->contains($item->id))>
                                        <x-icon.archive-box class="h-4 w-4 shrink-0 text-slate-400" />
                                        <span class="text-sm text-foreground">{{ $item->name }}</span>
                                    </label>
                                @empty
                                    <p class="px-3.5 py-4 text-center text-sm text-muted-foreground">
                                        Belum ada barang terdaftar. Tambahkan dulu di halaman Barang.
                                    </p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="flex shrink-0 items-center gap-3 border-t border-slate-100 bg-white px-6 py-5 sm:px-8">
                        <button type="button" onclick="closeModal('add-subject-modal')" class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit" class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 py-3 text-sm font-semibold text-primary-foreground shadow-md shadow-blue-600/25 transition-all hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============ EDIT & DELETE MODALS (khusus teacher) ============ --}}
    @if ($isTeacher)
        <div id="edit-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('edit-subject-modal')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-lg scale-95 rounded-[28px] bg-white p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
                <div class="flex items-center gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <x-icon.pencil class="h-5 w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-foreground">
                            Edit Jadwal
                            <span id="edit-modal-loading" class="hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
                        </h3>
                        <p class="text-xs text-slate-400">Perbarui detail jadwal ini</p>
                    </div>
                    <button type="button" onclick="closeModal('edit-subject-modal')" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div id="edit-modal-error" class="mt-4 hidden items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
                    <x-icon.exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Gagal ambil data jadwal. Coba lagi.</span>
                </div>

                <form id="edit-subject-form" method="POST" action="" class="schedule-form mt-6 flex max-h-[70vh] flex-col gap-5 overflow-y-auto scrollbar-none pr-1 transition-opacity duration-150">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_active" value="1">

                    @if ($errors->any())
                        <div class="flex items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
                            <x-icon.exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" />
                            <div>
                                <p class="font-semibold">Ada {{ $errors->count() }} kesalahan pada form:</p>
                                <ul class="mt-1 list-disc space-y-0.5 pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Pelajaran</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                <x-icon.book-open class="h-4 w-4" />
                            </span>
                            <input type="text" name="name" id="edit-name" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        @error('name')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                <x-icon.academic-cap class="h-4 w-4" />
                            </span>
                            <select name="class_id" id="edit-class_id" class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                                <option value="" disabled>Pilih kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->grade }} {{ $class->major }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('class_id')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                <x-icon.map-pin class="h-4 w-4" />
                            </span>
                            <input type="text" name="location" id="edit-location" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        @error('location')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                <x-icon.calendar class="h-4 w-4" />
                            </span>
                            <select name="day" id="edit-day" class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                                @foreach ($dayLabels as $dayValue => $dayLabel)
                                    <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('day')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Mulai</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                    <x-icon.clock class="h-4 w-4" />
                                </span>
                                <input type="time" name="start_time" id="edit-start_time" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            @error('start_time')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                    <x-icon.clock class="h-4 w-4" />
                                </span>
                                <input type="time" name="end_time" id="edit-end_time" class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            @error('end_time')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">PR (opsional)</label>
                        <textarea name="homework" id="edit-homework" rows="2" class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"></textarea>
                        @error('homework')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-3 transition-colors hover:bg-slate-50">
                        <input type="checkbox" name="has_exam" id="edit-has_exam" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
                        <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                    </label>

                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <label class="block text-sm font-medium text-foreground">Barang Wajib</label>
                            <span class="edit-items-selected-count text-xs font-semibold text-blue-600">0 dipilih</span>
                        </div>
                        <div class="flex flex-col divide-y divide-border overflow-hidden rounded-xl border border-border">
                            @forelse ($availableItems as $item)
                                <label class="item-checkbox-row flex cursor-pointer items-center gap-3 px-3.5 py-3 transition-colors hover:bg-muted">
                                    <span class="item-checkbox-box flex h-5 w-5 shrink-0 items-center justify-center rounded-md border border-slate-300 text-white transition-colors">
                                        <x-icon.check-circle class="pointer-events-none h-3.5 w-3.5 opacity-0" />
                                    </span>
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" class="edit-item-checkbox item-checkbox sr-only">
                                    <x-icon.archive-box class="h-4 w-4 shrink-0 text-slate-400" />
                                    <span class="text-sm text-foreground">{{ $item->name }}</span>
                                </label>
                            @empty
                                <p class="px-3.5 py-4 text-center text-sm text-muted-foreground">
                                    Belum ada barang terdaftar. Tambahkan dulu di halaman Barang.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-2 flex items-center gap-3">
                        <button type="button" onclick="closeModal('edit-subject-modal')" class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit" class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 py-3 text-sm font-semibold text-primary-foreground shadow-md shadow-blue-600/25 transition-all hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="delete-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('delete-subject-modal')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-sm scale-95 rounded-[28px] bg-white p-6 text-center opacity-0 shadow-2xl transition-all duration-200 ease-out">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive">
                    <x-icon.trash class="h-7 w-7" />
                </span>

                <h3 class="mt-4 text-lg font-bold text-foreground">Hapus jadwal ini?</h3>
                <p id="delete-subject-name" class="mt-2 text-sm leading-relaxed text-muted-foreground"></p>

                <form id="delete-subject-form" method="POST" action="" class="schedule-form mt-6 flex items-center gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button" onclick="closeModal('delete-subject-modal')" class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                        Batal
                    </button>
                    <button type="submit" class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-destructive py-3 text-sm font-semibold text-white transition-all hover:bg-destructive/90 disabled:cursor-not-allowed disabled:opacity-70">
                        <span class="btn-label">Ya, Hapus</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <script>
        function switchScheduleDay(day, button) {
            document.querySelectorAll('.schedule-day-panel').forEach(function (panel) {
                panel.classList.add('hidden');
            });
            document.getElementById('schedule-day-' + day).classList.remove('hidden');

            document.querySelectorAll('.day-tab').forEach(function (tab) {
                tab.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-primary-foreground', 'shadow-md', 'shadow-blue-600/25');
                tab.classList.add('border', 'border-border', 'bg-card', 'text-muted-foreground');
            });
            button.classList.remove('border', 'border-border', 'bg-card', 'text-muted-foreground');
            button.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-primary-foreground', 'shadow-md', 'shadow-blue-600/25');
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            const panel = modal.querySelector('.modal-panel');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            requestAnimationFrame(function () {
                if (!panel) return;
                if (panel.classList.contains('drawer-panel')) {
                    panel.classList.remove('translate-x-full');
                    panel.classList.add('translate-x-0');
                } else {
                    panel.classList.remove('opacity-0', 'scale-95');
                    panel.classList.add('opacity-100', 'scale-100');
                }
            });
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            const panel = modal.querySelector('.modal-panel');

            if (panel) {
                if (panel.classList.contains('drawer-panel')) {
                    panel.classList.remove('translate-x-0');
                    panel.classList.add('translate-x-full');
                } else {
                    panel.classList.remove('opacity-100', 'scale-100');
                    panel.classList.add('opacity-0', 'scale-95');
                }
            }

            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function openAddModal() {
            openModal('add-subject-modal');
        }

        function openEditModal(button) {
            const form = document.getElementById('edit-subject-form');
            if (!form) return;

            const id = button.dataset.id;
            const loadingIndicator = document.getElementById('edit-modal-loading');
            const errorBanner = document.getElementById('edit-modal-error');

            errorBanner.classList.add('hidden');
            errorBanner.classList.remove('flex');
            form.classList.add('opacity-40', 'pointer-events-none');
            loadingIndicator.classList.remove('hidden');

            openModal('edit-subject-modal');

            fetch('/subjects/' + id + '/edit')
                .then(function (response) { return response.json(); })
                .then(function (subject) {
                    document.getElementById('edit-name').value = subject.name ?? '';
                    document.getElementById('edit-location').value = subject.location ?? '';
                    document.getElementById('edit-day').value = subject.day ?? '';
                    document.getElementById('edit-class_id').value = subject.class_id ?? '';
                    document.getElementById('edit-start_time').value = (subject.start_time ?? '').toString().slice(0, 5);
                    document.getElementById('edit-end_time').value = (subject.end_time ?? '').toString().slice(0, 5);
                    document.getElementById('edit-homework').value = subject.homework ?? '';
                    document.getElementById('edit-has_exam').checked = !!subject.has_exam;

                    const assignedIds = (subject.items || []).map(function (item) { return item.id; });
                    document.querySelectorAll('.edit-item-checkbox').forEach(function (checkbox) {
                        checkbox.checked = assignedIds.includes(Number(checkbox.value));
                    });

                    document.querySelectorAll('.item-checkbox').forEach(function (cb) {
                        cb.dispatchEvent(new Event('change'));
                    });

                    form.action = '/subjects/' + id;
                })
                .catch(function () {
                    errorBanner.classList.remove('hidden');
                    errorBanner.classList.add('flex');
                })
                .finally(function () {
                    form.classList.remove('opacity-40', 'pointer-events-none');
                    loadingIndicator.classList.add('hidden');
                });
        }

        function openDeleteModal(button) {
            const form = document.getElementById('delete-subject-form');
            if (!form) return;

            document.getElementById('delete-subject-name').textContent =
                'Jadwal "' + button.dataset.name + '" akan dihapus permanen dan gak bisa dibatalin.';
            form.action = '/subjects/' + button.dataset.id;
            openModal('delete-subject-modal');
        }

        document.querySelectorAll('.item-checkbox').forEach(function (checkbox) {
            const row = checkbox.closest('.item-checkbox-row');
            const box = row ? row.querySelector('.item-checkbox-box') : null;
            const boxIcon = box ? box.querySelector('svg') : null;

            const sync = function () {
                if (row) row.classList.toggle('bg-blue-50/60', checkbox.checked);
                if (box) {
                    box.classList.toggle('bg-blue-600', checkbox.checked);
                    box.classList.toggle('border-blue-600', checkbox.checked);
                }
                if (boxIcon) boxIcon.classList.toggle('opacity-0', !checkbox.checked);

                const scope = row ? row.closest('form') : null;
                if (scope) {
                    const count = scope.querySelectorAll('.item-checkbox:checked').length;
                    const counter = scope.querySelector('.add-items-selected-count, .edit-items-selected-count');
                    if (counter) counter.textContent = count + ' dipilih';
                }
            };
            checkbox.addEventListener('change', sync);
            sync();
        });

        document.querySelectorAll('form.schedule-form').forEach(function (form) {
            form.addEventListener('submit', function () {
                const btn = form.querySelector('.submit-btn');
                if (!btn || btn.disabled) return;

                btn.disabled = true;
                const label = btn.querySelector('.btn-label');
                if (label) {
                    label.dataset.original = label.textContent;
                    label.textContent = 'Memproses...';
                }
                btn.insertAdjacentHTML('afterbegin', '<span class="h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent"></span>');
            });
        });

        document.querySelectorAll('.schedule-ripple-btn').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const span = document.createElement('span');
                span.className = 'schedule-ripple-span';
                span.style.width = span.style.height = size + 'px';
                span.style.left = (e.clientX - rect.left - size / 2) + 'px';
                span.style.top = (e.clientY - rect.top - size / 2) + 'px';
                btn.appendChild(span);
                setTimeout(function () { span.remove(); }, 600);
            });
        });

        (function () {
            const addForm = document.getElementById('add-subject-form');
            const editForm = document.getElementById('edit-subject-form');

            if (addForm) {
                addForm.addEventListener('submit', function () {
                    sessionStorage.setItem('schedule_form_context', JSON.stringify({ type: 'add' }));
                });
            }

            if (editForm) {
                editForm.addEventListener('submit', function () {
                    const action = editForm.action || '';
                    const id = action.substring(action.lastIndexOf('/') + 1);
                    sessionStorage.setItem('schedule_form_context', JSON.stringify({ type: 'edit', id: id }));
                });
            }

            @if ($errors->any())
                try {
                    const ctx = JSON.parse(sessionStorage.getItem('schedule_form_context') || 'null');
                    if (ctx && ctx.type === 'edit' && ctx.id && editForm) {
                        editForm.action = '/subjects/' + ctx.id;
                        openModal('edit-subject-modal');
                    } else if (addForm) {
                        openModal('add-subject-modal');
                    }
                } catch (e) {
                    if (addForm) openModal('add-subject-modal');
                } finally {
                    sessionStorage.removeItem('schedule_form_context');
                }
            @endif
        })();
    </script>
</x-layouts.dashboard>