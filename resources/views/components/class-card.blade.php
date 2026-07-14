@props([
    'subject' => '',
    'teacher' => '',
    'room' => '',
    'start' => '',
    'end' => '',
    'items' => [],
])

<div
    x-data="{ status: getClassStatus('{{ $start }}', '{{ $end }}') }"
    x-init="setInterval(() => status = getClassStatus('{{ $start }}', '{{ $end }}'), 30000)"
    @click="$dispatch('open-class-drawer', {
        subject: '{{ $subject }}',
        teacher: '{{ $teacher }}',
        room: '{{ $room }}',
        start: '{{ $start }}',
        end: '{{ $end }}',
        items: {{ collect($items)->pluck('label')->toJson() }},
    })"
    :class="status === 'now'
        ? 'border-primary/30 bg-gradient-to-br from-primary/10 via-primary/5 to-transparent shadow-[0_0_0_1px_rgba(16,110,190,0.15),0_20px_45px_-20px_rgba(16,110,190,0.4)]'
        : 'border-border bg-card shadow-sm hover:-translate-y-0.5 hover:shadow-md'"
    class="cursor-pointer rounded-[24px] border p-6 transition-all duration-200"
>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-bold text-foreground">{{ $subject }}</h3>
            <p class="mt-1 flex items-center gap-1.5 text-sm text-muted-foreground">
                <x-icon.user class="h-3.5 w-3.5" />
                {{ $teacher }}
            </p>
        </div>

        <span x-show="status === 'now'" x-cloak class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-primary-foreground">
            <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-white"></span>
            Berlangsung
        </span>
        <span x-show="status === 'upcoming'" x-cloak class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-warning/10 px-3 py-1 text-xs font-semibold text-warning">
            Akan Datang
        </span>
        <span x-show="status === 'completed'" x-cloak class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-muted px-3 py-1 text-xs font-semibold text-muted-foreground">
            Selesai
        </span>
    </div>

    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-muted-foreground">
        <span class="inline-flex items-center gap-1.5">
            <x-icon.map-pin class="h-4 w-4" />
            {{ $room }}
        </span>
        <span class="inline-flex items-center gap-1.5">
            <x-icon.clock class="h-4 w-4" />
            {{ $start }} – {{ $end }}
        </span>
    </div>

    @if (count($items))
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($items as $item)
                <x-required-item-badge :icon="$item['icon']" :label="$item['label']" />
            @endforeach
        </div>
    @endif
</div>
