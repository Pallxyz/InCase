@php
    $detectedItems = [
        ['icon' => 'book-open', 'label' => 'Buku Matematika'],
        ['icon' => 'ruler', 'label' => 'Alat Tulis'],
        ['icon' => 'laptop', 'label' => 'Laptop'],
        ['icon' => 'backpack', 'label' => 'Botol Minum'],
    ];
@endphp

<section class="relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute left-1/2 top-[-10%] h-[420px] w-[820px] -translate-x-1/2 rounded-full bg-primary/10 blur-3xl"></div>
        <div class="absolute right-[8%] top-[30%] h-64 w-64 rounded-full bg-secondary/25 blur-3xl"></div>
    </div>

    <div class="mx-auto grid max-w-[1280px] items-center gap-16 px-6 py-20 lg:grid-cols-2 lg:py-28">
        <div class="flex flex-col items-start">
            <span class="inline-flex items-center gap-2 rounded-full border border-border bg-card px-4 py-1.5 text-sm font-medium text-muted-foreground shadow-sm">
                <span class="flex h-2 w-2 rounded-full bg-success"></span>
                Smart School Bag Management System
            </span>

            <h1 class="mt-6 text-balance text-4xl font-bold leading-[1.1] tracking-tight text-foreground sm:text-5xl lg:text-6xl">
                Pastikan Semua Barangmu Siap Sebelum Berangkat.
            </h1>

            <p class="mt-6 max-w-xl text-pretty text-lg leading-relaxed text-muted-foreground">
                InCase membantu siswa memastikan seluruh perlengkapan sekolah telah siap menggunakan
                RFID, IoT, dan Smart Reminder.
            </p>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <x-ui.button size="lg" class="rounded-full px-7 text-base font-semibold">
                    Mulai Sekarang
                    <x-icon.arrow-right class="h-4 w-4" />
                </x-ui.button>
                <x-ui.button size="lg" variant="outline" class="rounded-full border-border bg-card px-7 text-base font-semibold">
                    <x-icon.play class="h-4 w-4" />
                    Lihat Cara Kerja
                </x-ui.button>
            </div>

            <div class="mt-10 flex items-center gap-6 text-sm text-muted-foreground">
                <div class="flex items-center gap-2">
                    <x-icon.check-circle-2 class="h-4 w-4 text-success" />
                    Tanpa aplikasi rumit
                </div>
                <div class="flex items-center gap-2">
                    <x-icon.check-circle-2 class="h-4 w-4 text-success" />
                    Scan &lt; 1 detik
                </div>
            </div>
        </div>

        {{-- Hero illustration: Scanner Box + Backpack card --}}
        <div class="relative">
            <div class="relative rounded-[24px] border border-border bg-card p-6 shadow-[0_24px_60px_-20px_rgba(16,110,190,0.25)]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 text-sm font-semibold text-foreground">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                            <x-icon.scan-line class="h-4 w-4" />
                        </span>
                        Scanner Box
                    </div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-3 py-1 text-xs font-semibold text-success">
                        <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-success"></span>
                        Memindai
                    </span>
                </div>

                {{-- Scanner surface --}}
                <div class="relative mt-5 overflow-hidden rounded-[20px] bg-muted p-8">
                    <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <div class="animate-scan-glow h-48 w-48 rounded-full bg-primary/25 blur-2xl"></div>
                    </div>

                    <div class="pointer-events-none absolute inset-x-6 inset-y-6 overflow-hidden rounded-2xl">
                        <div class="animate-scan-line absolute inset-x-0 top-0 h-16 bg-gradient-to-b from-primary/40 via-primary/10 to-transparent"></div>
                        <div class="animate-scan-line absolute inset-x-0 top-0 h-px bg-primary/70 [animation-delay:0.2s]"></div>
                    </div>

                    <div class="relative flex items-center justify-center">
                        <div class="relative flex h-40 w-40 items-center justify-center rounded-[20px] bg-primary/10">
                            <span class="animate-scan-ping absolute h-24 w-24 rounded-full border border-primary/40"></span>
                            <x-icon.backpack class="relative h-20 w-20 text-primary" :stroke-width="1.5" />
                            <span class="absolute -right-2 -top-2 flex h-9 w-9 items-center justify-center rounded-full border-4 border-card bg-secondary text-secondary-foreground shadow-sm">
                                <x-icon.nfc class="h-4 w-4" />
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Detected items --}}
                <div class="mt-5 grid grid-cols-2 gap-3">
                    @foreach ($detectedItems as $item)
                        <div class="flex items-center gap-2.5 rounded-xl border border-border bg-background px-3 py-2.5">
                            <x-dynamic-component :component="'icon.' . $item['icon']" class="h-4 w-4 shrink-0 text-primary" />
                            <span class="truncate text-xs font-medium text-foreground">{{ $item['label'] }}</span>
                            <x-icon.check-circle-2 class="ml-auto h-4 w-4 shrink-0 text-success" />
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Floating RFID sticker chip --}}
            <div class="absolute -left-5 -top-6 hidden rotate-[-8deg] items-center gap-2 rounded-2xl border border-border bg-card px-4 py-3 shadow-lg sm:flex">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-secondary/30 text-primary">
                    <x-icon.nfc class="h-5 w-5" />
                </span>
                <div>
                    <p class="text-xs font-semibold text-foreground">RFID Sticker</p>
                    <p class="text-[11px] text-muted-foreground">Terpasang</p>
                </div>
            </div>

            {{-- Floating phone notification --}}
            <div class="absolute -bottom-6 -right-4 flex w-60 items-start gap-3 rounded-2xl border border-border bg-card p-4 shadow-xl">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary text-primary-foreground">
                    <x-icon.bell class="h-4 w-4" />
                </span>
                <div>
                    <p class="text-sm font-semibold text-foreground">Tas kamu siap!</p>
                    <p class="mt-0.5 text-xs leading-relaxed text-muted-foreground">
                        Semua 4 barang wajib hari ini sudah terdeteksi.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
