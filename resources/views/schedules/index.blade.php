@php
    $teachers = $teachers ?? collect();
    $unreadNotifications = $unreadNotifications ?? collect();

    $allDayLabels = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];

    $dayLabels = collect($allDayLabels)
        ->only($schoolDayNames ?? array_keys($allDayLabels))
        ->all();

    $englishDayNames = [
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday',
    ];
    $todayDay = $englishDayNames[now()->dayOfWeekIso] ?? 'Monday';

    $todaySubjects = $subjects->where('day', $todayDay)->values();
    $dayCounts = $subjects->groupBy('day')->map->count();

    $authUser = auth()->user();
    $role = $authUser->role ?? null;
    $isAdmin = $role === 'admin';
    $isTeacher = $role === 'teacher';
    $isStudent = $role === 'student';

    $canAddSchedule = $isAdmin; // hanya admin yang bisa tambah
    $canEdit = $isAdmin || $isTeacher; // admin & guru bisa edit
    $canDelete = $isAdmin; // hanya admin yang bisa hapus

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
    $greetingName = $authUser->name ?? null;

    $classesJson = $classes
        ->map(
            fn($class) => [
                'id' => $class->id,
                'grade' => $class->grade,
                'major' => $class->major,
                'label' => $class->name,
            ],
        )
        ->values();

    $inputBase =
        'block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground shadow-sm transition-colors focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10';
@endphp

<x-layouts.dashboard title="Jadwal — InCase">
    <style>
        @keyframes schedule-fade-in-up {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes schedule-fade-in {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes schedule-toast-in {
            from {
                opacity: 0;
                transform: translate(-50%, -12px);
            }

            to {
                opacity: 1;
                transform: translate(-50%, 0);
            }
        }

        @keyframes schedule-fab-pop {
            from {
                opacity: 0;
                transform: scale(0.6);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes schedule-ripple {
            to {
                transform: scale(3);
                opacity: 0;
            }
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

        .schedule-ripple-btn {
            position: relative;
            overflow: hidden;
        }

        .schedule-ripple-span {
            position: absolute;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.55);
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
            .schedule-fab-enter {
                animation: none;
            }

            .schedule-stat-card:hover,
            .schedule-subject-wrap:hover {
                transform: none;
            }
        }
    </style>

    <div class="flex h-screen bg-slate-50">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="Jadwal — InCase" />

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">

                {{-- ============ FLASH MESSAGE (TOAST) ============ --}}
                @if (session('success'))
                    <div id="schedule-toast"
                        class="schedule-toast-enter fixed left-1/2 top-5 z-[60] flex w-[92%] max-w-sm -translate-x-1/2 items-center gap-3 rounded-2xl border border-emerald-100 bg-white/95 px-4 py-3.5 shadow-[0_12px_32px_-8px_rgba(16,185,129,0.35)] backdrop-blur">
                        <span
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <x-icon.check-circle class="h-4.5 w-4.5" />
                        </span>
                        <p class="flex-1 text-sm font-medium text-slate-700">{{ session('success') }}</p>
                        <button type="button" onclick="document.getElementById('schedule-toast')?.remove()"
                            class="shrink-0 rounded-full p-1 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                            <x-icon.x-mark class="h-4 w-4" />
                        </button>
                    </div>
                    <script>
                        setTimeout(function() {
                            var t = document.getElementById('schedule-toast');
                            if (t) {
                                t.style.transition = 'opacity .3s ease, transform .3s ease';
                                t.style.opacity = '0';
                                t.style.transform = 'translate(-50%, -12px)';
                                setTimeout(function() {
                                    t.remove();
                                }, 300);
                            }
                        }, 4000);
                    </script>
                @endif

                {{-- ============ NOTIFIKASI UNTUK SISWA ============ --}}
                @if ($isStudent && $unreadNotifications->isNotEmpty())
                    <div class="schedule-fade-in mb-6 rounded-3xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="text-sm font-bold text-amber-800">
                                🔔 {{ $unreadNotifications->count() }} pembaruan jadwal
                            </p>
                            <form method="POST" action="{{ route('notifications.read') }}">
                                @csrf
                                <button type="submit" class="text-xs font-semibold text-amber-700 hover:underline">
                                    Tandai sudah dibaca
                                </button>
                            </form>
                        </div>
                        <ul class="mt-3 space-y-2">
                            @foreach ($unreadNotifications as $notification)
                                <li class="rounded-xl bg-white/80 px-3.5 py-2.5 text-sm text-slate-700">
                                    {{ $notification->data['message'] ?? 'Ada pembaruan jadwal.' }}
                                    <span class="block text-[11px] text-slate-400">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- ============ HEADER ============ --}}
                <div
                    class="relative overflow-hidden rounded-[28px] border border-slate-200/70 bg-gradient-to-br from-blue-600 via-blue-600 to-indigo-700 px-5 py-7 shadow-[0_20px_50px_-20px_rgba(37,99,235,0.55)] sm:px-9 sm:py-10">
                    <div
                        class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-white/10 blur-2xl">
                    </div>
                    <div
                        class="pointer-events-none absolute -bottom-20 left-1/3 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl">
                    </div>

                    <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex items-start gap-3 sm:gap-4">
                            <span
                                class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-white shadow-inner ring-1 ring-white/20 backdrop-blur sm:h-14 sm:w-14">
                                <x-icon.calendar class="h-5 w-5 sm:h-7 sm:w-7" />
                            </span>
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-white/70 sm:text-xs">
                                    {{ $greetingText }}{{ $greetingName ? ', ' . $greetingName : '' }} 👋
                                </p>
                                <h1 class="mt-1 text-xl font-bold tracking-tight text-white sm:text-2xl lg:text-3xl">
                                    Jadwal Pelajaran
                                </h1>
                                <p class="mt-1.5 max-w-md text-xs leading-relaxed text-white/75 sm:text-sm">
                                    Pantau semua kelas, tenggat PR, dan barang wajib kamu dalam satu tampilan yang rapi.
                                </p>
                            </div>
                        </div>

                        @if ($canAddSchedule)
                            <div class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row">

                                {{-- Cetak Jadwal --}}
                                <select
                                    onchange="if (this.value) window.open(this.value, '_blank'); this.selectedIndex = 0;"
                                    class="inline-flex w-full cursor-pointer items-center justify-center rounded-full border border-white/30 bg-white/10 px-5 py-2.5 text-sm font-semibold text-white outline-none transition-colors hover:bg-white/20 sm:w-auto">
                                    <option value="" selected disabled class="text-gray-800">
                                        Cetak Jadwal
                                    </option>

                                    @foreach ($classes as $class)
                                        <option value="{{ route('subjects.print', ['schoolClass' => $class->id]) }}"
                                            class="text-gray-800">
                                            {{ $class->name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Tambah Jadwal --}}
                                <button type="button" onclick="openAddModal()"
                                    class="schedule-ripple-btn inline-flex w-full items-center justify-center gap-2 self-start rounded-full bg-white px-5 py-2.5 text-sm font-semibold text-blue-700 shadow-lg shadow-blue-900/20 transition-all hover:-translate-y-0.5 hover:shadow-xl active:scale-[0.97] sm:w-auto">
                                    <x-icon.plus class="h-4 w-4" />
                                    Tambah Jadwal
                                </button>

                            </div>
                        @endif
                    </div>

                    {{-- ============ STAT CARDS ============ --}}
                    <div class="relative mt-6 grid grid-cols-2 gap-2.5 sm:mt-8 sm:gap-4">
                        <div
                            class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-3.5 shadow-sm backdrop-blur transition-transform sm:p-4">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white sm:h-9 sm:w-9">
                                <x-icon.book-open class="h-4 w-4" />
                            </span>
                            <p class="mt-2.5 text-xl font-bold text-white sm:mt-3 sm:text-2xl">{{ $totalJadwal }}</p>
                            <p class="text-[11px] font-medium text-white/70 sm:text-xs">Total Jadwal</p>
                        </div>

                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-3.5 shadow-sm backdrop-blur transition-transform sm:p-4"
                            style="animation-delay: 60ms">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white sm:h-9 sm:w-9">
                                <x-icon.clock class="h-4 w-4" />
                            </span>
                            <p class="mt-2.5 text-xl font-bold text-white sm:mt-3 sm:text-2xl">
                                {{ $jadwalHariIniCount }}</p>
                            <p class="text-[11px] font-medium text-white/70 sm:text-xs">Jadwal Hari Ini</p>
                        </div>

                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-3.5 shadow-sm backdrop-blur transition-transform sm:p-4"
                            style="animation-delay: 120ms">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white sm:h-9 sm:w-9">
                                <x-icon.calendar class="h-4 w-4" />
                            </span>
                            <p class="mt-2.5 text-xl font-bold text-white sm:mt-3 sm:text-2xl">{{ $jadwalMingguIni }}
                            </p>
                            <p class="text-[11px] font-medium text-white/70 sm:text-xs">Jadwal Minggu Ini</p>
                        </div>

                        <div class="schedule-stat-card schedule-card-enter rounded-2xl border border-white/15 bg-white/10 p-3.5 shadow-sm backdrop-blur transition-transform sm:p-4"
                            style="animation-delay: 180ms">
                            <span
                                class="flex h-8 w-8 items-center justify-center rounded-xl bg-white/15 text-white sm:h-9 sm:w-9">
                                <x-icon.academic-cap class="h-4 w-4" />
                            </span>
                            <p class="mt-2.5 text-xl font-bold text-white sm:mt-3 sm:text-2xl">
                                {{ $totalMataPelajaran }}</p>
                            <p class="text-[11px] font-medium text-white/70 sm:text-xs">Total Mapel</p>
                        </div>
                    </div>
                </div>

                {{-- ============ TODAY'S CLASSES ============ --}}
                <div
                    class="sticky top-0 z-10 -mx-4 mt-6 bg-slate-50/90 px-4 pb-1 pt-2 backdrop-blur sm:mt-8 sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-8">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-50 text-blue-600 sm:h-8 sm:w-8">
                            <x-icon.clock class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                        </span>
                        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Kelas Hari Ini</h2>
                        <span
                            class="rounded-full border border-slate-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-slate-500">
                            {{ $dayLabels[$todayDay] ?? $todayDay }}
                        </span>
                        @if ($todaySubjects->isNotEmpty())
                            <span
                                class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-blue-600 px-1.5 text-[11px] font-semibold text-white">
                                {{ $todaySubjects->count() }}
                            </span>
                        @endif
                    </div>

                    @if ($nextUpcomingSubject)
                        <p class="mt-1 pl-[38px] text-xs font-medium text-slate-500 sm:pl-[42px]">
                            Kelas berikutnya:
                            <span class="text-blue-600">{{ $nextUpcomingSubject->name }}</span>
                            pukul
                            {{ \Illuminate\Support\Str::of((string) $nextUpcomingSubject->start_time)->substr(0, 5) }}
                        </p>
                    @endif
                </div>

                <div class="mt-3">
                    @if ($todaySubjects->isEmpty())
                        <x-schedule.empty-state
                            class="schedule-fade-in mt-1 rounded-3xl border border-dashed border-slate-200 bg-white/70 py-10 shadow-sm sm:py-12"
                            title="Gak ada kelas hari ini"
                            description="Nikmati harimu, atau cek jadwal hari lain di bawah." />
                    @else
                        <div class="grid gap-3 sm:grid-cols-2 sm:gap-4">
                            @foreach ($todaySubjects as $subject)
                                @php
                                    $isPast = (string) $subject->end_time < $currentTimeString;
                                @endphp
                                <div class="schedule-subject-wrap schedule-card-enter rounded-2xl {{ $isPast ? 'opacity-60' : '' }}"
                                    style="animation-delay: {{ $loop->index * 40 }}ms">
                                    <x-schedule.subject-card :subject="$subject" :is-teacher="$isTeacher" :can-edit="$canEdit"
                                        :can-delete="$canDelete" variant="today"
                                        class="h-full rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-shadow hover:shadow-[0_16px_32px_-16px_rgba(15,23,42,0.18)] sm:p-5" />
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ============ WEEKLY SCHEDULE ============ --}}
                <div class="mt-8 sm:mt-10">
                    <div class="flex items-center gap-2 sm:gap-2.5">
                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 sm:h-8 sm:w-8">
                            <x-icon.calendar class="h-3.5 w-3.5 sm:h-4 sm:w-4" />
                        </span>
                        <h2 class="text-base font-bold text-slate-900 sm:text-lg">Jadwal Mingguan</h2>
                    </div>

                    <div
                        class="schedule-day-tab-track sticky top-0 z-10 mt-3 flex gap-1.5 overflow-x-auto rounded-full border border-slate-200 bg-white p-1.5 shadow-sm sm:mt-4">
                        @foreach ($dayLabels as $dayValue => $dayLabel)
                            <button type="button" onclick="switchScheduleDay('{{ $dayValue }}', this)"
                                data-day="{{ $dayValue }}"
                                class="day-tab inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-xs font-semibold transition-all duration-200 sm:px-4 sm:text-sm {{ $dayValue === $todayDay ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-primary-foreground shadow-md shadow-blue-600/25' : 'border border-border bg-card text-muted-foreground hover:bg-slate-50 hover:text-foreground' }}">
                                {{ $dayLabel }}
                                @if (($dayCounts[$dayValue] ?? 0) > 0)
                                    <span class="text-[11px] font-medium opacity-70">{{ $dayCounts[$dayValue] }}</span>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    @foreach ($dayLabels as $dayValue => $dayLabel)
                        <div id="schedule-day-{{ $dayValue }}"
                            class="schedule-day-panel mt-4 flex flex-col gap-3 sm:mt-5 sm:gap-4 {{ $dayValue === $todayDay ? '' : 'hidden' }}">
                            @php
                                $dayItems = $subjects->where('day', $dayValue)->values();
                            @endphp

                            @if ($dayItems->isEmpty())
                                <x-schedule.empty-state
                                    class="schedule-fade-in rounded-3xl border border-dashed border-slate-200 bg-white/70 py-10 shadow-sm sm:py-12"
                                    title="Belum ada jadwal" :description="$canAddSchedule
                                        ? 'Tambahkan jadwal buat hari ' . $dayLabel . '.'
                                        : 'Belum ada kelas yang dijadwalkan di hari ' . $dayLabel . '.'" />
                            @else
                                @foreach ($dayItems as $subject)
                                    <div class="schedule-subject-wrap schedule-card-enter rounded-2xl"
                                        style="animation-delay: {{ $loop->index * 40 }}ms">
                                        <x-schedule.subject-card :subject="$subject" :is-teacher="$isTeacher" :can-edit="$canEdit"
                                            :can-delete="$canDelete" variant="weekly"
                                            class="rounded-2xl border border-slate-200 bg-white p-4 shadow-[0_1px_2px_rgba(15,23,42,0.04)] transition-shadow hover:shadow-[0_16px_32px_-16px_rgba(15,23,42,0.18)] sm:p-5" />
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($canAddSchedule)
                    <div class="h-20 sm:h-4"></div>
                @endif
            </div>
        </main>
    </div>

    {{-- ============ FLOATING ACTION BUTTON (ADMIN) ============ --}}
    @if ($canAddSchedule)
        <button type="button" onclick="openAddModal()" title="Tambah Jadwal"
            class="schedule-ripple-btn schedule-fab-enter fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-[0_16px_32px_-8px_rgba(37,99,235,0.55)] transition-transform hover:-translate-y-0.5 active:scale-95 sm:hidden">
            <x-icon.plus class="h-6 w-6" />
        </button>
    @endif

    {{-- ============ ADD SCHEDULE DRAWER (ADMIN) ============ --}}
    @if ($canAddSchedule)
        <div id="add-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
            <div onclick="closeModal('add-subject-modal')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm">
            </div>

            <div
                class="modal-panel drawer-panel fixed inset-y-0 right-0 z-10 flex h-full w-full max-w-md translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300 ease-out sm:rounded-l-[28px]">
                <div class="flex shrink-0 items-center gap-3 border-b border-slate-100 px-5 py-4 sm:px-8 sm:py-5">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 sm:h-10 sm:w-10">
                        <x-icon.plus class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-slate-900 sm:text-lg">Tambah Jadwal</h3>
                        <p class="text-xs text-slate-400">Isi detail kelas baru</p>
                    </div>
                    <button type="button" onclick="closeModal('add-subject-modal')"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form id="add-subject-form" method="POST" action="{{ route('subjects.store') }}"
                    class="schedule-form flex flex-1 flex-col overflow-hidden">
                    @csrf
                    <input type="hidden" name="is_active" value="1">

                    <div
                        class="flex flex-1 flex-col gap-5 overflow-y-auto scrollbar-none px-5 py-5 sm:gap-6 sm:px-8 sm:py-6">
                        @if ($errors->any())
                            <div
                                class="flex items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
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
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Detail
                                Pelajaran</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama
                                        Pelajaran</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.book-open class="h-4 w-4" />
                                        </span>
                                        <input type="text" name="name" value="{{ old('name') }}"
                                            placeholder="Contoh: Matematika"
                                            class="{{ $inputBase }} @error('name') border-destructive @enderror">
                                    </div>
                                    @error('name')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Tingkat</label>
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                                <x-icon.academic-cap class="h-4 w-4" />
                                            </span>
                                            <select id="add-grade-select"
                                                class="{{ $inputBase }} appearance-none">
                                                <option value="" disabled selected>Pilih tingkat</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                                <x-icon.tag class="h-4 w-4" />
                                            </span>
                                            <select name="class_id" id="add-class-select"
                                                class="{{ $inputBase }} appearance-none">
                                                <option value="" disabled selected>Pilih tingkat dulu</option>
                                            </select>
                                        </div>
                                        @error('class_id')
                                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Guru
                                        Pengajar</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.identification class="h-4 w-4" />
                                        </span>
                                        <select name="teacher_id" id="add-teacher_id"
                                            class="{{ $inputBase }} appearance-none">
                                            <option value="" disabled @selected(!old('teacher_id'))>Pilih guru
                                            </option>
                                            @foreach ($teachers as $t)
                                                <option value="{{ $t->id }}" @selected(old('teacher_id') == $t->id)>
                                                    {{ $t->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('teacher_id')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                                    <div class="relative">
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.map-pin class="h-4 w-4" />
                                        </span>
                                        <input type="text" name="location" value="{{ old('location') }}"
                                            placeholder="Contoh: A203"
                                            class="{{ $inputBase }} @error('location') border-destructive @enderror">
                                    </div>
                                    @error('location')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
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
                                        <span
                                            class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                            <x-icon.calendar class="h-4 w-4" />
                                        </span>
                                        <select name="day"
                                            class="{{ $inputBase }} appearance-none @error('day') border-destructive @enderror">
                                            @foreach ($dayLabels as $dayValue => $dayLabel)
                                                <option value="{{ $dayValue }}" @selected(old('day') === $dayValue)>
                                                    {{ $dayLabel }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('day')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Jam
                                            Mulai</label>
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                                <x-icon.clock class="h-4 w-4" />
                                            </span>
                                            <input type="time" name="start_time" value="{{ old('start_time') }}"
                                                min="06:00" max="16:00"
                                                class="{{ $inputBase }} @error('start_time') border-destructive @enderror">
                                        </div>
                                        @error('start_time')
                                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Jam
                                            Selesai</label>
                                        <div class="relative">
                                            <span
                                                class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                                <x-icon.clock class="h-4 w-4" />
                                            </span>
                                            <input type="time" name="end_time" value="{{ old('end_time') }}"
                                                min="06:00" max="16:00"
                                                class="{{ $inputBase }} @error('end_time') border-destructive @enderror">
                                        </div>
                                        @error('end_time')
                                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground">Jam pelajaran cuma boleh antara 06:00–16:00.
                                </p>
                            </div>
                        </div>

                        <div>
                            <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-400">Tambahan</p>

                            <div class="space-y-4">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">PR
                                        (opsional)</label>
                                    <textarea name="homework" rows="2" placeholder="Contoh: Kerjakan halaman 42"
                                        class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10 @error('homework') border-destructive @enderror">{{ old('homework') }}</textarea>
                                    @error('homework')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <label
                                    class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-3 transition-colors hover:bg-slate-50">
                                    <input type="checkbox" name="has_exam" value="1"
                                        class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30"
                                        @checked(old('has_exam'))>
                                    <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="add-required_items"
                                class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-400">Barang
                                Wajib</label>
                            <textarea id="add-required_items" name="required_items" rows="2"
                                placeholder="Contoh: Buku Paket Matematika, Buku Tulis Matematika, Laptop"
                                class="w-full rounded-xl border border-border px-3.5 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20">{{ old('required_items') }}</textarea>
                            <p class="mt-1.5 text-xs text-muted-foreground">Pisahkan dengan koma.</p>
                        </div>
                    </div>

                    <div
                        class="flex shrink-0 items-center gap-3 border-t border-slate-100 bg-white px-5 py-4 sm:px-8 sm:py-5">
                        <button type="button" onclick="closeModal('add-subject-modal')"
                            class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit"
                            class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 py-3 text-sm font-semibold text-primary-foreground shadow-md shadow-blue-600/25 transition-all hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============ EDIT MODAL (ADMIN & GURU) ============ --}}
    @if ($canEdit)
        <div id="edit-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-3 sm:px-4">
            <div onclick="closeModal('edit-subject-modal')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm">
            </div>

            <div
                class="modal-panel relative max-h-[92vh] w-full max-w-lg scale-95 overflow-hidden rounded-[28px] bg-white p-5 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
                <div class="flex items-center gap-3">
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600 sm:h-10 sm:w-10">
                        <x-icon.pencil class="h-4.5 w-4.5 sm:h-5 sm:w-5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="flex items-center gap-2 text-base font-bold text-foreground sm:text-lg">
                            Edit Jadwal
                            <span id="edit-modal-loading"
                                class="hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
                        </h3>
                        <p class="text-xs text-slate-400">
                            {{ $isAdmin ? 'Perbarui detail jadwal ini' : 'Perbarui kelas, PR, dan barang wajib' }}
                        </p>
                    </div>
                    <button type="button" onclick="closeModal('edit-subject-modal')"
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <div id="edit-modal-error"
                    class="mt-4 hidden items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
                    <x-icon.exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" />
                    <span>Gagal ambil data jadwal. Coba lagi.</span>
                </div>

                <form id="edit-subject-form" method="POST" action=""
                    class="schedule-form mt-5 flex max-h-[65vh] flex-col gap-4 overflow-y-auto scrollbar-none pr-1 transition-opacity duration-150 sm:mt-6 sm:max-h-[70vh] sm:gap-5">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_active" value="1">

                    @if ($errors->any())
                        <div
                            class="flex items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
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

                    {{-- Ringkasan read-only untuk guru: field yang TIDAK bisa mereka ubah --}}
                    @if ($isTeacher)
                        <div class="rounded-2xl bg-slate-50 px-4 py-3">
                            <p id="edit-summary-name" class="text-sm font-semibold text-slate-800"></p>
                            <p id="edit-summary-meta" class="mt-0.5 text-xs text-slate-500"></p>
                            <p class="mt-1.5 text-[11px] text-slate-400">Nama pelajaran, guru, ruangan, hari & jam
                                hanya bisa diubah admin.</p>
                        </div>
                    @endif

                    {{-- Nama Pelajaran: khusus admin --}}
                    @if ($isAdmin)
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Pelajaran</label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                    <x-icon.book-open class="h-4 w-4" />
                                </span>
                                <input type="text" name="name" id="edit-name" class="{{ $inputBase }}">
                            </div>
                            @error('name')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    {{-- Pindah Kelas: admin & guru boleh mengubah --}}
                    @if ($isAdmin || $isTeacher)
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Tingkat</label>
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <x-icon.academic-cap class="h-4 w-4" />
                                    </span>
                                    <select id="edit-grade-select" class="{{ $inputBase }} appearance-none">
                                        <option value="" disabled selected>Pilih tingkat</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <x-icon.tag class="h-4 w-4" />
                                    </span>
                                    <select name="class_id" id="edit-class_id"
                                        class="{{ $inputBase }} appearance-none">
                                        <option value="" disabled>Pilih tingkat dulu</option>
                                    </select>
                                </div>
                                @error('class_id')
                                    <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    @endif

                    {{-- Field lain: khusus admin --}}
                    @if ($isAdmin)
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Guru Pengajar</label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                    <x-icon.identification class="h-4 w-4" />
                                </span>
                                <select name="teacher_id" id="edit-teacher_id"
                                    class="{{ $inputBase }} appearance-none">
                                    <option value="" disabled>Pilih guru</option>
                                    @foreach ($teachers as $t)
                                        <option value="{{ $t->id }}">{{ $t->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('teacher_id')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                    <x-icon.map-pin class="h-4 w-4" />
                                </span>
                                <input type="text" name="location" id="edit-location"
                                    class="{{ $inputBase }}">
                            </div>
                            @error('location')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                            <div class="relative">
                                <span
                                    class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                    <x-icon.calendar class="h-4 w-4" />
                                </span>
                                <select name="day" id="edit-day" class="{{ $inputBase }} appearance-none">
                                    @foreach ($dayLabels as $dayValue => $dayLabel)
                                        <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @error('day')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Mulai</label>
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <x-icon.clock class="h-4 w-4" />
                                    </span>
                                    <input type="time" name="start_time" id="edit-start_time" min="06:00"
                                        max="16:00" class="{{ $inputBase }}">
                                </div>
                                @error('start_time')
                                    <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                                <div class="relative">
                                    <span
                                        class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                        <x-icon.clock class="h-4 w-4" />
                                    </span>
                                    <input type="time" name="end_time" id="edit-end_time" min="06:00"
                                        max="16:00" class="{{ $inputBase }}">
                                </div>
                                @error('end_time')
                                    <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <p class="-mt-2 text-xs text-muted-foreground">Jam pelajaran cuma boleh antara 06:00–16:00.</p>

                        <label
                            class="flex cursor-pointer items-center gap-2.5 rounded-xl border border-slate-200 px-3.5 py-3 transition-colors hover:bg-slate-50">
                            <input type="checkbox" name="has_exam" id="edit-has_exam" value="1"
                                class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
                            <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                        </label>
                    @endif

                    {{-- Field untuk admin & guru --}}
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">PR (opsional)</label>
                        <textarea name="homework" id="edit-homework" rows="2"
                            class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground shadow-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10"></textarea>
                        @error('homework')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="edit-required_items"
                            class="mb-1.5 block text-sm font-medium text-foreground">Barang Wajib</label>
                        <textarea id="edit-required_items" name="required_items" rows="2"
                            placeholder="Contoh: Buku Paket Matematika, Buku Tulis Matematika, Laptop"
                            class="w-full rounded-xl border border-border px-3.5 py-3 text-sm text-foreground placeholder:text-muted-foreground focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"></textarea>
                        <p class="mt-1.5 text-xs text-muted-foreground">Pisahkan dengan koma.</p>
                    </div>

                    <div class="sticky bottom-0 -mx-1 mt-1 flex items-center gap-3 bg-white px-1 pt-2">
                        <button type="button" onclick="closeModal('edit-subject-modal')"
                            class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit"
                            class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 py-3 text-sm font-semibold text-primary-foreground shadow-md shadow-blue-600/25 transition-all hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ============ DELETE MODAL (ADMIN) ============ --}}
    @if ($canDelete)
        <div id="delete-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('delete-subject-modal')"
                class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <div
                class="modal-panel relative w-full max-w-sm scale-95 rounded-[28px] bg-white p-6 text-center opacity-0 shadow-2xl transition-all duration-200 ease-out">
                <span
                    class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive">
                    <x-icon.trash class="h-7 w-7" />
                </span>

                <h3 class="mt-4 text-lg font-bold text-foreground">Hapus jadwal ini?</h3>
                <p id="delete-subject-name" class="mt-2 text-sm leading-relaxed text-muted-foreground"></p>

                <form id="delete-subject-form" method="POST" action=""
                    class="schedule-form mt-6 flex items-center gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button" onclick="closeModal('delete-subject-modal')"
                        class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                        Batal
                    </button>
                    <button type="submit"
                        class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-destructive py-3 text-sm font-semibold text-white transition-all hover:bg-destructive/90 disabled:cursor-not-allowed disabled:opacity-70">
                        <span class="btn-label">Ya, Hapus</span>
                    </button>
                </form>
            </div>
        </div>
    @endif

    <script>
        function switchScheduleDay(day, button) {
            document.querySelectorAll('.schedule-day-panel').forEach(function(panel) {
                panel.classList.add('hidden');
            });
            document.getElementById('schedule-day-' + day).classList.remove('hidden');

            document.querySelectorAll('.day-tab').forEach(function(tab) {
                tab.classList.remove('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600',
                    'text-primary-foreground', 'shadow-md', 'shadow-blue-600/25');
                tab.classList.add('border', 'border-border', 'bg-card', 'text-muted-foreground');
            });
            button.classList.remove('border', 'border-border', 'bg-card', 'text-muted-foreground');
            button.classList.add('bg-gradient-to-r', 'from-blue-600', 'to-indigo-600', 'text-primary-foreground',
                'shadow-md', 'shadow-blue-600/25');
        }

        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            const panel = modal.querySelector('.modal-panel');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            requestAnimationFrame(function() {
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

            setTimeout(function() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 300);
        }

        function openAddModal() {
            openModal('add-subject-modal');
        }

        function openDeleteModal(button) {
            const form = document.getElementById('delete-subject-form');
            if (!form) return;

            document.getElementById('delete-subject-name').textContent =
                'Jadwal "' + button.dataset.name + '" akan dihapus permanen dan gak bisa dibatalin.';
            form.action = '/subjects/' + button.dataset.id;
            openModal('delete-subject-modal');
        }

        document.querySelectorAll('form.schedule-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                const startInput = form.querySelector('input[name="start_time"]');
                const endInput = form.querySelector('input[name="end_time"]');

                if (startInput && endInput && startInput.value && endInput.value) {
                    const isOutOfRange = function(time) {
                        return time < '06:00' || time > '16:00';
                    };

                    if (isOutOfRange(startInput.value) || isOutOfRange(endInput.value)) {
                        e.preventDefault();
                        alert('Jam pelajaran cuma boleh antara 06:00 sampai 16:00.');
                        return;
                    }
                }

                const btn = form.querySelector('.submit-btn');
                if (!btn || btn.disabled) return;

                btn.disabled = true;
                const label = btn.querySelector('.btn-label');
                if (label) {
                    label.dataset.original = label.textContent;
                    label.textContent = 'Memproses...';
                }
                btn.insertAdjacentHTML('afterbegin',
                    '<span class="h-3.5 w-3.5 shrink-0 animate-spin rounded-full border-2 border-current border-t-transparent"></span>'
                );
            });
        });

        document.querySelectorAll('.schedule-ripple-btn').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const span = document.createElement('span');
                span.className = 'schedule-ripple-span';
                span.style.width = span.style.height = size + 'px';
                span.style.left = (e.clientX - rect.left - size / 2) + 'px';
                span.style.top = (e.clientY - rect.top - size / 2) + 'px';
                btn.appendChild(span);
                setTimeout(function() {
                    span.remove();
                }, 600);
            });
        });

        (function() {
            const addForm = document.getElementById('add-subject-form');
            const editForm = document.getElementById('edit-subject-form');

            if (addForm) {
                addForm.addEventListener('submit', function() {
                    sessionStorage.setItem('schedule_form_context', JSON.stringify({
                        type: 'add'
                    }));
                });
            }

            if (editForm) {
                editForm.addEventListener('submit', function() {
                    const action = editForm.action || '';
                    const id = action.substring(action.lastIndexOf('/') + 1);
                    sessionStorage.setItem('schedule_form_context', JSON.stringify({
                        type: 'edit',
                        id: id
                    }));
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

        // ============ CASCADING DROPDOWN TINGKAT -> KELAS + EDIT MODAL ============
        (function() {
            const classesData = @json($classesJson);

            function uniqueGrades() {
                return [...new Set(classesData.map(function(c) {
                    return c.grade;
                }))];
            }

            function populateGradeOptions(gradeSelect) {
                gradeSelect.innerHTML = '<option value="" disabled selected>Pilih tingkat</option>';
                uniqueGrades().forEach(function(grade) {
                    const opt = document.createElement('option');
                    opt.value = grade;
                    opt.textContent = grade;
                    gradeSelect.appendChild(opt);
                });
            }

            function populateClassOptions(classSelect, grade, selectedId) {
                const filtered = classesData.filter(function(c) {
                    return c.grade === grade;
                });

                if (filtered.length === 0) {
                    classSelect.innerHTML = '<option value="" disabled selected>Tidak ada kelas</option>';
                    return;
                }

                classSelect.innerHTML = '<option value="" disabled selected>Pilih kelas</option>' +
                    filtered.map(function(c) {
                        const isSelected = selectedId && String(c.id) === String(selectedId);
                        return '<option value="' + c.id + '"' + (isSelected ? ' selected' : '') + '>' + c.label +
                            '</option>';
                    }).join('');
            }

            // ---- Form Tambah ----
            const addGradeSelect = document.getElementById('add-grade-select');
            const addClassSelect = document.getElementById('add-class-select');

            if (addGradeSelect && addClassSelect) {
                populateGradeOptions(addGradeSelect);

                addGradeSelect.addEventListener('change', function() {
                    populateClassOptions(addClassSelect, this.value, null);
                });

                @if (old('class_id'))
                    (function() {
                        const oldClass = classesData.find(function(c) {
                            return String(c.id) === '{{ old('class_id') }}';
                        });
                        if (oldClass) {
                            addGradeSelect.value = oldClass.grade;
                            populateClassOptions(addClassSelect, oldClass.grade, oldClass.id);
                        }
                    })();
                @endif
            }

            // ---- Form Edit (dropdown tersedia untuk admin & guru) ----
            const editGradeSelect = document.getElementById('edit-grade-select');
            const editClassSelect = document.getElementById('edit-class_id');

            if (editGradeSelect && editClassSelect) {
                populateGradeOptions(editGradeSelect);

                editGradeSelect.addEventListener('change', function() {
                    populateClassOptions(editClassSelect, this.value, null);
                });
            }

            function setVal(id, value) {
                const el = document.getElementById(id);
                if (el) el.value = value;
            }

            function setText(id, value) {
                const el = document.getElementById(id);
                if (el) el.textContent = value;
            }

            window.openEditModal = function(button) {
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

                fetch('/subjects/' + id + '/edit', {
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(function(response) {
                        if (!response.ok) throw new Error('Gagal');
                        return response.json();
                    })
                    .then(function(subject) {
                        const start = (subject.start_time ?? '').toString().slice(0, 5);
                        const end = (subject.end_time ?? '').toString().slice(0, 5);

                        setVal('edit-name', subject.name ?? '');
                        setVal('edit-teacher_id', subject.teacher_id ?? '');
                        setVal('edit-location', subject.location ?? '');
                        setVal('edit-day', subject.day ?? '');
                        setVal('edit-start_time', start);
                        setVal('edit-end_time', end);
                        setVal('edit-homework', subject.homework ?? '');
                        setVal('edit-required_items',
                            (subject.requiredItems || []).map(function(item) {
                                return item.name;
                            }).join(', '));

                        const exam = document.getElementById('edit-has_exam');
                        if (exam) exam.checked = !!subject.has_exam;

                        setText('edit-summary-name', subject.name ?? '');
                        setText('edit-summary-meta', [
                            subject.school_class ? subject.school_class.name : '',
                            subject.day ?? '',
                            start + '–' + end,
                            subject.location ?? ''
                        ].filter(Boolean).join(' • '));

                        if (editGradeSelect && editClassSelect) {
                            const targetClassId = subject.class_id ?? (subject.school_class ? subject
                                .school_class.id : null);
                            const currentClass = classesData.find(function(c) {
                                return String(c.id) === String(targetClassId);
                            });

                            if (currentClass) {
                                editGradeSelect.value = currentClass.grade;
                                populateClassOptions(editClassSelect, currentClass.grade, currentClass.id);
                            } else {
                                editGradeSelect.value = '';
                                editClassSelect.innerHTML =
                                    '<option value="" disabled selected>Pilih tingkat dulu</option>';
                            }
                        }

                        form.action = '/subjects/' + id;
                    })
                    .catch(function() {
                        errorBanner.classList.remove('hidden');
                        errorBanner.classList.add('flex');
                    })
                    .finally(function() {
                        form.classList.remove('opacity-40', 'pointer-events-none');
                        loadingIndicator.classList.add('hidden');
                    });
            };
        })();
    </script>
</x-layouts.dashboard>
