@props([
    'icon' => 'cube',
    'name' => '',
    'category' => '',
    'compartment' => '',
    'signal' => 'Kuat',
    'lastScan' => '',
    'packed' => true,
])

@php
    $signalStyles = [
        'Kuat' => 'text-success',
        'Sedang' => 'text-warning',
        'Lemah' => 'text-destructive',
    ];
@endphp

<div class="group flex items-center gap-4 rounded-2xl border border-border bg-card p-4 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
    <span
        @class([
            'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl transition-colors',
            'bg-primary/10 text-primary' => $packed,
            'bg-muted text-muted-foreground' => ! $packed,
        ])
    >
        <x-dynamic-component :component="'icon.' . $icon" class="h-5 w-5" />
    </span>

    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <p class="truncate text-sm font-semibold text-foreground">{{ $name }}</p>
            @if ($packed)
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-success/10 px-2 py-0.5 text-[11px] font-semibold text-success">
                    <x-icon.check-circle class="h-3 w-3" />
                    Lengkap
                </span>
            @else
                <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-destructive/10 px-2 py-0.5 text-[11px] font-semibold text-destructive">
                    <x-icon.x-circle class="h-3 w-3" />
                    Belum Terdeteksi
                </span>
            @endif
        </div>
        <p class="mt-0.5 truncate text-xs text-muted-foreground">
            {{ $category }} · {{ $compartment }}
        </p>
    </div>

    <div class="hidden shrink-0 flex-col items-end gap-1 text-right sm:flex">
        <span class="inline-flex items-center gap-1 text-xs font-medium {{ $signalStyles[$signal] ?? 'text-muted-foreground' }}">
            <x-icon.signal class="h-3.5 w-3.5" />
            {{ $signal }}
        </span>
        <span class="text-[11px] text-muted-foreground">{{ $lastScan }}</span>
    </div>
</div>