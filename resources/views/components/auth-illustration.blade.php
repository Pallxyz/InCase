@php
    $nodes = [
        ['icon' => 'device-phone-mobile', 'label' => 'Laptop', 'position' => 'top-4 left-2'],
        ['icon' => 'book-open', 'label' => 'Physics Book', 'position' => 'top-16 -right-4'],
        ['icon' => 'calculator', 'label' => 'Calculator', 'position' => 'bottom-24 -left-8'],
        ['icon' => 'beaker', 'label' => 'Bottle', 'position' => 'bottom-8 right-0'],
        ['icon' => 'identification', 'label' => 'RFID Tag', 'position' => 'top-1/2 -left-12 -translate-y-1/2'],
    ];
@endphp

<div class="relative hidden h-full flex-col justify-between overflow-hidden bg-[#0B1220] p-12 text-white lg:flex">
    {{-- Ambient glow --}}
    <div class="pointer-events-none absolute -left-32 -top-32 h-96 w-96 rounded-full bg-primary/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 -right-24 h-80 w-80 rounded-full bg-secondary/20 blur-3xl"></div>

    {{-- Logo --}}
    <div class="relative flex items-center gap-3">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary">
            <x-icon.cube class="h-5 w-5" />
        </span>
        <span class="text-lg font-bold tracking-tight">InCase</span>
    </div>

    {{-- Illustration --}}
    <div class="relative mx-auto flex h-72 w-72 items-center justify-center">
        <div class="absolute inset-0 rounded-full bg-primary/10"></div>
        <div class="absolute inset-10 rounded-full bg-primary/15"></div>
        <div class="absolute inset-0 animate-pulse rounded-full ring-1 ring-secondary/20"></div>

        <div class="relative flex h-36 w-36 items-center justify-center rounded-[32px] bg-primary/20 backdrop-blur">
            <x-icon.cube class="h-16 w-16 text-secondary" />
            <span class="absolute -right-3 -top-3 flex h-9 w-9 items-center justify-center rounded-full bg-secondary text-[#0B1220] shadow-lg">
                <x-icon.sparkles class="h-4 w-4" />
            </span>
        </div>

        @foreach ($nodes as $node)
            <div class="absolute {{ $node['position'] }} flex items-center gap-1.5 rounded-full border border-white/10 bg-white/5 px-3 py-1.5 backdrop-blur">
                <x-dynamic-component :component="'icon.' . $node['icon']" class="h-3.5 w-3.5 text-secondary" />
                <span class="text-[11px] font-medium text-white/90">{{ $node['label'] }}</span>
            </div>
        @endforeach
    </div>

    {{-- Headline --}}
    <div class="relative">
        <h2 class="text-balance text-2xl font-bold leading-snug tracking-tight">
            Never forget a school item again.
        </h2>
        <p class="mt-3 max-w-sm text-pretty text-sm leading-relaxed text-white/60">
            RFID, IoT, dan AI bekerja sama memastikan tasmu selalu lengkap sebelum berangkat.
        </p>
    </div>
</div>
