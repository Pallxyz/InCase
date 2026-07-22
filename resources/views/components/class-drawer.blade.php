<div
    x-data="{
        open: false,
        subject: '',
        teacher: '',
        location: '',
        start: '',
        end: '',
        items: [],
    }"
    @open-class-drawer.window="
        open = true;
        subject = $event.detail.subject;
        teacher = $event.detail.teacher;
        location = $event.detail.location;
        start = $event.detail.start;
        end = $event.detail.end;
        items = $event.detail.items;
    "
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
            <h3 class="text-lg font-bold text-foreground">Detail Pelajaran</h3>
            <button type="button" @click="open = false" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                <x-icon.x-mark class="h-5 w-5" />
            </button>
        </div>

        <div class="flex-1 overflow-y-auto scrollbar-none px-6 py-6">
            <h2 class="text-xl font-bold text-foreground" x-text="subject"></h2>

            <div class="mt-5 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-icon.user class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="text-xs text-muted-foreground">Guru Pengajar</p>
                        <p class="text-sm font-medium text-foreground" x-text="teacher"></p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-icon.map-pin class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="text-xs text-muted-foreground">Ruangan</p>
                        <p class="text-sm font-medium text-foreground" x-text="location"></p>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <x-icon.clock class="h-4 w-4" />
                    </span>
                    <div>
                        <p class="text-xs text-muted-foreground">Waktu</p>
                        <p class="text-sm font-medium text-foreground">
                            <span x-text="start"></span> – <span x-text="end"></span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Barang Wajib</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <template x-for="item in items" :key="item">
                        <span class="inline-flex items-center rounded-full bg-muted px-3 py-1.5 text-xs font-medium text-foreground" x-text="item"></span>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 border-t border-border px-6 py-5">
            <button type="button" @click="open = false" class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-destructive transition-colors hover:bg-destructive/10">
                Hapus
            </button>
            <button type="button" @click="open = false" class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                Edit
            </button>
        </div>
    </div>
</div>
