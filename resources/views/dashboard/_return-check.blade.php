{{--
    Bagian dashboard siswa: banner libur + cek pulang (dikumpulkan / hilang).
    Dipanggil dari dashboard/index.blade.php.
    Variabel dari DashboardController: $holiday, $returnCheckOpen, $notReturned, $resolutions
--}}

{{-- ============ BANNER HARI LIBUR ============ --}}
@if (!empty($holiday))
    <div class="mt-6 flex items-center gap-4 rounded-[24px] border border-primary/20 bg-primary/5 p-6">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
            <x-icon.calendar class="h-5 w-5" />
        </span>
        <div>
            <h2 class="text-sm font-semibold text-foreground">Hari ini libur: {{ $holiday->name }}</h2>
            <p class="text-xs text-muted-foreground">Tidak ada jadwal dan tidak ada barang yang perlu dibawa hari ini.</p>
        </div>
    </div>
@endif

{{-- ============ CEK PULANG ============ --}}
@if (!empty($returnCheckOpen))
    @php
        $pendingReturn = $notReturned->reject(fn($i) => $resolutions->has($i->id));
        $resolvedReturn = $notReturned->filter(fn($i) => $resolutions->has($i->id));
    @endphp

    @if (session('success'))
        <div class="mt-6 rounded-2xl border border-success/20 bg-success/10 px-4 py-3 text-sm font-medium text-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mt-6 rounded-[24px] border {{ $pendingReturn->isNotEmpty() ? 'border-warning/30 bg-warning/5' : 'border-success/20 bg-success/5' }} p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2 {{ $pendingReturn->isNotEmpty() ? 'text-warning' : 'text-success' }}">
                @if ($pendingReturn->isNotEmpty())
                    <x-icon.exclamation-triangle class="h-4 w-4" />
                @else
                    <x-icon.check-circle class="h-4 w-4" />
                @endif
                <h2 class="text-sm font-semibold text-foreground">Cek Pulang</h2>
            </div>
            @if ($pendingReturn->isNotEmpty())
                <span class="rounded-full bg-warning/10 px-3 py-1 text-xs font-semibold text-warning">
                    {{ $pendingReturn->count() }} barang belum kembali
                </span>
            @endif
        </div>

        @if ($notReturned->isEmpty())
            <div class="mt-4 flex items-center gap-2 rounded-xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
                <x-icon.check-circle class="h-4 w-4" />
                Semua barang sudah kembali ke tas kamu. Aman untuk pulang.
            </div>
        @else
            <p class="mt-2 text-xs text-muted-foreground">
                Scan barang yang belum kembali, atau jelaskan kenapa barangnya tidak ada.
            </p>
        @endif

        <div class="mt-4 flex flex-col gap-3">
            {{-- Belum dijelaskan --}}
            @foreach ($pendingReturn as $item)
                <div class="rounded-xl bg-card px-4 py-3 shadow-sm">
                    <p class="text-sm font-semibold text-foreground">Kemana {{ $item->name }} kamu?</p>

                    <form method="POST" action="{{ route('items.resolve', $item) }}" class="mt-3 flex flex-wrap gap-2"
                        onsubmit="return this.querySelector('[name=status]').value === 'lost' ? confirm('Yakin {{ e($item->name) }} hilang?') : confirm('Kamu yakin {{ e($item->name) }} dikumpulkan atau terbawa teman? Kalau belum yakin, tanya teman dulu.')">
                        @csrf
                        <input type="hidden" name="confirmed" value="1">

                        {{-- Cuma buku yang boleh "dikumpulkan / terbawa teman". Barang pribadi cuma "hilang". --}}
                        @if ($item->canBeSubmitted())
                            <button type="submit" name="status" value="submitted"
                                class="rounded-full bg-primary px-4 py-2 text-xs font-semibold text-primary-foreground hover:opacity-90">
                                Dikumpulkan / terbawa teman
                            </button>
                        @endif

                        <button type="submit" name="status" value="lost"
                            class="rounded-full border border-destructive/30 px-4 py-2 text-xs font-semibold text-destructive hover:bg-destructive/10">
                            Memang hilang
                        </button>
                    </form>

                    @error('status')
                        <p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                    @error('item')
                        <p class="mt-2 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            {{-- Sudah dijelaskan (boleh diralat) --}}
            @foreach ($resolvedReturn as $item)
                @php $status = $resolutions->get($item->id)->status; @endphp
                <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl bg-card px-4 py-3 shadow-sm">
                    <div>
                        <p class="text-sm font-semibold text-foreground">{{ $item->name }}</p>
                        <p class="text-xs text-muted-foreground">
                            {{ $status === 'submitted' ? 'Dicatat: dikumpulkan / terbawa teman' : 'Dicatat: hilang' }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('items.resolve', $item) }}"
                        onsubmit="return confirm('Ubah catatan untuk {{ e($item->name) }}?')">
                        @csrf
                        <input type="hidden" name="confirmed" value="1">
                        @if ($status === 'submitted')
                            <button type="submit" name="status" value="lost"
                                class="text-xs font-semibold text-destructive hover:underline">Ubah jadi hilang</button>
                        @elseif ($item->canBeSubmitted())
                            <button type="submit" name="status" value="submitted"
                                class="text-xs font-semibold text-primary hover:underline">Ubah jadi dikumpulkan</button>
                        @endif
                    </form>
                </div>
            @endforeach
        </div>
    </div>
@endif