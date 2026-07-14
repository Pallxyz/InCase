@php
    $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    $todayName = $dayNames[now()->dayOfWeekIso] ?? 'Senin';

    $weeklySchedule = [
        'Senin' => [
            ['subject' => 'Matematika', 'teacher' => 'Bu Sari', 'room' => 'Ruang 3A', 'start' => '07:00', 'end' => '08:30', 'items' => [['icon' => 'book-open', 'label' => 'Buku Matematika'], ['icon' => 'calculator', 'label' => 'Kalkulator']]],
            ['subject' => 'Bahasa Indonesia', 'teacher' => 'Pak Budi', 'room' => 'Ruang 2B', 'start' => '08:30', 'end' => '10:00', 'items' => [['icon' => 'book-open', 'label' => 'Buku Bahasa Indonesia'], ['icon' => 'document-text', 'label' => 'Buku Tulis']]],
            ['subject' => 'IPA', 'teacher' => 'Bu Rina', 'room' => 'Lab IPA', 'start' => '10:15', 'end' => '11:45', 'items' => [['icon' => 'book-open', 'label' => 'Buku IPA'], ['icon' => 'calculator', 'label' => 'Kalkulator']]],
            ['subject' => 'Olahraga', 'teacher' => 'Pak Joko', 'room' => 'Lapangan', 'start' => '13:00', 'end' => '14:30', 'items' => [['icon' => 'tag', 'label' => 'Sepatu Olahraga'], ['icon' => 'beaker', 'label' => 'Botol Minum']]],
        ],
        'Selasa' => [
            ['subject' => 'Fisika', 'teacher' => 'Bu Dewi', 'room' => 'Ruang 3A', 'start' => '07:00', 'end' => '08:30', 'items' => [['icon' => 'book-open', 'label' => 'Buku Fisika'], ['icon' => 'calculator', 'label' => 'Kalkulator']]],
            ['subject' => 'Bahasa Inggris', 'teacher' => 'Miss Anna', 'room' => 'Ruang 1C', 'start' => '08:30', 'end' => '10:00', 'items' => [['icon' => 'book-open', 'label' => 'Buku Bahasa Inggris']]],
            ['subject' => 'Seni Budaya', 'teacher' => 'Pak Anto', 'room' => 'Ruang Seni', 'start' => '10:15', 'end' => '11:45', 'items' => [['icon' => 'document-text', 'label' => 'Buku Gambar'], ['icon' => 'pencil', 'label' => 'Pensil Warna']]],
        ],
        'Rabu' => [
            ['subject' => 'Matematika', 'teacher' => 'Bu Sari', 'room' => 'Ruang 3A', 'start' => '07:00', 'end' => '08:30', 'items' => [['icon' => 'book-open', 'label' => 'Buku Matematika'], ['icon' => 'calculator', 'label' => 'Kalkulator']]],
            ['subject' => 'Kimia', 'teacher' => 'Pak Hadi', 'room' => 'Lab Kimia', 'start' => '08:30', 'end' => '10:00', 'items' => [['icon' => 'book-open', 'label' => 'Buku Kimia']]],
            ['subject' => 'PPKN', 'teacher' => 'Bu Yuli', 'room' => 'Ruang 2A', 'start' => '10:15', 'end' => '11:45', 'items' => [['icon' => 'book-open', 'label' => 'Buku PPKN']]],
        ],
        'Kamis' => [
            ['subject' => 'Matematika', 'teacher' => 'Bu Sari', 'room' => 'Ruang 3A', 'start' => '07:00', 'end' => '08:30', 'items' => [['icon' => 'book-open', 'label' => 'Buku Matematika'], ['icon' => 'calculator', 'label' => 'Kalkulator']]],
            ['subject' => 'Fisika', 'teacher' => 'Bu Dewi', 'room' => 'Ruang 3A', 'start' => '08:30', 'end' => '10:00', 'items' => [['icon' => 'book-open', 'label' => 'Buku Fisika'], ['icon' => 'calculator', 'label' => 'Kalkulator']]],
            ['subject' => 'Bahasa Indonesia', 'teacher' => 'Pak Budi', 'room' => 'Ruang 2B', 'start' => '10:15', 'end' => '11:45', 'items' => [['icon' => 'book-open', 'label' => 'Buku Bahasa Indonesia'], ['icon' => 'document-text', 'label' => 'Buku Tulis']]],
            ['subject' => 'Olahraga', 'teacher' => 'Pak Joko', 'room' => 'Lapangan', 'start' => '13:00', 'end' => '14:30', 'items' => [['icon' => 'tag', 'label' => 'Sepatu Olahraga'], ['icon' => 'beaker', 'label' => 'Botol Minum']]],
        ],
        'Jumat' => [
            ['subject' => 'Pendidikan Agama', 'teacher' => 'Pak Yusuf', 'room' => 'Ruang 1A', 'start' => '07:00', 'end' => '08:30', 'items' => [['icon' => 'book-open', 'label' => 'Buku Agama']]],
            ['subject' => 'IPS', 'teacher' => 'Bu Nita', 'room' => 'Ruang 2C', 'start' => '08:30', 'end' => '10:00', 'items' => [['icon' => 'book-open', 'label' => 'Buku IPS']]],
            ['subject' => 'Prakarya', 'teacher' => 'Pak Deni', 'room' => 'Ruang Prakarya', 'start' => '10:15', 'end' => '11:45', 'items' => [['icon' => 'cube', 'label' => 'Alat Prakarya']]],
        ],
        'Sabtu' => [
            ['subject' => 'Ekstrakurikuler Pramuka', 'teacher' => 'Pembina Pramuka', 'room' => 'Lapangan', 'start' => '08:00', 'end' => '10:00', 'items' => [['icon' => 'tag', 'label' => 'Seragam Pramuka'], ['icon' => 'tag', 'label' => 'Tali Pramuka']]],
        ],
    ];

    $todayClasses = collect($weeklySchedule[$todayName] ?? [])
        ->sortBy('start')
        ->values()
        ->all();

    $firstClassItemCount = count($todayClasses[0]['items'] ?? []);

    // Daftar barang dari halaman Barang (Items) — dipakai buat multi-select di drawer Tambah Jadwal
    $availableItems = ['Laptop', 'Buku Fisika', 'Kalkulator', 'Botol Minum', 'Buku Matematika', 'Sepatu Olahraga'];
@endphp

@push('scripts')
<script>
    function getClassStatus(start, end) {
        const now = new Date();
        const todayStr = now.toISOString().slice(0, 10);
        const startTime = new Date(`${todayStr}T${start}:00`);
        const endTime = new Date(`${todayStr}T${end}:00`);

        if (now < startTime) return 'upcoming';
        if (now >= startTime && now <= endTime) return 'now';
        return 'completed';
    }

    function schedulePage(todayName) {
        return {
            modeView: 'today',
            selectedDay: todayName,
        };
    }
</script>
@endpush

<x-layouts.dashboard title="Jadwal — InCase">
    <div x-data="schedulePage('{{ $todayName }}')" class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <div class="mx-auto max-w-7xl px-6 py-8 sm:px-8">
                {{-- Header + Segmented control --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Jadwal
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Lihat jadwal pelajaran hari ini atau seminggu penuh.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="inline-flex items-center gap-1 rounded-full border border-border bg-card p-1">
                            <button
                                type="button"
                                @click="modeView = 'today'"
                                :class="modeView === 'today' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition-colors"
                            >
                                Hari Ini
                            </button>
                            <button
                                type="button"
                                @click="modeView = 'weekly'"
                                :class="modeView === 'weekly' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition-colors"
                            >
                                Mingguan
                            </button>
                        </div>

                        <button
                            type="button"
                            @click="$dispatch('open-add-schedule-drawer')"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            <x-icon.plus class="h-4 w-4" />
                            Tambah Jadwal
                        </button>
                    </div>
                </div>

                {{-- ============ TODAY MODE ============ --}}
                <div x-show="modeView === 'today'">
                    {{-- Welcome section --}}
                    <div class="mt-6 flex items-center justify-between rounded-2xl border border-border bg-card px-6 py-4">
                        <p class="text-sm font-medium text-foreground">
                            Selamat Pagi, Nopal.
                            <span class="text-muted-foreground">
                                {{ count($todayClasses) }} kelas hari ini ({{ $todayName }})
                                @if ($firstClassItemCount > 0)
                                    , {{ $firstClassItemCount }} barang wajib sebelum kelas pertama
                                @endif
                                .
                            </span>
                        </p>
                    </div>

                    <div class="mt-6 flex flex-col gap-4">
                        @forelse ($todayClasses as $class)
                            <x-class-card
                                :subject="$class['subject']"
                                :teacher="$class['teacher']"
                                :room="$class['room']"
                                :start="$class['start']"
                                :end="$class['end']"
                                :items="$class['items']"
                            />
                        @empty
                            <x-empty-state
                                icon="calendar-days"
                                title="Belum ada jadwal"
                                description="Belum ada kelas yang terjadwal hari ini. Tambahkan jadwal baru untuk mulai memantau."
                                button-label="Tambah Jadwal"
                                button-click="$dispatch('open-add-schedule-drawer')"
                            />
                        @endforelse
                    </div>
                </div>

                {{-- ============ WEEKLY MODE ============ --}}
                <div x-show="modeView === 'weekly'" x-cloak>
                    {{-- Day tabs --}}
                    <div class="mt-6 flex flex-wrap gap-2">
                        @foreach (array_keys($weeklySchedule) as $day)
                            <button
                                type="button"
                                @click="selectedDay = '{{ $day }}'"
                                :class="selectedDay === '{{ $day }}' ? 'bg-primary text-primary-foreground' : 'bg-card text-muted-foreground hover:text-foreground border border-border'"
                                class="rounded-full px-4 py-2 text-sm font-semibold transition-colors"
                            >
                                {{ $day }}
                            </button>
                        @endforeach
                    </div>

                    {{-- Per-day class list --}}
                    @foreach ($weeklySchedule as $day => $classes)
                        <div x-show="selectedDay === '{{ $day }}'" x-cloak class="mt-6 flex flex-col gap-4">
                            @forelse ($classes as $class)
                                <div
                                    @click="$dispatch('open-class-drawer', {
                                        subject: '{{ $class['subject'] }}',
                                        teacher: '{{ $class['teacher'] }}',
                                        room: '{{ $class['room'] }}',
                                        start: '{{ $class['start'] }}',
                                        end: '{{ $class['end'] }}',
                                        items: {{ collect($class['items'])->pluck('label')->toJson() }},
                                    })"
                                    class="cursor-pointer rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <h3 class="text-lg font-bold text-foreground">{{ $class['subject'] }}</h3>
                                            <p class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                                                <x-icon.user class="h-3.5 w-3.5" />
                                                {{ $class['teacher'] }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-icon.map-pin class="h-4 w-4" />
                                            {{ $class['room'] }}
                                        </span>
                                        <span class="inline-flex items-center gap-1.5">
                                            <x-icon.clock class="h-4 w-4" />
                                            {{ $class['start'] }} – {{ $class['end'] }}
                                        </span>
                                    </div>

                                    @if (count($class['items']))
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            @foreach ($class['items'] as $item)
                                                <x-required-item-badge :icon="$item['icon']" :label="$item['label']" />
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            @empty
                                <x-empty-state
                                    icon="calendar-days"
                                    title="Belum ada jadwal"
                                    description="Belum ada kelas yang terjadwal di hari ini. Tambahkan jadwal baru untuk hari ini."
                                    button-label="Tambah Jadwal"
                                    button-click="$dispatch('open-add-schedule-drawer')"
                                />
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>
        </main>

        <x-class-drawer />
        <x-add-schedule-drawer :available-items="$availableItems" />
    </div>
</x-layouts.dashboard>