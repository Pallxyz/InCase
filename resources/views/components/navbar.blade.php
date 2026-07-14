@php
    $navLinks = [
        ['label' => 'Fitur', 'href' => '#fitur'],
        ['label' => 'Cara Kerja', 'href' => '#cara-kerja'],
        ['label' => 'Dashboard', 'href' => '#dashboard'],
        ['label' => 'Teknologi', 'href' => '#teknologi'],
        ['label' => 'FAQ', 'href' => '#faq'],
    ];
@endphp

<header x-data="{ open: false }" class="sticky top-0 z-50 border-b border-border/70 bg-background/80 backdrop-blur-xl">
    <nav class="mx-auto flex h-16 max-w-[1280px] items-center justify-between px-6">
        <a href="#" class="flex items-center gap-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                <x-icon.scan-line class="h-5 w-5" :stroke-width="2" />
            </span>
            <span class="text-lg font-bold tracking-tight text-foreground">InCase</span>
        </a>

        <div class="hidden items-center gap-8 md:flex">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    class="text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
        </div>

        <div class="hidden items-center gap-3 md:flex">
            <x-ui.button variant="ghost" class="rounded-full font-medium">
                Masuk
            </x-ui.button>
            <x-ui.button class="rounded-full font-medium">Mulai Sekarang</x-ui.button>
        </div>

        <button
            type="button"
            @click="open = !open"
            class="flex h-10 w-10 items-center justify-center rounded-lg text-foreground md:hidden"
            :aria-label="open ? 'Tutup menu' : 'Buka menu'"
        >
            <x-icon.x x-show="open" class="h-5 w-5" />
            <x-icon.menu x-show="!open" class="h-5 w-5" />
        </button>
    </nav>

    <div x-show="open" x-cloak class="border-t border-border bg-background px-6 py-4 md:hidden">
        <div class="flex flex-col gap-1">
            @foreach ($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    @click="open = false"
                    class="rounded-lg px-3 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                >
                    {{ $link['label'] }}
                </a>
            @endforeach
            <div class="mt-3 flex flex-col gap-2">
                <x-ui.button variant="ghost" class="w-full rounded-full font-medium">
                    Masuk
                </x-ui.button>
                <x-ui.button class="w-full rounded-full font-medium">Mulai Sekarang</x-ui.button>
            </div>
        </div>
    </div>
</header>
