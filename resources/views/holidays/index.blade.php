<x-layouts.dashboard title="Hari Libur — InCase">
    <div class="flex h-screen bg-slate-50">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">

                @if (session('success'))
                    <div class="mb-4 rounded-xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                <h1 class="text-2xl font-bold text-slate-900">Hari Libur</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Kelola tanggal libur sekolah. Kosongkan "Kelas" kalau libur berlaku buat semua kelas.
                </p>

                <form method="POST" action="{{ route('holidays.store') }}" class="mt-6 rounded-2xl border border-border bg-white p-5">
                    @csrf

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Tanggal</label>
                            <input type="date" name="date" required class="block w-full rounded-xl border border-border px-3.5 py-2.5 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @error('date')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Libur</label>
                            <input type="text" name="name" required placeholder="Contoh: HUT RI, Ujian Kelas 12" class="block w-full rounded-xl border border-border px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                            @error('name')
                                <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas (opsional)</label>
                            <select name="class_id" class="block w-full appearance-none rounded-xl border border-border px-3.5 py-2.5 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                                <option value="">Semua kelas</option>
                                @foreach ($classes as $class)
                                    <option value="{{ $class->id }}">{{ $class->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="mt-4 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90">
                        Tambah Libur
                    </button>
                </form>

                <div class="mt-6 space-y-2">
                    @forelse ($holidays as $holiday)
                        <div class="flex items-center justify-between rounded-xl border border-border bg-white px-4 py-3">
                            <div>
                                <p class="text-sm font-semibold text-foreground">{{ $holiday->name }}</p>
                                <p class="text-xs text-muted-foreground">
                                    {{ \Carbon\Carbon::parse($holiday->date)->translatedFormat('d F Y') }}
                                    — {{ $holiday->schoolClass?->name ?? 'Semua kelas' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('holidays.destroy', $holiday) }}" onsubmit="return confirm('Hapus hari libur ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-destructive hover:bg-destructive/10">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    @empty
                        <p class="rounded-xl border border-dashed border-border py-8 text-center text-sm text-muted-foreground">
                            Belum ada hari libur yang ditambahkan.
                        </p>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>