@php
    $events = [
        ['label' => 'Pemindaian Dimulai', 'time' => '07:14:02', 'tone' => 'primary', 'icon' => 'bolt'],
        ['label' => 'RFID Terdeteksi', 'time' => '07:14:03', 'tone' => 'success', 'icon' => 'check-circle'],
        ['label' => 'Analisis AI', 'time' => '07:14:04', 'tone' => 'accent', 'icon' => 'sparkles'],
        ['label' => 'Barang Belum Lengkap', 'time' => '07:14:05', 'tone' => 'danger', 'icon' => 'x-circle'],
    ];

    $toneStyles = [
        'primary' => 'bg-primary/10 text-primary',
        'success' => 'bg-success/10 text-success',
        'accent' => 'bg-secondary/20 text-foreground',
        'danger' => 'bg-destructive/10 text-destructive',
    ];

    $lastIndex = count($events) - 1;
@endphp

<div class="rounded-2xl border border-border bg-card p-5 shadow-sm">
    <span class="text-sm font-semibold text-foreground">Linimasa Aktivitas</span>

    <ol class="mt-4">
        @foreach ($events as $index => $event)
            <li class="relative flex gap-3 pb-6 last:pb-0">
                @if ($index !== $lastIndex)
                    <span class="absolute left-[15px] top-8 h-[calc(100%-1.5rem)] w-px bg-border" aria-hidden="true"></span>
                @endif

                <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $toneStyles[$event['tone']] }}">
                    <x-dynamic-component :component="'icon.' . $event['icon']" class="h-4 w-4" />
                </span>

                <div class="pt-1">
                    <p class="text-sm font-medium text-foreground">{{ $event['label'] }}</p>
                    <p class="mt-0.5 text-[11px] text-muted-foreground">{{ $event['time'] }}</p>
                </div>
            </li>
        @endforeach
    </ol>
</div>