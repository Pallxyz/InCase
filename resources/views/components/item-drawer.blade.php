{{-- resources/views/components/item-drawer.blade.php --}}

<div
    x-show="drawerOpen"
    x-cloak
    class="relative z-50"
    aria-labelledby="drawer-title"
    role="dialog"
    aria-modal="true"
>
    {{-- Backdrop --}}
    <div
        x-show="drawerOpen"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="closeDrawer()"
        class="fixed inset-0 bg-black/40"
    ></div>

    {{-- Panel --}}
    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div
                    x-show="drawerOpen"
                    x-transition:enter="transform transition ease-in-out duration-300"
                    x-transition:enter-start="translate-x-full"
                    x-transition:enter-end="translate-x-0"
                    x-transition:leave="transform transition ease-in-out duration-200"
                    x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="pointer-events-auto w-screen max-w-md"
                    @click.outside="closeDrawer()"
                >
                    <div class="flex h-full flex-col overflow-y-scroll bg-card shadow-xl">
                        {{-- Header --}}
                        <div class="flex items-start justify-between border-b border-border px-6 py-5">
                            <div>
                                <h2 id="drawer-title" class="text-lg font-semibold text-foreground" x-text="drawerMode === 'edit' ? 'Edit Barang' : 'Tambah Barang'"></h2>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    Lengkapi detail barang untuk dipantau lewat RFID.
                                </p>
                            </div>
                            <button
                                type="button"
                                @click="closeDrawer()"
                                class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                            >
                                <x-icon.x-mark class="h-5 w-5" />
                            </button>
                        </div>

                        {{-- Form --}}
                        <div class="flex-1 space-y-5 px-6 py-6">
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Barang</label>
                                <input
                                    type="text"
                                    x-model="form.name"
                                    placeholder="Contoh: Laptop Asus, Buku Matematika"
                                    class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Kategori</label>
                                <div class="relative">
                                    <select
                                        x-model="form.category"
                                        class="block w-full appearance-none rounded-xl border border-border bg-background px-3.5 py-2.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                    >
                                        <option value="Elektronik">Elektronik</option>
                                        <option value="Buku Pelajaran">Buku Pelajaran</option>
                                        <option value="Alat Tulis">Alat Tulis</option>
                                        <option value="Perlengkapan">Perlengkapan</option>
                                        <option value="Perlengkapan Olahraga">Perlengkapan Olahraga</option>
                                    </select>
                                    <x-icon.chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                </div>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">UID RFID</label>
                                <input
                                    type="text"
                                    x-model="form.rfid"
                                    placeholder="Tempelkan / ketik UID tag RFID (opsional)"
                                    class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 font-mono text-sm text-foreground placeholder:font-sans placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                >
                                <p class="mt-1.5 text-xs text-muted-foreground">
                                    Kosongkan jika barang belum ditempel tag RFID.
                                </p>
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Kompartemen</label>
                                <input
                                    type="text"
                                    x-model="form.compartment"
                                    placeholder="Contoh: Rak A1, Loker 3"
                                    class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                >
                            </div>

                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-foreground">Deskripsi</label>
                                <textarea
                                    x-model="form.description"
                                    rows="3"
                                    placeholder="Catatan tambahan tentang barang ini (opsional)"
                                    class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                                ></textarea>
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="flex items-center justify-end gap-3 border-t border-border px-6 py-4">
                            <button
                                type="button"
                                @click="closeDrawer()"
                                class="rounded-full border border-border px-5 py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                :disabled="saving || !form.name"
                                @click="saveItem()"
                                class="inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90 disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <svg
                                    x-show="saving"
                                    class="h-4 w-4 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span x-text="saving ? 'Menyimpan...' : (drawerMode === 'edit' ? 'Simpan Perubahan' : 'Tambah Barang')"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>