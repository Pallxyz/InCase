<section class="rounded-[24px] border border-destructive/20 bg-card p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-destructive">
        Zona Berbahaya
    </h3>

    <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
        Menghapus akun akan menghapus seluruh data secara permanen. Tindakan ini tidak dapat dibatalkan.
    </p>

    <button
        type="button"
        x-data
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-full border border-destructive/30 px-4 py-2.5 text-sm font-semibold text-destructive transition-colors hover:bg-destructive/10"
    >
        <x-icon.trash class="h-4 w-4" />
        Hapus Akun
    </button>

    <x-modal
        name="confirm-user-deletion"
        :show="$errors->userDeletion->isNotEmpty()"
        focusable
    >
        <form
            method="POST"
            action="{{ route('profile.destroy') }}"
            class="p-6"
        >
            @csrf
            @method('DELETE')

            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive">
                <x-icon.trash class="h-7 w-7" />
            </div>

            <h2 class="mt-4 text-center text-xl font-bold text-foreground">
                Hapus akun?
            </h2>

            <p class="mt-2 text-center text-sm leading-relaxed text-muted-foreground">
                Semua data akun, barang, jadwal, dan riwayat akan dihapus permanen.
                Masukkan kata sandi untuk konfirmasi.
            </p>

            <div class="mt-6">
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="block w-full rounded-xl border-border bg-background"
                    placeholder="Masukkan kata sandi"
                />

                <x-input-error
                    :messages="$errors->userDeletion->get('password')"
                    class="mt-2"
                />
            </div>

            <div class="mt-6 flex gap-3">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition hover:bg-muted"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="flex-1 rounded-full bg-destructive py-2.5 text-sm font-semibold text-white transition hover:bg-destructive/90"
                >
                    Ya, Hapus
                </button>
            </div>
        </form>
    </x-modal>
</section>