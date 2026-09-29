@props([
    'subject',
    'isTeacher' => false,
    'canEdit' => false,
    'canDelete' => false,
    'variant' => 'weekly', // 'today' | 'weekly' — controls type scale only, markup stays identical
])

@php
    $isToday = $variant === 'today';
    $maxVisibleItems = 4;
    $visibleItems = $subject->requiredItems->take($maxVisibleItems);
    $remainingItemsCount = max(0, $subject->requiredItems->count() - $maxVisibleItems);
    $fieldClass = 'block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground focus:border-blue-500 focus:outline-none focus:ring-4 focus:ring-blue-500/10';
@endphp

<div {{ $attributes->merge(['class' => 'group relative rounded-[24px] border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md']) }}>
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h3 class="{{ $isToday ? 'text-lg' : 'text-base' }} font-bold text-foreground">
                {{ $subject->name }}
            </h3>
            <p class="mt-1 flex items-center gap-1.5 {{ $isToday ? 'text-sm font-medium' : 'text-xs' }} text-muted-foreground">
                <x-icon.clock class="{{ $isToday ? 'h-4 w-4' : 'h-3.5 w-3.5' }} shrink-0" />
                {{ \Carbon\Carbon::parse($subject->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($subject->end_time)->format('H:i') }}
            </p>
        </div>

        @if ($subject->has_exam)
            <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-destructive/10 px-2.5 py-1 text-[11px] font-semibold text-destructive">
                <x-icon.exclamation-triangle class="h-3 w-3" />
                Ujian
            </span>
        @endif
    </div>

    <div class="mt-3 flex flex-wrap items-center gap-3 {{ $isToday ? 'text-sm' : 'text-xs' }} text-muted-foreground">
        <span class="inline-flex items-center gap-1.5">
            <x-icon.user class="{{ $isToday ? 'h-4 w-4' : 'h-3.5 w-3.5' }} shrink-0" />
            {{ $subject->teacher->name ?? '—' }}
        </span>
        @if ($subject->location)
            <span class="inline-flex items-center gap-1.5">
                <x-icon.map-pin class="{{ $isToday ? 'h-4 w-4' : 'h-3.5 w-3.5' }} shrink-0" />
                Ruang {{ $subject->location }}
            </span>
        @endif
    </div>

    @if (! empty($subject->homework))
        <div class="mt-3 flex items-start gap-2 rounded-xl bg-warning/10 px-3 py-2.5 text-xs text-warning">
            <x-icon.document-text class="mt-0.5 h-3.5 w-3.5 shrink-0" />
            <span><span class="font-semibold">PR:</span> {{ $subject->homework }}</span>
        </div>
    @endif

    @if ($subject->requiredItems->isNotEmpty())
        <div class="mt-3 flex flex-wrap gap-1.5">
            @foreach ($visibleItems as $item)
                <span class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium text-foreground">
                    {{ $item->name }}
                </span>
            @endforeach

            @if ($remainingItemsCount > 0)
                <span
                    class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-[11px] font-medium text-muted-foreground"
                    title="{{ $subject->requiredItems->skip($maxVisibleItems)->pluck('name')->implode(', ') }}"
                >
                    +{{ $remainingItemsCount }} lainnya
                </span>
            @endif
        </div>
    @endif

    @if ($isTeacher)
        @php
            // Perpindahan ruang yang masih berlaku (hari ini atau ke depan) untuk jadwal ini.
            $upcomingRoomChanges = $subject->roomChanges
                ->filter(fn ($rc) => \Carbon\Carbon::parse($rc->date)->gte(today()))
                ->sortBy('date')
                ->values();
        @endphp

        @if ($upcomingRoomChanges->isNotEmpty())
            <div class="mt-3 flex flex-col gap-2">
                @foreach ($upcomingRoomChanges as $rc)
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-primary/5 px-3 py-2 text-xs text-primary">
                        <span class="inline-flex items-center gap-1.5">
                            <x-icon.map-pin class="h-3.5 w-3.5 shrink-0" />
                            Pindah ke {{ $rc->location }} pada {{ \Carbon\Carbon::parse($rc->date)->translatedFormat('d M Y') }}
                        </span>
                        <form method="POST" action="{{ route('subjects.room-changes.destroy', [$subject, $rc]) }}"
                            onsubmit="return confirm('Batalkan perpindahan ruang ini? Ruang balik ke {{ $subject->location }}.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="font-semibold text-destructive hover:underline">
                                Batalkan
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    @if ($canEdit || $isTeacher)
        <div class="mt-4 flex items-center gap-2 border-t border-border pt-4">
            @if ($canEdit)
                <button
                    type="button"
                    data-id="{{ $subject->id }}"
                    onclick="openEditModal(this)"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
                >
                    <x-icon.pencil class="h-3.5 w-3.5" />
                    Edit
                </button>
            @endif

            @if ($isTeacher)
                <button
                    type="button"
                    onclick="openModal('room-change-modal-{{ $subject->id }}')"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-border py-2 text-xs font-semibold text-foreground transition-colors hover:bg-muted"
                >
                    <x-icon.map-pin class="h-3.5 w-3.5" />
                    Pindah Ruang
                </button>
            @endif

            @if ($canDelete)
                <button
                    type="button"
                    data-id="{{ $subject->id }}"
                    data-name="{{ $subject->name }}"
                    onclick="openDeleteModal(this)"
                    class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-xl border border-destructive/20 py-2 text-xs font-semibold text-destructive transition-colors hover:bg-destructive/10"
                >
                    <x-icon.trash class="h-3.5 w-3.5" />
                    Hapus
                </button>
            @endif
        </div>
    @endif

    @if ($isTeacher)
        {{-- Modal khusus jadwal ini: pindah ruang untuk satu tanggal (harus jatuh di hari {{ $subject->day }}) --}}
        <div id="room-change-modal-{{ $subject->id }}" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
            <div onclick="closeModal('room-change-modal-{{ $subject->id }}')" class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"></div>

            <div class="modal-panel relative w-full max-w-sm scale-95 rounded-[28px] bg-white p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out">
                <div class="flex items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <x-icon.map-pin class="h-4.5 w-4.5" />
                    </span>
                    <div class="flex-1">
                        <h3 class="text-base font-bold text-foreground">Pindah Ruang</h3>
                        <p class="text-xs text-slate-400">{{ $subject->name }} — ruang biasa: {{ $subject->location ?: 'belum diisi' }}</p>
                    </div>
                    <button type="button" onclick="closeModal('room-change-modal-{{ $subject->id }}')"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                        <x-icon.x-mark class="h-5 w-5" />
                    </button>
                </div>

                @if ($errors->has('location') || $errors->has('date'))
                    <div class="mt-4 flex items-start gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm text-destructive">
                        <x-icon.exclamation-triangle class="mt-0.5 h-4 w-4 shrink-0" />
                        <div>
                            @foreach ($errors->get('date') as $e)
                                <p>{{ $e }}</p>
                            @endforeach
                            @foreach ($errors->get('location') as $e)
                                <p>{{ $e }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('subjects.room-changes.store', $subject) }}" class="schedule-form mt-5 flex flex-col gap-4">
                    @csrf

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Tanggal</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                <x-icon.calendar class="h-4 w-4" />
                            </span>
                            <input type="date" name="date" min="{{ today()->toDateString() }}" class="{{ $fieldClass }}">
                        </div>
                        <p class="mt-1.5 text-xs text-muted-foreground">
                            Harus tanggal yang jatuh di hari {{ $subject->day === 'Monday' ? 'Senin' : ($subject->day === 'Tuesday' ? 'Selasa' : ($subject->day === 'Wednesday' ? 'Rabu' : ($subject->day === 'Thursday' ? 'Kamis' : ($subject->day === 'Friday' ? 'Jumat' : ($subject->day === 'Saturday' ? 'Sabtu' : 'Minggu'))))) }},
                            karena jadwal ini cuma ada di hari itu.
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-foreground">Ruang Pengganti</label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-3.5 flex items-center text-slate-400">
                                <x-icon.map-pin class="h-4 w-4" />
                            </span>
                            <input type="text" name="location" placeholder="Contoh: Masjid, Lapangan" class="{{ $fieldClass }}">
                        </div>
                    </div>

                    <div class="mt-1 flex items-center gap-3">
                        <button type="button" onclick="closeModal('room-change-modal-{{ $subject->id }}')"
                            class="flex-1 rounded-full border border-border py-3 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                            Batal
                        </button>
                        <button type="submit"
                            class="submit-btn schedule-ripple-btn flex flex-1 items-center justify-center gap-2 rounded-full bg-gradient-to-r from-blue-600 to-indigo-600 py-3 text-sm font-semibold text-primary-foreground shadow-md shadow-blue-600/25 transition-all hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-70">
                            <span class="btn-label">Pindahkan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>