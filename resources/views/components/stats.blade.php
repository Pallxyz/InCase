@php
    $stats = [
        ['value' => '95%', 'label' => 'Barang Berhasil Dideteksi'],
        ['value' => '500+', 'label' => 'Pemindaian'],
        ['value' => '<1 Detik', 'label' => 'Waktu Scan'],
        ['value' => '24/7', 'label' => 'Smart Reminder'],
    ];
@endphp

<section class="mx-auto max-w-[1280px] px-6 pb-8">
    <div class="grid grid-cols-2 gap-4 rounded-[24px] border border-border bg-card p-6 shadow-sm sm:p-8 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="flex flex-col items-center px-4 py-3 text-center">
                <span class="text-4xl font-bold tracking-tight text-primary lg:text-5xl">
                    {{ $stat['value'] }}
                </span>
                <span class="mt-2 text-sm font-medium leading-relaxed text-muted-foreground">
                    {{ $stat['label'] }}
                </span>
            </div>
        @endforeach
    </div>
</section>
