@props(['itemId'])

<div x-data="{ open: false }" @click.outside="open = false" class="relative inline-block text-left">
    <button
        type="button"
        @click="open = !open"
        class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
        aria-label="Buka menu aksi"
    >
        <x-icon.ellipsis-vertical class="h-4 w-4" />
    </button>

    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        class="absolute right-0 z-20 mt-2 w-40 origin-top-right overflow-hidden rounded-xl border border-border bg-card shadow-lg"
    >
        <button
            type="button"
            @click="open = false"
            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-foreground transition-colors hover:bg-muted"
        >
            <x-icon.eye class="h-4 w-4 text-muted-foreground" />
            Lihat
        </button>
        <button
            type="button"
            @click="open = false; openEditDrawer({{ $itemId }})"
            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-foreground transition-colors hover:bg-muted"
        >
            <x-icon.pencil class="h-4 w-4 text-muted-foreground" />
            Edit
        </button>
        <button
            type="button"
            @click="open = false; openDeleteModal({{ $itemId }})"
            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-destructive transition-colors hover:bg-destructive/10"
        >
            <x-icon.trash class="h-4 w-4" />
            Hapus
        </button>
    </div>
</div>
