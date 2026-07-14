@props([
    'scanId' => 0,
    'time' => '',
    'date' => '',
    'scanType' => 'Auto Scan',
    'status' => 'success',
    'duration' => '',
    'itemsDetected' => 0,
    'itemsTotal' => 0,
    'detectedItems' => [],
    'missingItems' => [],
    'aiSummary' => '',
    'device' => 'ESP32-01',
    'location' => '',
    'signal' => 'Kuat',
])

@php
    $statusStyles = [
        'success' => ['label' => 'Berhasil', 'class' => 'bg-success/10 text-success', 'dot' => 'bg-success', 'ring' => 'ring-success/15'],
        'warning' => ['label' => 'Peringatan', 'class' => 'bg-warning/10 text-warning', 'dot' => 'bg-warning', 'ring' => 'ring-warning/15'],
        'missing' => ['label' => 'Barang Kurang', 'class' => 'bg-destructive/10 text-destructive', 'dot' => 'bg-destructive', 'ring' => 'ring-destructive/15'],
    ];
    $isManual = $scanType === 'Manual Scan';
    $dotColor = $isManual ? 'bg-blue-500' : $statusStyles[$status]['dot'];
    $ringColor = $isManual ? 'ring-blue-500/15' : $statusStyles[$status]['ring'];
    $progressPct = $itemsTotal > 0 ? round(($itemsDetected / $itemsTotal) * 100) : 0;
@endphp

<div
    data-scan-id="{{ $scanId }}"
    x-show="matches({{ $scanId }})"
    :style="'order:' + order({{ $scanId }})"
    x-data="{ expanded: false }"
    class="relative flex gap-4"
>
    {{-- Timeline dot + line --}}
    <div class="flex flex-col items-center pt-6">
        <span class="h-3 w-3 shrink-0 rounded-full {{ $dotColor }} ring-4 {{ $ringColor }}"></span>
        <span class="mt-1 w-px flex-1 bg-border"></span>
    </div>

    {{-- Card --}}
    <div class="mb-5 flex-1 cursor-pointer rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md" @click="expanded = !expanded">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2">
                    <span class="text-base font-bold text-foreground">{{ $time }}</span>
                    <span class="text-sm text-muted-foreground">· {{ $date }}</span>
                </div>
                <p class="mt-1 text-sm font-medium text-muted-foreground">{{ $scanType }}</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$status]['class'] }}">
                    {{ $statusStyles[$status]['label'] }}
                </span>
                <span class="text-xs text-muted-foreground">{{ $duration }}</span>
                <x-icon.chevron-down x-show="!expanded" class="h-4 w-4 text-muted-foreground" />
                <x-icon.chevron-up x-show="expanded" x-cloak class="h-4 w-4 text-muted-foreground" />
            </div>
        </div>

        <div class="mt-4">
            <div class="flex items-center justify-between text-xs text-muted-foreground">
                <span>Barang Terdeteksi</span>
                <span class="font-medium text-foreground">{{ $itemsDetected }} / {{ $itemsTotal }}</span>
            </div>
            <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-muted">
                <div class="h-full rounded-full bg-primary transition-all duration-500" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>

        {{-- Expandable details --}}
        <div
            x-show="expanded"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            class="mt-5 border-t border-border pt-5"
        >
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Barang Terdeteksi</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @forelse ($detectedItems as $item)
                            <span class="inline-flex items-center gap-1 rounded-full bg-success/10 px-2.5 py-1 text-xs font-medium text-success">
                                <x-icon.check-circle class="h-3 w-3" />
                                {{ $item }}
                            </span>
                        @empty
                            <span class="text-xs text-muted-foreground">Tidak ada</span>
                        @endforelse
                    </div>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">Barang Belum Terdeteksi</p>
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @forelse ($missingItems as $item)
                            <span class="inline-flex items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-1 text-xs font-medium text-destructive">
                                <x-icon.x-circle class="h-3 w-3" />
                                {{ $item }}
                            </span>
                        @empty
                            <span class="text-xs text-muted-foreground">Tidak ada</span>
                        @endforelse
                    </div>
                </div>
            </div>

            @if ($aiSummary)
                <div class="mt-5 rounded-xl bg-muted/50 p-4">
                    <p class="flex items-center gap-1.5 text-xs font-semibold text-primary">
                        <x-icon.sparkles class="h-3.5 w-3.5" />
                        Ringkasan AI
                    </p>
                    <p class="mt-1.5 text-sm leading-relaxed text-foreground">{{ $aiSummary }}</p>
                </div>
            @endif

            <div class="mt-5 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <div>
                    <p class="text-xs text-muted-foreground">Perangkat Scanner</p>
                    <p class="mt-0.5 text-sm font-medium text-foreground">{{ $device }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Lokasi Scanner</p>
                    <p class="mt-0.5 text-sm font-medium text-foreground">{{ $location }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Durasi Pindai</p>
                    <p class="mt-0.5 text-sm font-medium text-foreground">{{ $duration }}</p>
                </div>
                <div>
                    <p class="text-xs text-muted-foreground">Kualitas Sinyal</p>
                    <p class="mt-0.5 text-sm font-medium text-foreground">{{ $signal }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
