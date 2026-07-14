@props([
    'percentage' => 87,
    'packed' => 7,
    'total' => 8,
])

@php
    $radius = 42;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
@endphp

<div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
    <div class="flex items-center justify-between">
        <span class="text-sm font-semibold text-foreground">Bag Health</span>
        <span class="rounded-full bg-success/10 px-2.5 py-1 text-[11px] font-semibold text-success">
            Excellent
        </span>
    </div>

    <div class="mt-4 flex items-center justify-center">
        <div class="relative flex h-32 w-32 items-center justify-center">
            <svg class="h-32 w-32 -rotate-90" viewBox="0 0 96 96">
                <circle
                    cx="48" cy="48" r="{{ $radius }}"
                    fill="none" stroke="currentColor" stroke-width="8"
                    class="text-muted"
                />
                <circle
                    cx="48" cy="48" r="{{ $radius }}"
                    fill="none" stroke="currentColor" stroke-width="8"
                    stroke-linecap="round"
                    stroke-dasharray="{{ $circumference }}"
                    stroke-dashoffset="{{ $offset }}"
                    class="text-primary transition-all duration-500"
                />
            </svg>
            <div class="absolute flex flex-col items-center">
                <span class="text-2xl font-bold text-foreground">{{ $percentage }}%</span>
            </div>
        </div>
    </div>

    <p class="mt-3 text-center text-sm font-medium text-foreground">
        {{ $packed }} of {{ $total }} Items Packed
    </p>

    <div class="mt-4 flex items-center justify-between rounded-xl border border-border bg-background px-3 py-2.5">
        <span class="flex items-center gap-2 text-xs font-medium text-muted-foreground">
            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-success/10 text-success">
                <x-icon.wifi class="h-3.5 w-3.5" />
            </span>
            ESP32 Connected
        </span>
        <span class="text-[11px] text-muted-foreground">Sync 5s ago</span>
    </div>
</div>
