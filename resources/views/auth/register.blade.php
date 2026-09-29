<x-layouts.auth title="Daftar — InCase">
    <div
        class="w-full max-w-2xl rounded-3xl border border-border/70 bg-white/80 p-8 shadow-[0_24px_60px_-24px_rgba(15,23,42,0.15)] backdrop-blur-xl sm:p-10">
        {{-- Header --}}
        <div class="flex items-center gap-3 lg:hidden">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                <x-icon.viewfinder-circle class="h-5 w-5" />
            </span>
            <span class="text-lg font-bold tracking-tight text-foreground">InCase</span>
        </div>

        <div class="mt-6 lg:mt-0">
            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                Buat Akun Kamu
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Daftar untuk mulai memantau kelengkapan tas sekolahmu dengan InCase.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="mt-8 flex flex-col gap-5">
            @csrf
            <input type="hidden" name="role" value="student">

            {{-- Baris 1 — Nama Lengkap | Email --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <x-auth-input icon="user" label="Nama Lengkap" name="name" type="text"
                    placeholder="Nama lengkap kamu" required autofocus autocomplete="name" />

                <x-auth-input icon="envelope" label="Email" name="email" type="email"
                    placeholder="nama@sekolah.sch.id" required autocomplete="username" />
            </div>

            {{-- Baris 2 — Kelas (satu dropdown, dikelompokkan per tingkat) --}}
            @php
                $groupedClasses = $classes
                    ->sortBy('name')
                    ->groupBy('grade')
                    ->sortBy(fn($items, $grade) => strlen($grade) . $grade);
            @endphp
            <div>
                <label for="class_id" class="mb-1.5 block text-sm font-medium text-foreground">
                    Kelas
                </label>
                <div class="relative">
                    <span
                        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                        <x-icon.academic-cap class="h-5 w-5" />
                    </span>
                    <select name="class_id" id="class_id" required
                        class="block w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        <option value="" disabled {{ old('class_id') ? '' : 'selected' }}>Pilih kelas kamu</option>
                        @foreach ($groupedClasses as $grade => $items)
                            <optgroup label="Kelas {{ $grade }}">
                                @foreach ($items as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (string) old('class_id') === (string) $c->id ? 'selected' : '' }}>
                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                @if ($classes->isEmpty())
                    <p class="mt-1.5 text-xs font-medium text-destructive">
                        Belum ada kelas terdaftar. Hubungi admin sekolah.
                    </p>
                @endif
                @error('class_id')
                    <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            {{-- Baris 3 — Kata Sandi | Konfirmasi --}}
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-foreground">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.lock-closed class="h-5 w-5" />
                        </span>
                        <input type="password" id="password" name="password" placeholder="••••••••" required
                            autocomplete="new-password"
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-foreground">
                        Konfirmasi Kata Sandi
                    </label>
                    <div class="relative">
                        <span
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                            <x-icon.lock-closed class="h-5 w-5" />
                        </span>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            placeholder="••••••••" required autocomplete="new-password"
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                    </div>
                </div>
            </div>

            <button type="submit"
                class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary py-3 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90">
                Daftar
                <x-icon.arrow-right class="h-4 w-4" />
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-muted-foreground">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">
                Masuk di sini
            </a>
        </p>
    </div>
</x-layouts.auth>