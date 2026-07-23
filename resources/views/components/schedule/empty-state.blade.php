@props([
    'title',
    'description',
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card px-8 py-14 text-center']) }}>
    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
        <x-icon.calendar-days class="h-7 w-7" />
    </span>
    <p class="mt-4 text-sm font-medium text-foreground">{{ $title }}</p>
    <p class="mt-1 text-sm text-muted-foreground">{{ $description }}</p>

    {{ $slot }}
</div>