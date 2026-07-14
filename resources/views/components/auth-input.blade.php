@props([
    'icon' => null,
    'label' => '',
    'name' => '',
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
])

<div>
    <label for="{{ $name }}" class="mb-1.5 block text-sm font-medium text-foreground">
        {{ $label }}
    </label>
    <div class="relative">
        @if ($icon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                <x-dynamic-component :component="'icon.' . $icon" class="h-5 w-5" />
            </span>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $name }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            value="{{ old($name, $value) }}"
            {{ $attributes->merge([
                'class' => 'block w-full rounded-xl border border-border bg-background py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10 ' . ($icon ? 'pl-11 pr-3.5' : 'px-3.5'),
            ]) }}
        >
    </div>

    @error($name)
        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
    @enderror
</div>
