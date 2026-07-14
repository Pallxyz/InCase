@php
    $requiredItems = [
        [
            'icon' => 'device-phone-mobile',
            'name' => 'Laptop',
            'category' => 'Elektronik',
            'compartment' => 'Kompartemen Utama',
            'signal' => 'Kuat',
            'lastScan' => '2 menit lalu',
            'packed' => true,
        ],
        [
            'icon' => 'book-open',
            'name' => 'Buku Fisika',
            'category' => 'Buku Pelajaran',
            'compartment' => 'Kantong Depan',
            'signal' => 'Kuat',
            'lastScan' => '2 menit lalu',
            'packed' => true,
        ],
        [
            'icon' => 'calculator',
            'name' => 'Kalkulator',
            'category' => 'Alat Tulis',
            'compartment' => 'Kantong Samping',
            'signal' => 'Sedang',
            'lastScan' => '3 menit lalu',
            'packed' => true,
        ],
        [
            'icon' => 'beaker',
            'name' => 'Botol Minum',
            'category' => 'Perlengkapan',
            'compartment' => 'Kantong Samping',
            'signal' => 'Kuat',
            'lastScan' => '3 menit lalu',
            'packed' => true,
        ],
        [
            'icon' => 'book-open',
            'name' => 'Buku Matematika',
            'category' => 'Buku Pelajaran',
            'compartment' => 'Kompartemen Utama',
            'signal' => 'Lemah',
            'lastScan' => '2 hari lalu',
            'packed' => false,
        ],
        [
            'icon' => 'tag',
            'name' => 'Sepatu Olahraga',
            'category' => 'Perlengkapan Olahraga',
            'compartment' => 'Kompartemen Bawah',
            'signal' => 'Lemah',
            'lastScan' => '5 hari lalu',
            'packed' => false,
        ],
    ];
@endphp

<x-layouts.dashboard title="Dasbor — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64 lg:mr-80">
            <div class="mx-auto max-w-4xl px-6 py-8 sm:px-8">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                        Selamat Pagi, Nopal
                    </h1>
                    <p class="mt-1.5 text-sm text-muted-foreground">
                        Pastikan tasmu sudah siap hari ini.
                    </p>
                </div>

                <div class="mt-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-foreground">Barang Wajib Hari Ini</h2>
                        <span class="text-sm font-medium text-muted-foreground">
                            {{ collect($requiredItems)->where('packed', true)->count() }}/{{ count($requiredItems) }} lengkap
                        </span>
                    </div>

                    <div class="mt-4 flex flex-col gap-3">
                        @foreach ($requiredItems as $item)
                            <x-item-card
                                :icon="$item['icon']"
                                :name="$item['name']"
                                :category="$item['category']"
                                :compartment="$item['compartment']"
                                :signal="$item['signal']"
                                :last-scan="$item['lastScan']"
                                :packed="$item['packed']"
                            />
                        @endforeach
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