<x-layouts.auth title="Masuk — InCase">
    <div class="w-full max-w-md rounded-3xl border border-border bg-card p-8 shadow-[0_24px_60px_-24px_rgba(15,23,42,0.15)] sm:p-10">
        {{-- Header --}}
        <div class="flex items-center gap-3 lg:hidden">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                <x-icon.cube class="h-5 w-5" />
            </span>
            <span class="text-lg font-bold tracking-tight text-foreground">InCase</span>
        </div>

        <div class="mt-6 lg:mt-0">
            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                Welcome Back
            </h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Masuk ke akun InCase kamu untuk memantau kelengkapan tas hari ini.
            </p>
        </div>

        {{-- Session status --}}
        @if (session('status'))
            <div class="mt-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-8 flex flex-col gap-5">
            @csrf

            <x-auth-input
                icon="envelope"
                label="Email"
                name="email"
                type="email"
                placeholder="nama@sekolah.sch.id"
                required
                autofocus
                autocomplete="username"
            />

            <div>
                <div class="mb-1.5 flex items-center justify-between">
                    <label for="password" class="block text-sm font-medium text-foreground">
                        Password
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-primary hover:underline">
                            Forgot Password?
                        </a>
                    @endif
                </div>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                        <x-icon.lock-closed class="h-5 w-5" />
                    </span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>
                @error('password')
                    <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2.5">
                <input
                    type="checkbox"
                    name="remember"
                    class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30"
                >
                <span class="text-sm text-muted-foreground">Remember Me</span>
            </label>

            <button
                type="submit"
                class="mt-2 inline-flex w-full items-center justify-center gap-2 rounded-full bg-primary py-3 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
            >
                Login
                <x-icon.arrow-right class="h-4 w-4" />
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-muted-foreground">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-semibold text-primary hover:underline">
                Daftar sekarang
            </a>
        </p>
    </div>
</x-layouts.auth>
