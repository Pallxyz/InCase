@props(['icon' => 'tag', 'label' => ''])

<span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-3 py-1.5 text-xs font-medium text-foreground">
    <x-dynamic-component :component="'icon.' . $icon" class="h-3.5 w-3.5 text-primary" />
    {{ $label }}
</span>
