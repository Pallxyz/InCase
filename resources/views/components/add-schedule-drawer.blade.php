@props(['availableItems' => []])

<div
    x-data="{
        open: false,
        selectedItems: [],
        toggleItem(name) {
            if (this.selectedItems.includes(name)) {
                this.selectedItems = this.selectedItems.filter(i => i !== name);
            } else {
                this.selectedItems.push(name);
            }
        },
    }"
    @open-add-schedule-drawer.window="open = true; selectedItems = []"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50"
    style="display: none;"
>
    <div
        x-show="open"
        x-transition:enter="transition-opacity ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
    ></div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-250"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="absolute inset-y-0 right-0 flex w-full max-w-md flex-col bg-card shadow-2xl"
    >
        <div class="flex items-center justify-between border-b border-border px-6 py-5">
            <h3 class="text-lg font-bold text-foreground">Tambah Jadwal</h3>
            <button type="button" @click="open = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                <x-icon.x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-none px-6 py-6">
            <form @submit.prevent="open = false" class="flex flex-col gap-5">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Pelajaran</label>
                    <input
                        type="text"
                        placeholder="Contoh: Matematika"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Guru Pengajar</label>
                    <input
                        type="text"
                        placeholder="Contoh: Bu Sari"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Ruangan</label>
                    <input
                        type="text"
                        placeholder="Contoh: Ruang 3A"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Hari</label>
                    <select class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10">
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Mulai</label>
                        <input
                            type="time"
                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Jam Selesai</label>
                        <input
                            type="time"
                            class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Barang Wajib</label>
                    <p class="mb-2 text-xs text-muted-foreground">Pilih dari daftar barang yang sudah terdaftar di halaman Barang.</p>

                    {{-- Selected chips preview --}}
                    <div class="mb-2 flex flex-wrap gap-1.5" x-show="selectedItems.length > 0">
                        <template x-for="name in selectedItems" :key="name">
                            <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-2.5 py-1 text-xs font-medium text-primary" x-text="name"></span>
                        </template>
                    </div>

                    <div class="flex flex-col divide-y divide-border rounded-xl border border-border">
                        @forelse ($availableItems as $itemName)
                            <label class="flex cursor-pointer items-center gap-3 px-3.5 py-2.5 transition-colors hover:bg-muted">
                                <input
                                    type="checkbox"
                                    @change="toggleItem('{{ $itemName }}')"
                                    class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary/30"
                                >
                                <span class="text-sm text-foreground">{{ $itemName }}</span>
                            </label>
                        @empty
                            <p class="px-3.5 py-4 text-center text-sm text-muted-foreground">
                                Belum ada barang terdaftar. Tambahkan barang dulu di halaman Barang.
                            </p>
                        @endforelse
                    </div>
                </div>
            </form>
        </div>

        <div class="flex items-center gap-3 border-t border-border px-6 py-5">
            <button
                type="button"
                @click="open = false"
                class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
            >
                Batal
            </button>
            <button
                type="button"
                @click="open = false"
                class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                Simpan
            </button>
        </div>
    </div>
</div>
