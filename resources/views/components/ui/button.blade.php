@props([
    'variant' => 'default',
    'size' => 'default',
    'href' => null,
    'type' => 'button',
])

@php
    $variants = [
        'default' => 'bg-primary text-primary-foreground hover:bg-primary/80',
        'outline' => 'border-border bg-background hover:bg-muted hover:text-foreground',
        'secondary' => 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'ghost' => 'hover:bg-muted hover:text-foreground',
    ];

    $sizes = [
        'default' => 'h-8 gap-1.5 px-2.5',
        'lg' => 'h-11 gap-1.5 px-2.5',
    ];

    $classes = 'group/button inline-flex shrink-0 items-center justify-center rounded-lg border border-transparent text-sm font-medium whitespace-nowrap transition-all outline-none select-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 active:translate-y-px disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*=size-])]:size-4 '
        . ($variants[$variant] ?? $variants['default']) . ' '
        . ($sizes[$size] ?? $sizes['default']);
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
