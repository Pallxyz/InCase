<section class="rounded-[24px] border border-border bg-card p-6 shadow-sm sm:p-8">
    <div class="mb-6">
        <h3 class="text-lg font-bold text-foreground">
            Ubah Kata Sandi
        </h3>

        <p class="mt-1 text-sm text-muted-foreground">
            Gunakan kata sandi yang kuat agar akun tetap aman.
        </p>
    </div>

    @if (session('status') === 'password-updated')
        <div class="mb-6 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
            Kata sandi berhasil diperbarui.
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('password.update') }}"
        class="space-y-5"
    >
        @csrf
        @method('PUT')

        <div>
            <label
                for="update_password_current_password"
                class="mb-1.5 block text-sm font-medium text-foreground"
            >
                Kata Sandi Saat Ini
            </label>

            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                    <x-icon.lock-closed class="h-4 w-4"/>
                </span>

                <x-text-input
                    id="update_password_current_password"
                    name="current_password"
                    type="password"
                    autocomplete="current-password"
                    class="block w-full rounded-xl border-border bg-background py-2.5 pl-10 pr-3.5"
                />
            </div>

            <x-input-error
                :messages="$errors->updatePassword->get('current_password')"
                class="mt-2"
            />
        </div>

        <div class="grid gap-5 sm:grid-cols-2">

            <div>
                <label
                    for="update_password_password"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    Kata Sandi Baru
                </label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                        <x-icon.key class="h-4 w-4"/>
                    </span>

                    <x-text-input
                        id="update_password_password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        class="block w-full rounded-xl border-border bg-background py-2.5 pl-10 pr-3.5"
                    />
                </div>

                <x-input-error
                    :messages="$errors->updatePassword->get('password')"
                    class="mt-2"
                />
            </div>

            <div>
                <label
                    for="update_password_password_confirmation"
                    class="mb-1.5 block text-sm font-medium text-foreground"
                >
                    Konfirmasi Kata Sandi
                </label>

                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                        <x-icon.key class="h-4 w-4"/>
                    </span>

                    <x-text-input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="block w-full rounded-xl border-border bg-background py-2.5 pl-10 pr-3.5"
                    />
                </div>

                <x-input-error
                    :messages="$errors->updatePassword->get('password_confirmation')"
                    class="mt-2"
                />
            </div>

        </div>

        <div class="flex items-center gap-4 pt-2">
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
            >
                Simpan Perubahan
            </button>

            @if (session('status') === 'password-updated')
                <span
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm font-medium text-success"
                >
                    Berhasil disimpan.
                </span>
            @endif
        </div>
    </form>
</section>