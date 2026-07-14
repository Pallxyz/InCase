@props(['item'])

@php
    $statusStyles = [
        'connected' => ['label' => 'Terhubung', 'icon' => 'wifi', 'class' => 'bg-success/10 text-success'],
        'not_scanned' => ['label' => 'Belum Dipindai', 'icon' => 'chevron-down', 'class' => 'bg-warning/10 text-warning'],
        'no_rfid' => ['label' => 'Tanpa RFID', 'icon' => 'x-mark', 'class' => 'bg-destructive/10 text-destructive'],
    ];
    $status = $statusStyles[$item['status']];
@endphp

<div
    x-show="matches({{ $item['id'] }})"
    :style="'order:' + order({{ $item['id'] }})"
    :class="view === 'list' ? 'sm:flex sm:items-center sm:justify-between sm:gap-6' : ''"
    class="group rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
>
    <div :class="view === 'list' ? 'flex items-center gap-4' : ''">
        <div class="flex items-start justify-between">
            <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                <x-dynamic-component :component="'icon.' . $item['icon']" class="h-7 w-7" />
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $status['class'] }}" :class="view === 'list' ? 'sm:hidden' : ''">
                <x-dynamic-component :component="'icon.' . $status['icon']" class="h-3 w-3" />
                {{ $status['label'] }}
            </span>
        </div>

        <div :class="view === 'list' ? '' : 'mt-4'">
            <div class="flex items-center gap-2">
                <p class="text-base font-semibold text-foreground">{{ $item['name'] }}</p>
            </div>
            <span class="mt-1.5 inline-flex items-center rounded-full bg-muted px-2.5 py-0.5 text-[11px] font-medium text-muted-foreground">
                {{ $item['category'] }}
            </span>
        </div>
    </div>

    <div :class="view === 'list' ? 'flex flex-1 items-center justify-between gap-6' : 'mt-5 flex flex-col gap-2'">
        <div :class="view === 'list' ? 'flex items-center gap-6' : 'flex flex-col gap-2'">
            <div class="flex items-center justify-between text-xs">
                <span class="text-muted-foreground">UID RFID</span>
                <span class="font-mono font-medium text-foreground">{{ $item['rfid'] }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-muted-foreground">Kompartemen</span>
                <span class="font-medium text-foreground">{{ $item['compartment'] }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
                <span class="text-muted-foreground">Dibuat</span>
                <span class="font-medium text-foreground">{{ $item['createdLabel'] }}</span>
            </div>
        </div>

        <span class="hidden items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold {{ $status['class'] }}" :class="view === 'list' ? 'sm:inline-flex' : ''">
            <x-dynamic-component :component="'icon.' . $status['icon']" class="h-3 w-3" />
            {{ $status['label'] }}
        </span>
    </div>

    <div class="mt-5 flex items-center gap-2 border-t border-border pt-4" :class="view === 'list' ? 'sm:mt-0 sm:border-t-0 sm:pt-0' : ''">
        <button
            type="button"
            @click="openEditDrawer({{ $item['id'] }})"
            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
        >
            <x-icon.pencil class="h-3.5 w-3.5" />
            Edit
        </button>
        <button
            type="button"
            @click="openDeleteModal({{ $item['id'] }})"
            class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-destructive/20 py-2 text-xs font-semibold text-destructive transition-colors hover:bg-destructive/10"
        >
            <x-icon.trash class="h-3.5 w-3.5" />
            Hapus
        </button>
    </div>
</div>
