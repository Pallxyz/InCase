<x-layouts.dashboard title="Tahun Ajaran — InCase">
    <div class="flex h-screen bg-slate-50">
        <x-sidebar />

        <main class="h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="Tahun Ajaran — InCase" />

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">

                @if (session('success'))
                    <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->has('source_id'))
                    <div class="mb-6 rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-medium text-red-600">
                        {{ $errors->first('source_id') }}
                    </div>
                @endif

                <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Kelola Tahun Ajaran</h1>
                <p class="mt-1 text-sm text-slate-500">Aktifkan tahun ajaran & semester yang sedang berjalan. Jadwal baru otomatis mengikuti yang aktif.</p>

                {{-- FORM TAMBAH --}}
                <form method="POST" action="{{ route('academic-years.store') }}" class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    @csrf
                    <p class="mb-3 text-sm font-semibold text-slate-700">Tambah Tahun Ajaran Baru</p>

                    @if ($errors->any() && !$errors->has('source_id'))
                        <div class="mb-3 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-600">
                            <ul class="list-disc pl-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Tahun Mulai</label>
                            <input type="number" name="year_start" value="{{ old('year_start') }}" placeholder="2025" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Tahun Akhir</label>
                            <input type="number" name="year_end" value="{{ old('year_end') }}" placeholder="2026" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-slate-600">Semester</label>
                            <select name="semester" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-nonefocus:ring-4 focus:ring-blue-500/10">
                                <option value="ganjil" @selected(old('semester') === 'ganjil')>Ganjil</option>
                                <option value="genap" @selected(old('semester') === 'genap')>Genap</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="mt-4 w-full rounded-full bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Tambah
                    </button>
                </form>

                {{-- LIST --}}
                <div class="mt-6 flex flex-col gap-3">
                    @forelse ($academicYears as $year)
                        @php
                            $otherYears = $academicYears->reject(fn ($y) => $y->id === $year->id);
                        @endphp
                        <div class="rounded-2xl border {{ $year->is_active ? 'border-blue-200 bg-blue-50/60' : 'border-slate-200 bg-white' }} px-4 py-3.5">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ $year->label }}
                                        @if ($year->is_active)
                                            <span class="ml-1.5 rounded-full bg-blue-600 px-2 py-0.5 text-[10px] font-semibold text-white">Aktif</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-slate-500">{{ $year->subjects_count }} jadwal</p>
                                </div>

                                <div class="flex flex-wrap items-center gap-2">
                                    @unless ($year->is_active)
                                        <form method="POST" action="{{ route('academic-years.activate', $year) }}">
                                            @csrf
                                            <button type="submit" class="rounded-full border border-blue-200 px-3.5 py-1.5 text-xs font-semibold text-blue-600 hover:bg-blue-50">
                                                Aktifkan
                                            </button>
                                        </form>
                                    @endunless

                                    <a href="{{ route('academic-years.export', $year) }}" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                                        <x-icon.arrow-down-tray class="h-3.5 w-3.5" />
                                        Unduh CSV
                                    </a>

                                    @if ($otherYears->isNotEmpty())
                                        <button
                                            type="button"
                                            onclick="document.getElementById('copy-modal-{{ $year->id }}').classList.remove('hidden')"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 px-3.5 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50"
                                        >
                                            Salin Jadwal
                                        </button>
                                    @endif

                                    @if (!$year->is_active && $year->subjects_count === 0)
                                        <form method="POST" action="{{ route('academic-years.destroy', $year) }}" onsubmit="return confirm('Hapus tahun ajaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-full border border-red-200 px-3.5 py-1.5 text-xs font-semibold text-red-500 hover:bg-red-50">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Modal pilih tahun ajaran sumber untuk disalin KE {{ $year->label }} --}}
                        @if ($otherYears->isNotEmpty())
                            <div id="copy-modal-{{ $year->id }}" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 px-4">
                                <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-lg">
                                    <p class="text-sm font-semibold text-slate-900">Salin jadwal ke {{ $year->label }}</p>
                                    <p class="mt-1 text-xs text-slate-500">
                                        Mapel, kelas, guru, hari, jam, ruang, dan barang wajib disalin. PR dan tanda ujian tidak ikut.
                                        Jadwal yang sudah ada / bentrok otomatis dilewati.
                                    </p>

                                    <form method="POST" action="{{ route('academic-years.copy-schedules', $year) }}" class="mt-4">
                                        @csrf
                                        <label class="mb-1 block text-xs font-medium text-slate-600">Salin dari tahun ajaran</label>
                                        <select name="source_id" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                                            <option value="" disabled selected>Pilih tahun ajaran asal</option>
                                            @foreach ($otherYears as $source)
                                                <option value="{{ $source->id }}">{{ $source->label }} ({{ $source->subjects_count }} jadwal)</option>
                                            @endforeach
                                        </select>

                                        <div class="mt-4 flex gap-2">
                                            <button
                                                type="button"
                                                onclick="document.getElementById('copy-modal-{{ $year->id }}').classList.add('hidden')"
                                                class="flex-1 rounded-full border border-slate-200 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50"
                                            >
                                                Batal
                                            </button>
                                            <button type="submit" class="flex-1 rounded-full bg-blue-600 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                                                Salin
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-sm text-slate-400">Belum ada tahun ajaran.</p>
                    @endforelse
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>