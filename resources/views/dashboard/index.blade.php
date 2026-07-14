@php
    // $requiredItems dikirim dari DashboardController@index — sudah berisi
    // semua barang milik user yang login, diambil dari database (bukan array statis lagi).

    $userName = auth()->user()->name ?? 'Pengguna';

    $serverHour = now()->hour;
    $initialGreeting = match (true) {
        $serverHour >= 4 && $serverHour < 11 => 'Pagi',
        $serverHour >= 11 && $serverHour < 15 => 'Siang',
        $serverHour >= 15 && $serverHour < 18 => 'Sore',
        default => 'Malam',
    };
@endphp

<x-layouts.dashboard title="Dasbor — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64 lg:mr-80">
            <div class="mx-auto max-w-4xl px-6 py-8 sm:px-8">
                <div
                    x-data="{
                        greeting: '{{ $initialGreeting }}',
                        updateGreeting() {
                            const hour = new Date().getHours();
                            if (hour >= 4 && hour < 11) this.greeting = 'Pagi';
                            else if (hour >= 11 && hour < 15) this.greeting = 'Siang';
                            else if (hour >= 15 && hour < 18) this.greeting = 'Sore';
                            else this.greeting = 'Malam';
                        }
                    }"
                    x-init="updateGreeting(); setInterval(() => updateGreeting(), 60000)"
                >
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Selamat <span x-text="greeting">{{ $initialGreeting }}</span>, {{ $userName }}
                    </h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        Pastikan tasmu sudah siap hari ini.
                    </p>
                </div>

                {{-- Barang Wajib Hari Ini — bisa dicoret seperti checklist notes --}}
                <div class="mt-6" x-data="{
                    total: {{ $requiredItems->count() }},
                    struck: {},
                    get remaining() {
                        return this.total - Object.values(this.struck).filter(Boolean).length;
                    }
                }">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Barang Wajib Hari Ini</h2>
                        <span class="text-sm font-medium text-muted-foreground">
                            <span x-text="remaining"></span>/{{ $requiredItems->count() }} tersisa
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Centang barang yang sudah tidak kamu perlukan hari ini — barang yang dicoret tidak akan lagi memicu notifikasi.
                    </p>

                    <div class="mt-4 flex flex-col gap-3">
                        @forelse ($requiredItems as $index => $item)
                            <div class="group relative flex items-center gap-3">
                                {{-- Checkbox coret --}}
                                <label class="flex h-6 w-6 shrink-0 cursor-pointer items-center justify-center">
                                    <input
                                        type="checkbox"
                                        x-model="struck[{{ $index }}]"
                                        class="peer sr-only"
                                    />
                                    <span
                                        class="flex h-5 w-5 items-center justify-center rounded-full border-2 border-border bg-background transition-colors peer-checked:border-primary peer-checked:bg-primary"
                                        :class="struck[{{ $index }}] ? 'border-primary bg-primary' : ''"
                                    >
                                        <svg
                                            x-show="struck[{{ $index }}]"
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            class="h-3.5 w-3.5 text-primary-foreground"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M16.704 5.29a1 1 0 010 1.415l-7.5 7.5a1 1 0 01-1.415 0l-3.5-3.5a1 1 0 111.415-1.414l2.792 2.792 6.793-6.793a1 1 0 011.415 0z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </span>
                                </label>

                                {{-- Card barang --}}
                                <div class="min-w-0 flex-1 transition-opacity" :class="struck[{{ $index }}] ? 'opacity-40' : ''">
                                    <x-item-card
                                        :icon="$item['icon']"
                                        :name="$item['name']"
                                        :category="$item['category']"
                                        :compartment="$item['compartment']"
                                        :signal="$item['signal']"
                                        :last-scan="$item['lastScan']"
                                        :packed="$item['packed']"
                                    />
                                </div>

                                {{-- Garis coret melintasi kartu, muncul saat dicentang --}}
                                <div
                                    x-show="struck[{{ $index }}]"
                                    x-transition
                                    class="pointer-events-none absolute inset-y-0 left-9 right-0 flex items-center"
                                >
                                    <div class="h-px w-full bg-foreground/50"></div>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed border-border p-8 text-center">
                                <p class="text-sm font-medium text-foreground">Belum ada barang yang ditambahkan</p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Tambahkan barang lewat halaman <a href="{{ route('items.index') }}" class="font-medium text-primary underline">Barang</a> supaya muncul di sini.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="mt-8">
                    <x-hero-card />
                </div>
            </div>
        </main>

        <aside class="fixed inset-y-0 right-0 z-30 hidden w-80 shrink-0 overflow-y-auto scrollbar-none border-l border-border bg-background lg:block">
            <div class="flex flex-col gap-5 p-5">
                <x-ai-card />
                <x-timeline />
            </div>
        </aside>
    </div>
</x-layouts.dashboard>