@props([
    'icon' => 'archive-box',
    'title' => '',
    'description' => '',
    'buttonLabel' => null,
    'buttonClick' => null,
])

<div class="flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card px-8 py-20 text-center">
    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
        <x-dynamic-component :component="'icon.' . $icon" class="h-8 w-8" />
    </span>
    <h3 class="mt-5 text-lg font-bold text-foreground">{{ $title }}</h3>
    <p class="mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground">
        {{ $description }}
    </p>

    @if ($buttonLabel)
        <button
            type="button"
            @if ($buttonClick) @click="{{ $buttonClick }}" @endif
            class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
        >
            <x-icon.plus class="h-4 w-4" />
            {{ $buttonLabel }}
        </button>
    @endif
</div>
