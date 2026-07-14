<div
    x-show="drawerOpen"
    x-cloak
    class="fixed inset-0 z-50"
    style="display: none;"
>
    {{-- Backdrop --}}
    <div
        x-show="drawerOpen"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeDrawer()"
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
    ></div>

    {{-- Panel --}}
    <div
        x-show="drawerOpen"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-card shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-border px-6 py-5">
            <h3 class="text-lg font-bold text-foreground" x-text="drawerMode === 'add' ? 'Tambah Barang' : 'Edit Barang'"></h3>
            <button type="button" @click="closeDrawer()" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                <x-icon.x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-none px-6 py-6">
            <form @submit.prevent="closeDrawer()" class="flex flex-col gap-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Barang</label>
                    <input
                        type="text"
                        x-model="form.name"
                        placeholder="Contoh: Buku Fisika"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kategori</label>
                    <select
                        x-model="form.category"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        <option value="Elektronik">Elektronik</option>
                        <option value="Buku Pelajaran">Buku Pelajaran</option>
                        <option value="Alat Tulis">Alat Tulis</option>
                        <option value="Perlengkapan">Perlengkapan</option>
                        <option value="Perlengkapan Olahraga">Perlengkapan Olahraga</option>
                    </select>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">UID RFID</label>
                    <input
                        type="text"
                        x-model="form.rfid"
                        placeholder="Contoh: A4:3F:9C:12"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 font-mono text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kompartemen</label>
                    <input
                        type="text"
                        x-model="form.compartment"
                        placeholder="Contoh: Kantong Depan"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Deskripsi</label>
                    <textarea
                        x-model="form.description"
                        rows="4"
                        placeholder="Catatan tambahan tentang barang ini (opsional)"
                        class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    ></textarea>
                </div>
            </form>
        </div>

        <div class="flex items-center gap-3 border-t border-border px-6 py-5">
            <button
                type="button"
                @click="closeDrawer()"
                class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
            >
                Batal
            </button>
            <button
                type="button"
                @click="closeDrawer()"
                class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                Simpan
            </button>
        </div>
    </div>
</div>
