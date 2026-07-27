@props([
    'subject',
    'isTeacher' => false,
    'variant' => 'weekly', // 'today' | 'weekly' — controls type scale only, markup stays identical
])

@php
    $isToday = $variant === 'today';
    $maxVisibleItems = 4;
    $visibleItems = $subject->items->take($maxVisibleItems);
    $remainingItemsCount = max(0, $subject->items->count() - $maxVisibleItems);
@endphp

<div {{ $attributes->merge(['class' => 'group relative rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md']) }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="{{ $isToday ? 'text-lg' : 'text-base' }} font-bold text-foreground">
                {{ $subject->name }}
            </h3>
            <p class="mt-1 flex items-center gap-1.5 {{ $isToday ? 'text-sm font-medium' : 'text-xs' }} text-muted-foreground">
                <x-icon.clock class="{{ $isToday ? 'h-4 w-4' : 'h-3.5 w-3.5' }} shrink-0" />
                {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
            </p>
        </div>

        @if ($subject->has_exam)
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-1 text-[11px] font-semibold text-destructive">
                <x-icon.exclamation-triangle class="h-3 w-3" />
                Ujian
            </span>
        @endif
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-3 {{ $isToday ? 'text-sm' : 'text-xs' }} text-muted-foreground">
        <span class="inline-flex items-center gap-1.5">
            <x-icon.user class="{{ $isToday ? 'h-4 w-4' : 'h-3.5 w-3.5' }} shrink-0" />
            {{ $subject->teacher->name ?? '—' }}
        </span>
        @if ($subject->location)
            <span class="inline-flex items-center gap-1.5">
                <x-icon.map-pin class="{{ $isToday ? 'h-4 w-4' : 'h-3.5 w-3.5' }} shrink-0" />
                Ruang {{ $subject->location }}
            </span>
        @endif
    </div>

    @if (! empty($subject->homework))
        <div class="mt-3 flex items-start gap-2 rounded-xl bg-warning/10 px-3 py-2.5 text-xs text-warning">
            <x-icon.document-text class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span><span class="font-semibold">PR:</span> {{ $subject->homework }}</span>
        </div>
    @endif

    @if ($subject->items->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($visibleItems as $item)
                <span class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium text-foreground">
                    {{ $item->name }}
                </span>
            @endforeach

            @if ($remainingItemsCount > 0)
                <span
                    class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium text-muted-foreground"
                    title="{{ $subject->items->skip($maxVisibleItems)->pluck('name')->implode(', ') }}"
                >
                    +{{ $remainingItemsCount }} lainnya
                </span>
            @endif
        </div>
    @endif

    @if ($isTeacher)
        <div class="mt-4 flex items-center gap-2 border-t border-border pt-4">
            <button
                type="button"
                data-id="{{ $subject->id }}"
                onclick="openEditModal(this)"
                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
            >
                <x-icon.pencil class="h-3.5 w-3.5" />
                Edit
            </button>
            <button
                type="button"
                data-id="{{ $subject->id }}"
                data-name="{{ $subject->name }}"
                onclick="openDeleteModal(this)"
                class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-destructive/20 py-2 text-xs font-semibold text-destructive transition-colors hover:bg-destructive/10"
            >
                <x-icon.trash class="h-3.5 w-3.5" />
                Hapus
            </button>
        </div>
    @endif
</div>