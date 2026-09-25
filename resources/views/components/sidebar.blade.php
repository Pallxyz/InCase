@php
    $user = auth()->user();

    $navItems = [
        [
            'icon' => 'squares-2x2',
            'label' => 'Dasbor',
            'href' => route('dashboard'),
            'active' => request()->routeIs('dashboard'),
        ],
    ];

    if ($user->role === 'student') {
        $navItems[] = [
            'icon' => 'cube',
            'label' => 'Barang',
            'href' => route('items.index'),
            'active' => request()->routeIs('items.*'),
        ];

        $navItems[] = [
            'icon' => 'calendar-days',
            'label' => 'Jadwal',
            'href' => route('schedule.index'),
            'active' => request()->routeIs('schedule.*'),
            'badge' => $user->unreadNotifications()->count(),
        ];

        $navItems[] = [
            'icon' => 'clock',
            'label' => 'Riwayat Pindai',
            'href' => route('scan-history.index'),
            'active' => request()->routeIs('scan-history.*'),
        ];
    } elseif ($user->role === 'teacher') {
        $navItems[] = [
            'icon' => 'calendar-days',
            'label' => 'Jadwal',
            'href' => route('subjects.index'),
            'active' => request()->routeIs('subjects.*'),
        ];
    } elseif ($user->role === 'admin') {
        $navItems[] = [
            'icon' => 'calendar-days',
            'label' => 'Jadwal',
            'href' => route('subjects.index'),
            'active' => request()->routeIs('subjects.*'),
        ];

        $navItems[] = [
            'icon' => 'academic-cap',
            'label' => 'Tahun Ajaran',
            'href' => route('academic-years.index'),
            'active' => request()->routeIs('academic-years.*'),
        ];

        $navItems[] = [
            'icon' => 'calendar',
            'label' => 'Hari Libur',
            'href' => route('holidays.index'),
            'active' => request()->routeIs('holidays.*'),
        ];

        $navItems[] = [
            'icon' => 'identification',
            'label' => 'Akun Guru',
            'href' => route('teachers.index'),
            'active' => request()->routeIs('teachers.*'),
        ];
    }
@endphp

<aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col border-r border-border bg-card lg:flex">
    {{-- ============ BRAND ============ --}}
    <div class="flex h-16 shrink-0 items-center gap-2.5 border-b border-border px-6">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary text-primary-foreground">
            <x-icon.cube class="h-5 w-5" />
        </span>
        <span class="text-lg font-bold tracking-tight text-foreground">InCase</span>
    </div>

    {{-- ============ NAV ITEMS ============ --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-4 py-6">
        @foreach ($navItems as $item)
            <a href="{{ $item['href'] }}"
                class="group flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-sm font-medium transition-colors {{ $item['active'] ? 'bg-primary/10 text-primary' : 'text-muted-foreground hover:bg-muted hover:text-foreground' }}">
                <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5 shrink-0" />
                <span class="flex-1 truncate">{{ $item['label'] }}</span>
                @if (!empty($item['badge']))
                    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-destructive px-1.5 text-[11px] font-semibold text-white">
                        {{ $item['badge'] }}
                    </span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- ============ USER + LOGOUT ============ --}}
    <div class="border-t border-border p-4">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-xl px-2 py-2 transition-colors hover:bg-muted">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary text-sm font-semibold text-primary-foreground">
                @if ($user->avatar)
                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="h-9 w-9 rounded-full object-cover">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-foreground">{{ $user->name }}</p>
                <p class="truncate text-xs capitalize text-muted-foreground">{{ $user->role }}</p>
            </div>
        </a>

        <form method="POST" action="{{ route('logout') }}" class="mt-2">
            @csrf
            <button type="submit"
                class="flex w-full items-center justify-center rounded-xl px-3.5 py-2.5 text-sm font-medium text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive">
                Keluar
            </button>
        </form>
    </div>
</aside>