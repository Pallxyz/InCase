@php
    $flow = [
        ['icon' => 'nfc', 'label' => 'RFID Sticker'],
        ['icon' => 'package', 'label' => 'Barang'],
        ['icon' => 'backpack', 'label' => 'Tas'],
        ['icon' => 'scan-line', 'label' => 'Scanner Box'],
        ['icon' => 'server', 'label' => 'Laravel Website'],
        ['icon' => 'cpu', 'label' => 'AI Processing'],
        ['icon' => 'bell', 'label' => 'Notification'],
    ];
    $lastIndex = count($flow) - 1;
@endphp

<section id="teknologi" class="scroll-mt-20 bg-card">
    <div class="mx-auto max-w-[1280px] px-6 py-24">
        <div class="mx-auto max-w-2xl text-center">
            <span class="text-sm font-semibold uppercase tracking-wide text-primary">
                Teknologi
            </span>
            <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                Bagaimana data mengalir di InCase
            </h2>
            <p class="mt-4 text-pretty text-lg leading-relaxed text-muted-foreground">
                Dari RFID sticker hingga notifikasi, setiap tahap terhubung dalam satu alur yang mulus.
            </p>
        </div>

        <div class="mt-14 flex flex-wrap items-stretch justify-center gap-3">
            @foreach ($flow as $index => $node)
                <div class="flex items-center gap-3">
                    <div class="flex w-32 flex-col items-center gap-3 rounded-[20px] border border-border bg-background p-5 text-center shadow-sm">
                        <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <x-dynamic-component :component="'icon.' . $node['icon']" class="h-6 w-6" :stroke-width="1.75" />
                        </span>
                        <span class="text-sm font-semibold leading-snug text-foreground">
                            {{ $node['label'] }}
                        </span>
                    </div>
                    @if ($index < $lastIndex)
                        <x-icon.arrow-right class="hidden h-5 w-5 shrink-0 text-muted-foreground lg:block" />
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>
