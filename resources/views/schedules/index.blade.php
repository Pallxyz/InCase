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

    $isTeacher = auth()->check() && (auth()->user()->role ?? null) === 'teacher';
@endphp

<x-layouts.dashboard title="Jadwal — InCase">
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

                    @if ($isTeacher)
                        <button
                            type="button"
                            onclick="openAddModal()"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            <x-icon.plus class="h-4 w-4" />
                            Tambah Jadwal
                        </button>
                    @endif
                </div>

                {{-- ============ TODAY'S CLASSES ============ --}}
                <div class="mt-6">
                    <h2 class="text-lg font-bold text-foreground">
                        Kelas Hari Ini · {{ $dayLabels[$todayDay] ?? $todayDay }}
                    </h2>

                    @if ($todaySubjects->isEmpty())
                        <div class="mt-4 flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card px-8 py-14 text-center">
                            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <x-icon.calendar-days class="h-7 w-7" />
                            </span>
                            <p class="mt-4 text-sm font-medium text-foreground">Gak ada kelas hari ini</p>
                            <p class="mt-1 text-sm text-muted-foreground">Nikmati harimu, atau cek jadwal hari lain di bawah.</p>
                        </div>
                    @else
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            @foreach ($todaySubjects as $subject)
                                <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <h3 class="text-lg font-bold text-foreground">{{ $subject->name }}</h3>
                                            <p class="mt-1 text-sm font-medium text-muted-foreground">
                                                {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                                            </p>
                                        </div>

                                        @if ($subject->has_exam)
                                            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-1 text-[11px] font-semibold text-destructive">
                                                <x-icon.exclamation-triangle class="h-3 w-3" />
                                                Ujian
                                            </span>
                                        @endif
                                    </div>

                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-icon.user class="h-4 w-4" />
                                            {{ $subject->teacher->name ?? '—' }}
                                        </span>
                                        @if ($subject->location)
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-icon.map-pin class="h-4 w-4" />
                                                Ruang {{ $subject->location }}
                                            </span>
                                        @endif
                                    </div>

                                    @if (! empty($subject->homework))
                                        <div class="mt-3 flex items-start gap-2 rounded-xl bg-warning/10 px-3 py-2.5 text-xs text-warning">
                                            <x-icon.document-text class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                            <span><span class="font-semibold">PR:</span> {{ $subject->homework }}</span>
                                        </div>
                                    @endif

                                    @if ($subject->items->isNotEmpty())
                                        <div class="mt-3 flex flex-wrap gap-1.5">
                                            @foreach ($subject->items as $item)
                                                <span class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium text-foreground">
                                                    {{ $item->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    @if ($isTeacher)
                                        <div class="mt-4 flex items-center gap-2 border-t border-border pt-4">
                                            <button
                                                type="button"
                                                data-id="{{ $subject->id }}"
                                                onclick="openEditModal(this)"
                                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
                                            >
                                                <x-icon.pencil class="h-3.5 w-3.5" />
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                data-id="{{ $subject->id }}"
                                                data-name="{{ $subject->name }}"
                                                onclick="openDeleteModal(this)"
                                                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-destructive/20 py-2 text-xs font-semibold text-destructive transition-colors hover:bg-destructive/10"
                                            >
                                                <x-icon.trash class="h-3.5 w-3.5" />
                                                Hapus
                                            </button>
                                        </div>
                                    @endif
                                </div>
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
                                class="day-tab shrink-0 rounded-full px-4 py-2 text-sm font-semibold transition-colors {{ $dayValue === $todayDay ? 'bg-primary text-primary-foreground' : 'border border-border bg-card text-muted-foreground hover:text-foreground' }}"
                            >
                                {{ $dayLabel }}
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
                                <div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card px-8 py-14 text-center">
                                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                        <x-icon.calendar-days class="h-7 w-7" />
                                    </span>
                                    <p class="mt-4 text-sm font-medium text-foreground">Belum ada jadwal</p>
                                    @if ($isTeacher)
                                        <p class="mt-1 text-sm text-muted-foreground">Tambahkan jadwal buat hari {{ $dayLabel }}.</p>
                                        <button
                                            type="button"
                                            onclick="openAddModal()"
                                            class="mt-4 inline-flex items-center gap-2 rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                                        >
                                            <x-icon.plus class="h-4 w-4" />
                                            Tambah Jadwal
                                        </button>
                                    @else
                                        <p class="mt-1 text-sm text-muted-foreground">Belum ada kelas yang dijadwalkan di hari {{ $dayLabel }}.</p>
                                    @endif
                                </div>
                            @else
                                @foreach ($dayItems as $subject)
                                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
                                        <div class="flex flex-wrap items-start justify-between gap-3">
                                            <div>
                                                <h3 class="text-base font-bold text-foreground">{{ $subject->name }}</h3>
                                                <p class="mt-1 flex items-center gap-1.5 text-xs text-muted-foreground">
                                                    <x-icon.clock class="h-3.5 w-3.5" />
                                                    {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
                                                </p>
                                            </div>

                                            @if ($subject->has_exam)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-1 text-[11px] font-semibold text-destructive">
                                                    <x-icon.exclamation-triangle class="h-3 w-3" />
                                                    Ujian
                                                </span>
                                            @endif
                                        </div>

                                        <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
                                            <span class="inline-flex items-center gap-1.5">
                                                <x-icon.user class="h-3.5 w-3.5" />
                                                {{ $subject->teacher->name ?? '—' }}
                                            </span>
                                            @if ($subject->location)
                                                <span class="inline-flex items-center gap-1.5">
                                                    <x-icon.map-pin class="h-3.5 w-3.5" />
                                                    Ruang {{ $subject->location }}
                                                </span>
                                            @endif
                                        </div>

                                        @if (! empty($subject->homework))
                                            <div class="mt-3 flex items-start gap-2 rounded-xl bg-warning/10 px-3 py-2.5 text-xs text-warning">
                                                <x-icon.document-text class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                                                <span><span class="font-semibold">PR:</span> {{ $subject->homework }}</span>
                                            </div>
                                        @endif

                                        @if ($subject->items->isNotEmpty())
                                            <div class="mt-3 flex flex-wrap gap-1.5">
                                                @foreach ($subject->items as $item)
                                                    <span class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium text-foreground">
                                                        {{ $item->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif

                                        @if ($isTeacher)
                                            <div class="mt-4 flex items-center gap-2 border-t border-border pt-4">
                                                <button
                                                    type="button"
                                                    data-id="{{ $subject->id }}"
                                                    onclick="openEditModal(this)"
                                                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
                                                >
                                                    <x-icon.pencil class="h-3.5 w-3.5" />
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    data-id="{{ $subject->id }}"
                                                    data-name="{{ $subject->name }}"
                                                    onclick="openDeleteModal(this)"
                                                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-destructive/20 py-2 text-xs font-semibold text-destructive transition-colors hover:bg-destructive/10"
                                                >
                                                    <x-icon.trash class="h-3.5 w-3.5" />
                                                    Hapus
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    @if ($isTeacher)
        {{-- ============ ADD SCHEDULE MODAL ============ --}}
        <div id="add-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('add-subject-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-lg scale-95 rounded-[24px] bg-card p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">Tambah Jadwal</h3>
                    <button type="button" onclick="closeModal('add-subject-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('subjects.store') }}" class="mt-6 flex max-h-[70vh] flex-col gap-5 overflow-y-auto scrollbar-none pr-1">
                    @csrf
                    <input type="hidden" name="is_active" value="1">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Pelajaran</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Matematika" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('name')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                        <select name="class_id" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            <option value="" disabled selected>Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}" @selected((string) old('class_id') === (string) $class->id)>
                                    {{ $class->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                        <input type="text" name="location" value="{{ old('location') }}" placeholder="Contoh: A203" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('location')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                        <select name="day" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @foreach ($dayLabels as $dayValue => $dayLabel)
                                <option value="{{ $dayValue }}" @selected(old('day') === $dayValue)>{{ $dayLabel }}</option>
                            @endforeach
                        </select>
                        @error('day')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Mulai</label>
                            <input type="time" name="start_time" value="{{ old('start_time') }}" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @error('start_time')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                            <input type="time" name="end_time" value="{{ old('end_time') }}" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @error('end_time')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">PR (opsional)</label>
                        <textarea name="homework" rows="2" placeholder="Contoh: Kerjakan halaman 42" class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">{{ old('homework') }}</textarea>
                        @error('homework')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2.5">
                        <input type="checkbox" name="has_exam" value="1" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
                        <span class="text-sm text-foreground">Ada ujian di kelas ini</span>
                    </label>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Barang Wajib</label>
                        <div class="flex flex-col divide-y divide-border rounded-xl border border-border">
                            @forelse ($items as $item)
                                <label class="flex cursor-pointer items-center gap-3 px-3.5 py-2.5 transition-colors hover:bg-muted">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
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
                        <button type="button" onclick="closeModal('add-subject-modal')" class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ============ EDIT SCHEDULE MODAL ============ --}}
        <div id="edit-subject-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('edit-subject-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-lg scale-95 rounded-[24px] bg-card p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-foreground">Edit Jadwal</h3>
                    <button type="button" onclick="closeModal('edit-subject-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                <form id="edit-subject-form" method="POST" action="" class="mt-6 flex max-h-[70vh] flex-col gap-5 overflow-y-auto scrollbar-none pr-1">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="is_active" value="1">

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Pelajaran</label>
                        <input type="text" name="name" id="edit-name" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        @error('name')
                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                        <select name="class_id" id="edit-class_id" class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            <option value="" disabled>Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                        @error('class_id')
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
                            @forelse ($items as $item)
                                <label class="flex cursor-pointer items-center gap-3 px-3.5 py-2.5 transition-colors hover:bg-muted">
                                    <input type="checkbox" name="items[]" value="{{ $item->id }}" class="edit-item-checkbox h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30">
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
                        <button type="submit" class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                            Simpan Perubahan
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

                <form id="delete-subject-form" method="POST" action="" class="mt-6 flex items-center gap-3">
                    @csrf
                    @method('DELETE')

                    <button type="button" onclick="closeModal('delete-subject-modal')" class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                        Batal
                    </button>
                    <button type="submit" class="flex-1 rounded-full bg-destructive py-2.5 text-sm font-semibold text-white transition-colors hover:bg-destructive/90">
                        Ya, Hapus
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

        function openModal(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            const panel = modal.querySelector('.modal-panel');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            requestAnimationFrame(function () {
                if (panel) {
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
                panel.classList.remove('opacity-100', 'scale-100');
                panel.classList.add('opacity-0', 'scale-95');
            }

            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 150);
        }

        function openAddModal() {
            openModal('add-subject-modal');
        }

        // Edit butuh fetch ke SubjectController@edit (return JSON), soalnya
        // controller itu emang didesain buat dipanggil via AJAX, bukan render Blade.
        function openEditModal(button) {
            const id = button.dataset.id;

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

                    document.getElementById('edit-subject-form').action = '/subjects/' + id;
                    openModal('edit-subject-modal');
                })
                .catch(function () {
                    alert('Gagal ambil data jadwal. Coba lagi.');
                });
        }

        function openDeleteModal(button) {
            document.getElementById('delete-subject-name').textContent =
                'Jadwal "' + button.dataset.name + '" akan dihapus permanen dan gak bisa dibatalin.';
            document.getElementById('delete-subject-form').action = '/subjects/' + button.dataset.id;
            openModal('delete-subject-modal');
        }
    </script>
</x-layouts.dashboard>