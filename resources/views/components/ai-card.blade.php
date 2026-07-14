@php
    $recommendations = [
        'Buku Matematika belum terdeteksi',
        'Bawa buku catatan Fisika',
        'Bawa payung — kemungkinan hujan',
    ];
@endphp

<div class="relative overflow-hidden rounded-2xl bg-[#0B1220] p-5 text-white shadow-[0_20px_50px_-20px_rgba(0,0,0,0.5)]">
    <div class="pointer-events-none absolute -right-10 -top-10 h-40 w-40 rounded-full bg-primary/30 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-10 -left-10 h-32 w-32 rounded-full bg-secondary/20 blur-3xl"></div>

    <div class="relative flex items-center justify-between">
        <div class="flex items-center gap-2">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10">
                <x-icon.sparkles class="h-4 w-4 text-secondary" />
            </span>
            <span class="text-sm font-semibold">Asisten AI</span>
        </div>
        <span class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-semibold text-secondary">
            Keyakinan 98%
        </span>
    </div>

    <div class="relative mt-4 flex items-center gap-2 text-xs font-medium text-white/70">
        <span>Menganalisis</span>
        <span class="flex items-center gap-1">
            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-secondary [animation-delay:-0.3s]"></span>
            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-secondary [animation-delay:-0.15s]"></span>
            <span class="h-1.5 w-1.5 animate-bounce rounded-full bg-secondary"></span>
        </span>
    </div>

    <div class="relative mt-5">
        <p class="text-xs font-semibold uppercase tracking-wide text-white/50">Rekomendasi</p>
        <ul class="mt-2.5 flex flex-col gap-2">
            @foreach ($recommendations as $item)
                <li class="flex items-start gap-2 text-sm leading-snug text-white/90">
                    <span class="mt-1.5 h-1 w-1 shrink-0 rounded-full bg-secondary"></span>
                    {{ $item }}
                </li>
            @endforeach
        </ul>
    </div>
</div>