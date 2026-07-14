<div
    x-show="deleteModalOpen"
    x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center px-4"
    style="display: none;"
>
    <div
        x-show="deleteModalOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeDeleteModal()"
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
    ></div>

    <div
        x-show="deleteModalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="relative w-full max-w-sm rounded-[24px] bg-card p-6 text-center shadow-2xl"
    >
        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive">
            <x-icon.trash class="h-7 w-7" />
        </span>

        <h3 class="mt-4 text-lg font-bold text-foreground">Hapus barang ini?</h3>
        <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
            Tindakan ini gak bisa dibatalin. Barang bakal hilang permanen dari daftar dan gak lagi terhubung ke RFID.
        </p>

        <div class="mt-6 flex items-center gap-3">
            <button
                type="button"
                @click="closeDeleteModal()"
                class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
            >
                Batal
            </button>
            <button
                type="button"
                @click="confirmDelete()"
                class="flex-1 rounded-full bg-destructive py-2.5 text-sm font-semibold text-white transition-colors hover:bg-destructive/90"
            >
                Ya, Hapus
            </button>
        </div>
    </div>
</div>
