<x-layouts.dashboard title="Akun Guru — InCase">
    <div class="flex h-screen bg-slate-50">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="Akun Guru — InCase" />

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-10">

                @if (session('success'))
                    <div class="mb-4 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- Password acak yang baru dibuat -- tampil sekali doang, gak disimpan di server. --}}
                @if (session('generated_password'))
                    <div class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        <p class="font-semibold">Akun berhasil dibuat. Catat sekarang, password ini cuma tampil sekali!</p>
                        <div class="mt-2 space-y-1 font-mono text-xs">
                            <p>Email: <span class="font-semibold">{{ session('generated_email') }}</span></p>
                            <p>Password: <span class="font-semibold">{{ session('generated_password') }}</span></p>
                        </div>
                    </div>
                @endif

                <h1 class="text-xl font-bold text-slate-900 sm:text-2xl">Akun Guru</h1>
                <p class="mt-1 text-sm text-slate-500">
                    Guru tidak bisa mendaftar sendiri — admin yang membuatkan akunnya di sini.
                </p>

                <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-[380px_1fr] lg:items-start">
                    {{-- FORM TAMBAH --}}
                    <form method="POST" action="{{ route('teachers.store') }}" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:sticky lg:top-10">
                        @csrf
                        <p class="mb-3 text-sm font-semibold text-slate-700">Tambah Guru Baru</p>

                        @if ($errors->any())
                            <div class="mb-3 rounded-xl bg-red-50 px-3 py-2 text-xs text-red-600">
                                <ul class="list-disc pl-4">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="flex flex-col gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Nama</label>
                                <input type="text" name="name" value="{{ old('name') }}" required
                                    placeholder="Nama lengkap guru"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    placeholder="guru@sekolah.sch.id"
                                    class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10">
                            </div>
                        </div>

                        <p class="mt-2 text-xs text-slate-400">Password awal digenerate otomatis dan cuma ditampilkan sekali setelah akun dibuat.</p>

                        <button type="submit" class="mt-4 w-full rounded-full bg-blue-600 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                            Buat Akun
                        </button>
                    </form>

                    {{-- LIST GURU --}}
                    <div class="flex flex-col gap-3">
                        @forelse ($teachers as $teacher)
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-3.5">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                        <x-icon.identification class="h-5 w-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-semibold text-slate-900">{{ $teacher->name }}</p>
                                        <p class="truncate text-xs text-slate-500">{{ $teacher->email }}</p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('teachers.destroy', $teacher) }}"
                                    onsubmit="return confirm('Hapus akun guru {{ $teacher->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg px-3 py-1.5 text-xs font-semibold text-destructive hover:bg-destructive/10">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-dashed border-slate-200 py-8 text-center text-sm text-slate-500">
                                Belum ada akun guru. Tambahkan lewat form di samping.
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>