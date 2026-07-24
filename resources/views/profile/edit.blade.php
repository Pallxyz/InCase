@php
    $user = auth()->user();
@endphp

<x-layouts.dashboard title="Profil — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:overflow-y-hidden lg:ml-64">
            <div class="mx-auto max-w-6xl px-6 py-8 sm:px-8">
                {{-- Page Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Profil
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Kelola informasi akun kamu.
                        </p>
                    </div>

                    <button
                        type="submit"
                        form="profile-form"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                    >
                        Simpan Perubahan
                    </button>
                </div>

                <div class="mt-6 flex flex-col gap-6">

                    {{-- ============ HEADER: AVATAR / GANTI FOTO ============ --}}
                    <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm sm:p-8">
                        @if (session('status') === 'profile-updated')
                            <div
                                x-data="{ show: true }"
                                x-init="setTimeout(() => show = false, 3000)"
                                x-show="show"
                                x-transition
                                class="mb-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success"
                            >
                                Profil berhasil diperbarui.
                            </div>
                        @endif

                        <div class="flex flex-col items-center gap-4 sm:flex-row" x-data="{ preview: null }">
                            @if ($user->avatar)
                                <img
                                    src="{{ asset('storage/' . $user->avatar) }}"
                                    alt="{{ $user->name }}"
                                    class="h-24 w-24 shrink-0 rounded-full object-cover"
                                    x-show="!preview"
                                >
                            @else
                                <span class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full bg-primary text-3xl font-bold text-primary-foreground" x-show="!preview">
                                    {{ strtoupper(substr($user->name ?? 'N', 0, 1)) }}
                                </span>
                            @endif

                            <img
                                x-show="preview"
                                :src="preview"
                                class="h-24 w-24 shrink-0 rounded-full object-cover"
                                style="display: none;"
                            >

                            <div class="flex flex-col items-center gap-2 sm:items-start">
                                <input
                                    type="file"
                                    id="avatar-input"
                                    name="avatar"
                                    form="profile-form"
                                    accept="image/png, image/jpeg, image/webp"
                                    class="hidden"
                                    @change="
                                        const file = $event.target.files[0];
                                        if (file) { preview = URL.createObjectURL(file); }
                                    "
                                >
                                <button
                                    type="button"
                                    onclick="document.getElementById('avatar-input').click()"
                                    class="inline-flex items-center gap-2 rounded-full border border-border bg-background px-4 py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
                                >
                                    <x-icon.camera class="h-3.5 w-3.5" />
                                    Ganti Foto
                                </button>
                                @error('avatar')
                                    <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="flex flex-col items-center gap-1 text-center sm:ml-auto sm:items-end sm:text-right">
                                <div class="flex items-center gap-2">
                                    <h2 class="text-lg font-bold text-foreground">{{ $user->name ?? 'Nopal' }}</h2>
                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary">
                                        {{ ucfirst($user->role ?? 'student') === 'Teacher' ? 'Guru' : 'Siswa' }}
                                    </span>
                                </div>
                                <p class="text-sm text-muted-foreground">{{ $user->school_name ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- ============ GRID: EDIT PROFILE (kiri) | SIDEBAR CARDS (kanan) ============ --}}
                    <div class="grid items-start gap-6 lg:grid-cols-3">

                        {{-- ============ KIRI — EDIT PROFILE ============ --}}
                        <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm sm:p-8 lg:col-span-2">
                            <h3 class="text-lg font-bold text-foreground">Edit Profil</h3>

                            <form id="profile-form" method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="mt-5 grid gap-5 sm:grid-cols-2">
                                @csrf
                                @method('patch')

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Lengkap</label>
                                    <input
                                        type="text"
                                        name="name"
                                        value="{{ old('name', $user->name ?? '') }}"
                                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                    >
                                    @error('name')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Alamat Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email', $user->email ?? '') }}"
                                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                    >
                                    @error('email')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nomor Telepon</label>
                                    @if ($user->phone)
                                        <input
                                            type="text"
                                            value="{{ $user->phone }}"
                                            disabled
                                            class="block w-full rounded-xl border border-border bg-muted px-3.5 py-2.5 text-sm text-muted-foreground"
                                        >
                                        <p class="mt-1.5 text-xs text-muted-foreground">Nomor telepon sudah diatur dan tidak dapat diubah.</p>
                                    @else
                                        <input
                                            type="text"
                                            name="phone"
                                            value="{{ old('phone') }}"
                                            placeholder="0812xxxxxxxx"
                                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                        >
                                    @endif
                                    @error('phone')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Sekolah</label>
                                    @if ($user->school_name)
                                        <input
                                            type="text"
                                            value="{{ $user->school_name }}"
                                            disabled
                                            class="block w-full rounded-xl border border-border bg-muted px-3.5 py-2.5 text-sm text-muted-foreground"
                                        >
                                        <p class="mt-1.5 text-xs text-muted-foreground">Sekolah sudah diatur dan tidak dapat diubah.</p>
                                    @else
                                        <input
                                            type="text"
                                            name="school_name"
                                            value="{{ old('school_name') }}"
                                            placeholder="Nama sekolah"
                                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                        >
                                    @endif
                                    @error('school_name')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kelas</label>
                                    <input
                                        type="text"
                                        value="{{ $user->schoolClass->name ?? '-' }}"
                                        disabled
                                        class="block w-full rounded-xl border border-border bg-muted px-3.5 py-2.5 text-sm text-muted-foreground"
                                    >
                                    <p class="mt-1.5 text-xs text-muted-foreground">Kelas sudah diatur dan tidak dapat diubah.</p>
                                </div>

                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nomor Induk Siswa</label>
                                    @if ($user->student_id)
                                        <input
                                            type="text"
                                            value="{{ $user->student_id }}"
                                            disabled
                                            class="block w-full rounded-xl border border-border bg-muted px-3.5 py-2.5 text-sm text-muted-foreground"
                                        >
                                        <p class="mt-1.5 text-xs text-muted-foreground">NIS sudah diatur dan tidak dapat diubah.</p>
                                    @else
                                        <input
                                            type="text"
                                            name="student_id"
                                            value="{{ old('student_id') }}"
                                            placeholder="Contoh: 2026001234"
                                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                        >
                                    @endif
                                    @error('student_id')
                                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                    @enderror
                                </div>
                            </form>
                        </div>

                        {{-- ============ KANAN — STACK: AKSI CEPAT / UBAH PASSWORD / HAPUS AKUN ============ --}}
                        <div class="flex flex-col gap-6 lg:max-h-[calc(100vh-16rem)] lg:overflow-y-auto lg:pr-1 lg:pb-8">

                            {{-- Aksi Cepat --}}
                            <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                                <h3 class="text-sm font-semibold text-foreground">Aksi Cepat</h3>

                                <div class="mt-4 flex flex-col gap-2.5">
                                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                                        <x-icon.squares-2x2 class="h-4 w-4 text-muted-foreground" />
                                        Lihat Dasbor
                                    </a>
                                    <a href="{{ route('items.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                                        <x-icon.cube class="h-4 w-4 text-muted-foreground" />
                                        Kelola Barang
                                    </a>
                                    <a href="{{ route('schedule.index') }}" class="inline-flex items-center gap-2.5 rounded-xl border border-border px-4 py-2.5 text-sm font-medium text-foreground transition-colors hover:bg-muted">
                                        <x-icon.calendar-days class="h-4 w-4 text-muted-foreground" />
                                        Lihat Jadwal
                                    </a>
                                </div>
                            </div>

                            {{-- Ubah Kata Sandi --}}
                            <div class="rounded-[24px] border border-border bg-card p-6 shadow-sm">
                                <h3 class="text-lg font-bold text-foreground">Ubah Kata Sandi</h3>

                                @if (session('status') === 'password-updated')
                                    <div
                                        x-data="{ show: true }"
                                        x-init="setTimeout(() => show = false, 3000)"
                                        x-show="show"
                                        x-transition
                                        class="mt-4 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success"
                                    >
                                        Kata sandi berhasil diperbarui.
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('password.update') }}" class="mt-5 flex flex-col gap-5">
                                    @csrf
                                    @method('put')

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Kata Sandi Saat Ini</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                                                <x-icon.lock-closed class="h-4 w-4" />
                                            </span>
                                            <input
                                                type="password"
                                                name="current_password"
                                                class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                            >
                                        </div>
                                        @error('current_password', 'updatePassword')
                                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Kata Sandi Baru</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                                                <x-icon.key class="h-4 w-4" />
                                            </span>
                                            <input
                                                type="password"
                                                name="password"
                                                class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                            >
                                        </div>
                                        @error('password', 'updatePassword')
                                            <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="mb-1.5 block text-sm font-medium text-foreground">Konfirmasi Kata Sandi</label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                                                <x-icon.key class="h-4 w-4" />
                                            </span>
                                            <input
                                                type="password"
                                                name="password_confirmation"
                                                class="block w-full rounded-xl border border-border bg-background py-2.5 pl-10 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                            >
                                        </div>
                                    </div>

                                    <button
                                        type="submit"
                                        class="mt-1 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                                    >
                                        Perbarui Kata Sandi
                                    </button>
                                </form>
                            </div>

                            {{-- Zona Berbahaya --}}
                            <div
                                x-data="{ confirmOpen: false }"
                                class="rounded-[24px] border border-destructive/20 bg-card p-6 shadow-sm"
                            >
                                <h3 class="text-sm font-semibold text-destructive">Zona Berbahaya</h3>
                                <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                                    Tindakan ini gak bisa dibatalin.
                                </p>

                                <button
                                    type="button"
                                    @click="confirmOpen = true"
                                    class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full border border-destructive/30 px-4 py-2.5 text-sm font-semibold text-destructive transition-colors hover:bg-destructive/10"
                                >
                                    <x-icon.trash class="h-4 w-4" />
                                    Hapus Akun
                                </button>

                                {{-- Confirmation modal --}}
                                <div
                                    x-show="confirmOpen"
                                    x-cloak
                                    class="fixed inset-0 z-50 flex items-center justify-center px-4"
                                    style="display: none;"
                                >
                                    <div
                                        x-show="confirmOpen"
                                        x-transition:enter="transition-opacity ease-out duration-200"
                                        x-transition:enter-start="opacity-0"
                                        x-transition:enter-end="opacity-100"
                                        x-transition:leave="transition-opacity ease-in duration-150"
                                        x-transition:leave-start="opacity-100"
                                        x-transition:leave-end="opacity-0"
                                        @click="confirmOpen = false"
                                        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                                    ></div>

                                    <div
                                        x-show="confirmOpen"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-150"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="relative w-full max-w-sm rounded-[24px] bg-card p-6 shadow-2xl"
                                    >
                                        <h3 class="text-lg font-bold text-foreground">Hapus akun kamu?</h3>
                                        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                                            Semua data kamu akan hilang permanen. Masukkan kata sandi untuk konfirmasi.
                                        </p>

                                        <form method="POST" action="{{ route('profile.destroy') }}" class="mt-4 flex flex-col gap-4">
                                            @csrf
                                            @method('delete')

                                            <input
                                                type="password"
                                                name="password"
                                                placeholder="Kata sandi"
                                                class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                            >
                                            @error('password', 'userDeletion')
                                                <p class="text-xs font-medium text-destructive">{{ $message }}</p>
                                            @enderror

                                            <div class="flex items-center gap-3">
                                                <button
                                                    type="button"
                                                    @click="confirmOpen = false"
                                                    class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                                                >
                                                    Batal
                                                </button>
                                                <button
                                                    type="submit"
                                                    class="flex-1 rounded-full bg-destructive py-2.5 text-sm font-semibold text-white transition-colors hover:bg-destructive/90"
                                                >
                                                    Ya, Hapus
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>