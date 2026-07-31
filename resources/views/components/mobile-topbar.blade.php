@props(['title' => 'InCase'])

<div class="sticky top-0 z-20 flex items-center gap-3 border-b border-border bg-background/95 px-4 py-3 backdrop-blur lg:hidden">
    <button
        type="button"
        @click="$store.sidebar.open = true"
        class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card text-foreground"
        aria-label="Buka menu navigasi"
    >
        <x-icon.bars-3 class="h-5 w-5" />
    </button>
    <span class="text-sm font-bold text-foreground">{{ $title }}</span>
</div>