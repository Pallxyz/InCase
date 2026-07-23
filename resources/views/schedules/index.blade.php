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

    // Dipakai buat badge kecil di tab hari (mis. "Senin · 3"), murni kosmetik.
    $dayCounts = $subjects->groupBy('day')->map->count();

    $isTeacher = auth()->check() && (auth()->user()->role ?? null) === 'teacher';
    $isStudent = auth()->check() && (auth()->user()->role ?? null) === 'student';

    // Siapa aja yang boleh liat tombol/modal "Tambah Jadwal". Edit & Hapus
    // tetap eksklusif buat teacher (lihat $isTeacher di bawah), tapi Tambah
    // sekarang dibuka juga buat student.
    $canAddSchedule = $isTeacher || $isStudent;

    // SubjectController@index belum ngirim $items ke view ini, padahal modal
    // Tambah butuh daftar barang buat multi-select. Query langsung di sini
    // (data asli dari tabel items, BUKAN array dummy) sebagai jalan pintas.
    // Idealnya nanti dipindah ke SubjectController::index() dan di-pass via compact().
    $availableItems = $canAddSchedule
        ? \App\Models\Item::where('user_id', auth()->id())->orderBy('name')->get(['id', 'name'])
        : collect();
@endphp

<x-layouts.dashboard title="Jadwal — InCase">
    <style>
        @keyframes schedule-fade-in-up {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .schedule-card-enter {
            animation: schedule-fade-in-up 0.35s ease-out both;
        }
        @media (prefers-reduced-motion: reduce) {
            .schedule-card-enter { animation: none; }
        }
    </style>

    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

                {{-- ============ FLASH MESSAGE ============ --}}
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-2 rounded-2xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
                        <x-icon.check-circle class="h-4 w-4 shrink-0" />
                        {{ session('success') }}
                    </div>
                @endif

                {{-- ============ HEADER ============ --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Jadwal
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Kelola dan lihat jadwal pelajaran.
                        </p>
                    </div>

                    @if ($canAddSchedule)
                        <button
                            type="button"
                            onclick="openAddModal()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 active:scale-[0.98]"
                        >
                            <x-icon.plus class="h-4 w-4" />
                            Tambah Jadwal
                        </button>
                    @endif
                </div>

                {{-- ============ TODAY'S CLASSES ============ --}}
                <div class="mt-6">
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-foreground">
                            Kelas Hari Ini · {{ $dayLabels[$todayDay] ?? $todayDay }}
                        </h2>
                        @if ($todaySubjects->isNotEmpty())
                            <span class="inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary/10 px-1.5 text-[11px] font-semibold text-primary">
                                {{ $todaySubjects->count() }}
                            </span>
                        @endif
                    </div>

                    @if ($todaySubjects->isEmpty())
                        <x-schedule.empty-state
                            class="mt-4"
                            title="Gak ada kelas hari ini"
                            description="Nikmati harimu, atau cek jadwal hari lain di bawah."
                        />
                    @else
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($todaySubjects as $subject)
                                <x-schedule.subject-card
                                    :subject="$subject"
                                    :is-teacher="$isTeacher"
                                    variant="today"
                                    class="schedule-card-enter"
                                    style="animation-delay: {{ $loop->index * 40 }}ms"
                                />
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- ============ WEEKLY SCHEDULE ============ --}}
                <div class="mt-8">
                    <h2 class="text-lg font-bold text-foreground">Jadwal Mingguan</h2>

                    {{-- Sticky day tabs --}}
                    <div class="sticky top-0 z-10 mt-4 flex gap-2 overflow-x-auto bg-background py-2">
                        @foreach ($dayLabels as $dayValue => $dayLabel)
                            <button
                                type="button"
                                onclick="switchScheduleDay('{{ $dayValue }}', this)"
                                data-day="{{ $dayValue }}"
                                class="day-tab inline-flex shrink-0 items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ $dayValue === $todayDay ? 'bg-primary text-primary-foreground' : 'border border-border bg-card text-muted-foreground hover:text-foreground' }}"
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

                    {{-- Subject cards per hari --}}
                    @foreach ($dayLabels as $dayValue => $dayLabel)
                        <div
                            id="schedule-day-{{ $dayValue }}"
                            class="schedule-day-panel mt-4 flex flex-col gap-4 {{ $dayValue === $todayDay ? '' : 'hidden' }}"
                        >
                            @php
                                $dayItems = $subjects->where('day', $dayValue)->values();
                            @endphp

                            @if ($dayItems->isEmpty())
                                <x-schedule.empty-state
                                    title="Belum ada jadwal"
                                    :description="$canAddSchedule
                                        ? 'Tambahkan jadwal buat hari ' . $dayLabel . '.'
                                        : 'Belum ada kelas yang dijadwalkan di hari ' . $dayLabel . '.'"
                                >
                                    {{-- @if ($canAddSchedule)
                                        <button
                                            type="button"
                                            onclick="openAddModal()"
                                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                        >
                                            <x-icon.plus class="h-4 w-4" />
                                            Tambah Jadwal
                                        </button>
                                    @endif --}}
                                </x-schedule.empty-state>
                            @else
                                @foreach ($dayItems as $subject)
                                    <x-schedule.subject-card
                                        :subject="$subject"
                                        :is-teacher="$isTeacher"
                                        variant="weekly"
                                        class="schedule-card-enter"
                                        style="animation-delay: {{ $loop->index * 40 }}ms"
                                    />
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    @if ($canAddSchedule)
        {{-- ============ ADD SCHEDULE DRAWER ============ --}}
        <div id="add-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center">
            <div onclick="closeModal('add-subject-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            <div class="modal-panel drawer-panel fixed inset-y-0 right-0 z-10 flex h-full w-full max-w-md translate-x-full flex-col bg-card shadow-2xl transition-transform duration-300 ease-out sm:rounded-l-[24px]">
                <div class="flex shrink-0 items-center justify-between border-b border-border px-6 py-5 sm:px-8">
                    <h3 class="text-lg font-bold text-foreground">Tambah Jadwal</h3>
                    <button type="button" onclick="closeModal('add-subject-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form id="add-subject-form" method="POST" action="{{ route('subjects.store') }}" class="schedule-form flex flex-1 flex-col overflow-hidden">
                    @csrf
                    <input type="hidden" name="is_active" value="1">

                    <div class="flex flex-1 flex-col gap-5 overflow-y-auto scrollbar-none px-6 py-6 sm:px-8">

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
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Matematika" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 @error('name') border-destructive @enderror">
                        @error('name')
                            <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: A203" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 @error('location') border-destructive @enderror">
                        @error('location')
                            <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                        <select name="day" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 @error('day') border-destructive @enderror">
                            @foreach ($dayLabels as $dayValue => $dayLabel)
                                <option value="{{ $dayValue }}" @selected(old('day') === $dayValue)>{{ $dayLabel }}</option>
                            @endforeach
                        </select>
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
                            <input type="time" name="start_time" value="{{ old('start_time') }}" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 @error('start_time') border-destructive @enderror">
                            @error('start_time')
                                <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                    <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 @error('end_time') border-destructive @enderror">
                            @error('end_time')
                                <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                    <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">PR (opsional)</label>
                        <textarea name="homework" rows="2" placeholder="Contoh: Kerjakan halaman 42" class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 @error('homework') border-destructive @enderror">{{ old('homework') }}</textarea>
                        @error('homework')
                            <p class="mt-1.5 flex items-center gap-1 text-xs font-medium text-destructive">
                                <x-icon.exclamation-triangle class="h-3 w-3 shrink-0" />
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" name="has_exam" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30" @checked(old('has_exam'))>
                        <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                    </label>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Barang Wajib</label>
                        <div class="flex flex-col divide-y divide-border rounded-xl border border-border">
                            @forelse ($availableItems as $item)
                                <label class="item-checkbox-row flex cursor-pointer items-center gap-3 px-3.5 py-2.5 transition-colors hover:bg-muted">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" class="item-checkbox h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30" @checked(collect(old('items'))->contains($item->id))>
                                    <span class="text-sm text-foreground">{{ $item->name }}</span>
                                </label>
                            @empty
                                <p class="px-3.5 py-4 text-center text-sm text-muted-foreground">
                                    Belum ada barang terdaftar. Tambahkan dulu di halaman Barang.
                                </p>
                            @endforelse
                        </div>
                    </div>

                    </div>{{-- /scrollable body --}}

                    <div class="flex shrink-0 items-center gap-3 border-t border-border px-6 py-5 sm:px-8">
                        <button type="button" onclick="closeModal('add-subject-modal')" class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit" class="submit-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($isTeacher)
        {{-- ============ EDIT SCHEDULE MODAL ============ --}}
        <div id="edit-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('edit-subject-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-lg scale-95 rounded-[24px] bg-card p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
                <div class="flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-lg font-bold text-foreground">
                        Edit Jadwal
                        <span id="edit-modal-loading" class="hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-primary border-t-transparent"></span>
                    </h3>
                    <button type="button" onclick="closeModal('edit-subject-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
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
                        <input type="text" name="name" id="edit-name" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('name')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                        <input type="text" name="location" id="edit-location" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('location')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                        <select name="day" id="edit-day" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @foreach ($dayLabels as $dayValue => $dayLabel)
                                <option value="{{ $dayValue }}">{{ $dayLabel }}</option>
                            @endforeach
                        </select>
                        @error('day')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Mulai</label>
                            <input type="time" name="start_time" id="edit-start_time" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @error('start_time')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                            <input type="time" name="end_time" id="edit-end_time" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @error('end_time')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">PR (opsional)</label>
                        <textarea name="homework" id="edit-homework" rows="2" class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"></textarea>
                        @error('homework')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" name="has_exam" id="edit-has_exam" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
                        <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                    </label>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Barang Wajib</label>
                        <div class="flex flex-col divide-y divide-border rounded-xl border border-border">
                            @forelse ($availableItems as $item)
                                <label class="item-checkbox-row flex cursor-pointer items-center gap-3 px-3.5 py-2.5 transition-colors hover:bg-muted">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" class="edit-item-checkbox item-checkbox h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
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
                        <button type="button" onclick="closeModal('edit-subject-modal')" class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit" class="submit-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Simpan Perubahan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============ DELETE CONFIRMATION MODAL ============ --}}
        <div id="delete-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('delete-subject-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-sm scale-95 rounded-[24px] bg-card p-6 text-center opacity-0 shadow-2xl transition-all duration-200 ease-out">
                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive">
                    <x-icon.trash class="h-7 w-7" />
                </span>

                <h3 class="mt-4 text-lg font-bold text-foreground">Hapus jadwal ini?</h3>
                <p id="delete-subject-name" class="mt-2 text-sm leading-relaxed text-muted-foreground"></p>

                <form id="delete-subject-form" method="POST" action="" class="schedule-form mt-6 flex items-center gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button" onclick="closeModal('delete-subject-modal')" class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                        Batal
                    </button>
                    <button type="submit" class="submit-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-destructive py-2.5 text-sm font-semibold text-white transition-colors hover:bg-destructive/90 disabled:cursor-not-allowed disabled:opacity-70">
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
                tab.classList.remove('bg-primary', 'text-primary-foreground');
                tab.classList.add('border', 'border-border', 'bg-card', 'text-muted-foreground');
            });
            button.classList.remove('border', 'border-border', 'bg-card', 'text-muted-foreground');
            button.classList.add('bg-primary', 'text-primary-foreground');
        }

        // Drawer (add) pakai translate-x, modal-tengah (edit/delete) pakai scale+opacity.
        // Dibedain lewat class .drawer-panel di elemen .modal-panel-nya.
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

        // Edit butuh fetch ke SubjectController@edit (return JSON), soalnya
        // controller itu emang didesain buat dipanggil via AJAX, bukan render Blade.
        // Cuma ada buat teacher, jadi guard elemen-elemennya dulu.
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
                    document.getElementById('edit-start_time').value = (subject.start_time ?? '').toString().slice(0, 5);
                    document.getElementById('edit-end_time').value = (subject.end_time ?? '').toString().slice(0, 5);
                    document.getElementById('edit-homework').value = subject.homework ?? '';
                    document.getElementById('edit-has_exam').checked = !!subject.has_exam;

                    const assignedIds = (subject.items || []).map(function (item) { return item.id; });
                    document.querySelectorAll('.edit-item-checkbox').forEach(function (checkbox) {
                        checkbox.checked = assignedIds.includes(Number(checkbox.value));
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

        // Highlight baris checkbox barang yang lagi dicentang, biar gampang di-scan.
        document.querySelectorAll('.item-checkbox').forEach(function (checkbox) {
            const row = checkbox.closest('.item-checkbox-row');
            const sync = function () {
                row.classList.toggle('bg-primary/5', checkbox.checked);
            };
            checkbox.addEventListener('change', sync);
            sync();
        });

        // Cegah double-submit + kasih indikator loading di tombol submit.
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

        // Kalau submit gagal validasi, form di-redirect balik ke halaman ini
        // dengan modal tertutup lagi — jadi error-nya gak keliatan sampai user
        // buka modal manual. Simpan konteks sebelum submit, lalu reopen modal
        // yang relevan begitu halaman reload dan ada error.
        // Add modal ada buat teacher & student, Edit modal cuma buat teacher —
        // jadi semua akses ke editForm di-guard null-check.
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