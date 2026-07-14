@props([
    'icon' => 'chart-bar',
    'label' => '',
    'value' => '',
    'meta' => null,
    'tone' => 'primary',
])

@php
    $toneStyles = [
        'primary' => 'bg-primary/10 text-primary',
        'success' => 'bg-success/10 text-success',
        'warning' => 'bg-warning/10 text-warning',
        'accent' => 'bg-secondary/20 text-foreground',
    ];
@endphp

<div class="rounded-2xl border border-border bg-card p-5 shadow-sm transition-shadow hover:shadow-md">
    <div class="flex items-center justify-between">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $toneStyles[$tone] ?? $toneStyles['primary'] }}">
            <x-dynamic-component :component="'icon.' . $icon" class="h-5 w-5" />
        </span>
    </div>

    <p class="mt-4 text-sm font-medium text-muted-foreground">{{ $label }}</p>
    <p class="mt-1 text-2xl font-bold tracking-tight text-foreground">{{ $value }}</p>

    @if ($meta)
        <p class="mt-1 text-xs text-muted-foreground">{{ $meta }}</p>
    @endif
</div>
