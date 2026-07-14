@php
    $navItems = [
        ['icon' => 'squares-2x2', 'label' => 'Dasbor', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
        ['icon' => 'cube', 'label' => 'Barang', 'href' => route('items.index'), 'active' => request()->routeIs('items.index')],
        ['icon' => 'calendar-days', 'label' => 'Jadwal', 'href' => route('schedule.index'), 'active' => request()->routeIs('schedule.index')],
        ['icon' => 'clock', 'label' => 'Riwayat Pindai', 'href' => route('scan-history.index'), 'active' => request()->routeIs('scan-history.index')],
        ['icon' => 'cog-6-tooth', 'label' => 'Pengaturan', 'href' => route('profile.edit'), 'active' => request()->routeIs('profile.edit')],
    ];
@endphp

<aside class="fixed inset-y-0 left-0 z-40 hidden w-64 shrink-0 flex-col border-r border-border bg-card lg:flex">
    <div class="scrollbar-none flex h-full flex-col justify-between overflow-y-auto p-5">
        <div>
            <div class="flex items-center gap-3 px-2 pb-8">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary text-primary-foreground">
                    <x-icon.viewfinder-circle class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-base font-bold leading-none text-foreground">InCase</p>
                    <p class="mt-1 text-xs font-medium text-muted-foreground">Tas Sekolah Pintar</p>
                </div>
            </div>

            <nav class="flex flex-col gap-1">
                @foreach ($navItems as $item)
                    <a href="{{ $item['href'] }}" @class([
                        'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors',
                        'bg-primary/10 text-primary' => $item['active'],
                        'text-muted-foreground hover:bg-muted hover:text-foreground' => ! $item['active'],
                    ])>
                        <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5" />
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="flex items-center gap-3 rounded-2xl border border-border bg-background p-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                N
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-foreground">Nopal</p>
                <p class="truncate text-[11px] text-muted-foreground">Kelas 9 · SMPN 1</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-destructive" aria-label="Keluar">
                    <x-icon.arrow-left-on-rectangle class="h-4 w-4" />
                </button>
            </form>
        </div>
    </div>
</aside>