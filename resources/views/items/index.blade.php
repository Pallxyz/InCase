@php
    // Mapping label dan icon berdasarkan opsi kategori
    $categoryLabels = [
        'paket' => 'Buku Paket',
        'tulis' => 'Buku Tulis',
        'lks'   => 'Buku LKS',
    ];

    // Statistik dihitung dari collection $items
    $totalItems = $items->count();
    $activeItems = $items->where('status', 'active')->count();
    $archivedItems = $items->where('status', 'archived')->count();
    $rfidRegistered = $items->whereNotNull('rfid_uid')->count();
    
    // Modal mana yang harus dibuka ulang otomatis kalau validasi gagal
    $reopenModal = null;
    if ($errors->any()) {
        $reopenModal = old('_form') === 'edit' ? 'edit-item-modal' : 'add-item-modal';
    }
@endphp

<x-layouts.dashboard title="Buku Pelajaran — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="Buku Pelajaran — InCase" />

            <div class="mx-auto max-w-6xl px-6 py-8 sm:px-8">

                {{-- ============ FLASH MESSAGES ============ --}}
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-2 rounded-2xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
                        <x-icon.check-circle class="h-4 w-4 shrink-0" />
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-center gap-2 rounded-2xl bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive">
                        <x-icon.x-circle class="h-4 w-4 shrink-0" />
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 flex items-center gap-2 rounded-2xl bg-warning/10 px-4 py-3 text-sm font-medium text-warning">
                        <x-icon.exclamation-triangle class="h-4 w-4 shrink-0" />
                        {{ session('warning') }}
                    </div>
                @endif

                {{-- ============ HEADER ============ --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Buku Pelajaran
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Kelola semua daftar buku paket, buku tulis, dan LKS yang terdaftar dengan RFID.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            title="Segera hadir"
                            disabled
                            class="inline-flex cursor-not-allowed items-center justify-center gap-2 rounded-full border border-border bg-card px-4 py-2.5 text-sm font-semibold text-muted-foreground opacity-60"
                        >
                            <x-icon.viewfinder-circle class="h-4 w-4" />
                            Pindai RFID
                        </button>

                        <button
                            type="button"
                            onclick="openModal('add-item-modal')"
                            class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            <x-icon.plus class="h-4 w-4" />
                            Tambah Buku
                        </button>
                    </div>
                </div>

                {{-- ============ STATISTIC CARDS ============ --}}
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card icon="book-open" label="Total Buku" :value="$totalItems" tone="primary" />
                    <x-stat-card icon="check-circle" label="Buku Aktif" :value="$activeItems" tone="success" />
                    <x-stat-card icon="archive-box" label="Buku Diarsipkan" :value="$archivedItems" tone="warning" />
                    <x-stat-card icon="tag" label="RFID Terdaftar" :value="$rfidRegistered" tone="accent" />
                </div>

                {{-- ============ SEARCH & FILTER ============ --}}
                <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-border bg-card p-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-muted-foreground">
                            <x-icon.magnifying-glass class="h-4 w-4" />
                        </span>
                        <input
                            type="text"
                            id="item-search"
                            onkeyup="filterItemsTable()"
                            placeholder="Cari judul buku atau UID RFID..."
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>

                    <div class="flex gap-2">
                        {{-- Filter Kategori --}}
                        <div class="relative flex-1 sm:w-44">
                            <select
                                id="item-category-filter"
                                onchange="filterItemsTable()"
                                class="w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                            >
                                <option value="">Semua Kategori</option>
                                <option value="paket">Buku Paket</option>
                                <option value="tulis">Buku Tulis</option>
                                <option value="lks">Buku LKS</option>
                            </select>
                            <x-icon.funnel class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        </div>

                        {{-- Filter Status --}}
                        <div class="relative flex-1 sm:w-40">
                            <select
                                id="item-status-filter"
                                onchange="filterItemsTable()"
                                class="w-full appearance-none rounded-xl border border-border bg-background py-2.5 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                            >
                                <option value="">Semua Status</option>
                                <option value="active">Aktif</option>
                                <option value="archived">Diarsipkan</option>
                            </select>
                            <x-icon.funnel class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                        </div>
                    </div>
                </div>

                {{-- ============ TABLE / EMPTY STATE ============ --}}
                @if ($items->isEmpty())
                    <div class="mt-6 flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card px-8 py-20 text-center">
                        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <x-icon.book-open class="h-8 w-8" />
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-foreground">Belum ada buku</h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground">
                            Mulai daftarkan buku paket, buku tulis, atau LKS sekolahmu supaya bisa dipantau lewat RFID.
                        </p>
                        <button
                            type="button"
                            onclick="openModal('add-item-modal')"
                            class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            <x-icon.plus class="h-4 w-4" />
                            Tambah Buku Pertama
                        </button>
                    </div>
                @else
                    {{-- DESKTOP TABLE VIEW --}}
                    <div class="mt-6 hidden overflow-hidden rounded-[24px] border border-border bg-card shadow-sm sm:block">
                        <div class="max-h-[600px] overflow-x-auto overflow-y-auto">
                            <table class="w-full text-left table-fixed min-w-[850px]">
                                <thead class="sticky top-0 z-10 bg-muted/95 backdrop-blur">
                                    <tr class="border-b border-border">
                                        <th class="w-[26%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Judul Buku</th>
                                        <th class="w-[14%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Kategori</th>
                                        <th class="w-[22%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Deskripsi</th>
                                        <th class="w-[14%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground text-center">UID RFID</th>
                                        <th class="w-[10%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground text-center">Jumlah</th>
                                        <th class="w-[10%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground text-center">Status</th>
                                        <th class="w-[4%] px-5 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground text-right"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-table-body" class="divide-y divide-border">
                                    @foreach ($items as $item)
                                        <tr
                                            class="item-row transition-colors hover:bg-muted/40"
                                            data-name="{{ strtolower($item->name) }}"
                                            data-rfid="{{ strtolower($item->rfid_uid) }}"
                                            data-category="{{ strtolower($item->category) }}"
                                            data-status="{{ $item->status }}"
                                        >
                                            {{-- Judul Buku --}}
                                            <td class="px-5 py-4 align-middle">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                        @if($item->category === 'tulis' || $item->category === 'lks')
                                                            <x-icon.document-text class="h-5 w-5" />
                                                        @else
                                                            <x-icon.book-open class="h-5 w-5" />
                                                        @endif
                                                    </span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="font-semibold text-foreground truncate" title="{{ $item->name }}">{{ $item->name }}</p>
                                                        <p class="text-xs text-muted-foreground">Diubah {{ $item->updated_at->translatedFormat('d M Y, H:i') }}</p>
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Kategori --}}
                                            <td class="px-5 py-4 align-middle">
                                                <span class="inline-flex items-center rounded-lg bg-muted px-2.5 py-1 text-xs font-medium text-foreground whitespace-nowrap">
                                                    {{ $categoryLabels[$item->category] ?? 'Buku Paket' }}
                                                </span>
                                            </td>

                                            {{-- Deskripsi --}}
                                            <td class="px-5 py-4 align-middle">
                                                <p class="text-sm text-muted-foreground line-clamp-2" title="{{ $item->description }}">
                                                    {{ $item->description ?: '-' }}
                                                </p>
                                            </td>

                                            {{-- UID RFID --}}
                                            <td class="px-5 py-4 align-middle text-center">
                                                <span class="inline-flex items-center rounded-md bg-muted px-2.5 py-1 font-mono text-xs text-foreground whitespace-nowrap">
                                                    {{ $item->rfid_uid ?: '-' }}
                                                </span>
                                            </td>

                                            {{-- Jumlah --}}
                                            <td class="px-5 py-4 align-middle text-center">
                                                <span class="inline-flex items-center rounded-md bg-muted/80 px-2.5 py-1 font-semibold text-xs text-foreground whitespace-nowrap">
                                                    {{ $item->quantity }} Pcs
                                                </span>
                                            </td>

                                            {{-- Status --}}
                                            <td class="px-5 py-4 align-middle text-center">
                                                @if ($item->status === 'active')
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-1 text-xs font-semibold text-success whitespace-nowrap">
                                                        <span class="relative flex h-1.5 w-1.5">
                                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75"></span>
                                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-success"></span>
                                                        </span>
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground whitespace-nowrap">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground/50"></span>
                                                        Diarsipkan
                                                    </span>
                                                @endif
                                            </td>

                                            {{-- Aksi --}}
                                            <td class="px-5 py-4 align-middle text-right">
                                                <div class="relative inline-block text-left">
                                                    <button
                                                        type="button"
                                                        onclick="toggleActionMenu(event, 'action-menu-desktop-{{ $item->id }}')"
                                                        class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                                        aria-label="Buka menu aksi"
                                                    >
                                                        <x-icon.ellipsis-vertical class="h-4 w-4" />
                                                    </button>

                                                    <div
                                                        id="action-menu-desktop-{{ $item->id }}"
                                                        class="action-menu absolute right-0 z-20 mt-2 hidden w-36 overflow-hidden rounded-xl border border-border bg-card shadow-lg"
                                                    >
                                                        <button
                                                            type="button"
                                                            data-id="{{ $item->id }}"
                                                            data-name="{{ $item->name }}"
                                                            data-category="{{ $item->category }}"
                                                            data-rfid="{{ $item->rfid_uid }}"
                                                            data-quantity="{{ $item->quantity }}"
                                                            data-description="{{ $item->description }}"
                                                            data-status="{{ $item->status }}"
                                                            onclick="openEditModal(this)"
                                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-foreground transition-colors hover:bg-muted"
                                                        >
                                                            <x-icon.book-open class="h-4 w-4 text-muted-foreground" />
                                                            Edit
                                                        </button>
                                                        <button
                                                            type="button"
                                                            data-id="{{ $item->id }}"
                                                            data-name="{{ $item->name }}"
                                                            onclick="openDeleteModal(this)"
                                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-destructive transition-colors hover:bg-destructive/10"
                                                        >
                                                            <x-icon.trash class="h-4 w-4" />
                                                            Hapus
                                                        </button>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ============ MOBILE CARD LIST ============ --}}
                    <div id="items-mobile-list" class="mt-6 flex flex-col gap-3.5 sm:hidden">
                        @foreach ($items as $item)
                            <div
                                class="item-row flex flex-col justify-between rounded-2xl border border-border bg-card p-4 shadow-sm transition-colors gap-3"
                                data-name="{{ strtolower($item->name) }}"
                                data-rfid="{{ strtolower($item->rfid_uid) }}"
                                data-category="{{ strtolower($item->category) }}"
                                data-status="{{ $item->status }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                            @if($item->category === 'tulis' || $item->category === 'lks')
                                                <x-icon.document-text class="h-5 w-5" />
                                            @else
                                                <x-icon.book-open class="h-5 w-5" />
                                            @endif
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-foreground leading-tight">{{ $item->name }}</p>
                                            <p class="text-xs text-muted-foreground mt-0.5">
                                                {{ $categoryLabels[$item->category] ?? 'Buku Paket' }} • {{ $item->updated_at->translatedFormat('d M Y') }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="relative shrink-0">
                                        <button
                                            type="button"
                                            onclick="toggleActionMenu(event, 'action-menu-mobile-{{ $item->id }}')"
                                            class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                            aria-label="Buka menu aksi"
                                        >
                                            <x-icon.ellipsis-vertical class="h-4 w-4" />
                                        </button>

                                        <div
                                            id="action-menu-mobile-{{ $item->id }}"
                                            class="action-menu absolute right-0 z-20 mt-2 hidden w-40 overflow-hidden rounded-xl border border-border bg-card shadow-lg"
                                        >
                                            <button
                                                type="button"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                data-category="{{ $item->category }}"
                                                data-rfid="{{ $item->rfid_uid }}"
                                                data-quantity="{{ $item->quantity }}"
                                                data-description="{{ $item->description }}"
                                                data-status="{{ $item->status }}"
                                                onclick="openEditModal(this)"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-foreground transition-colors hover:bg-muted"
                                            >
                                                <x-icon.book-open class="h-4 w-4 text-muted-foreground" />
                                                Edit
                                            </button>
                                            <button
                                                type="button"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name }}"
                                                onclick="openDeleteModal(this)"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-destructive transition-colors hover:bg-destructive/10"
                                            >
                                                <x-icon.trash class="h-4 w-4" />
                                                Hapus
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                @if ($item->description)
                                    <p class="text-xs text-muted-foreground line-clamp-2">
                                        {{ $item->description }}
                                    </p>
                                @endif

                                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-border/60 pt-3">
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-md bg-muted px-2 py-0.5 font-mono text-xs text-foreground">
                                            {{ $item->rfid_uid ?: '-' }}
                                        </span>

                                        <span class="inline-flex items-center rounded-md bg-muted px-2 py-0.5 font-semibold text-xs text-foreground">
                                            {{ $item->quantity }} Pcs
                                        </span>
                                    </div>

                                    @if ($item->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-0.5 text-xs font-semibold text-success">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75"></span>
                                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-success"></span>
                                            </span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-0.5 text-xs font-semibold text-muted-foreground">
                                            <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground/50"></span>
                                            Diarsipkan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pesan kalau hasil filter/pencarian kosong --}}
                    <p id="no-results-message" class="mt-6 hidden text-center text-sm text-muted-foreground">
                        Gak ada buku yang cocok sama pencarian atau filter kamu.
                    </p>
                @endif
            </div>
        </main>
    </div>

    {{-- ============ ADD ITEM MODAL ============ --}}
    <div id="add-item-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
        <div onclick="closeModal('add-item-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <div class="modal-panel relative w-full max-w-lg scale-95 rounded-[24px] bg-card p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-foreground">Tambah Buku Pelajaran</h3>
                <button type="button" onclick="closeModal('add-item-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                    <x-icon.x-mark class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('items.store') }}" class="mt-6 flex flex-col gap-5">
                @csrf
                <input type="hidden" name="_form" value="add">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Judul Buku / Mata Pelajaran</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('_form') === 'add' ? old('name') : '' }}"
                        placeholder="Contoh: Matematika Kelas X"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kategori Buku</label>
                    <select
                        name="category"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        <option value="paket" @selected(old('category') === 'paket')>Buku Paket</option>
                        <option value="tulis" @selected(old('category') === 'tulis')>Buku Tulis</option>
                        <option value="lks" @selected(old('category') === 'lks')>Buku LKS</option>
                    </select>
                    @error('category')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">UID RFID</label>
                    <input
                        type="text"
                        name="rfid_uid"
                        value="{{ old('_form') === 'add' ? old('rfid_uid') : '' }}"
                        placeholder="Contoh: RF-9F21AC"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 font-mono text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('rfid_uid')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Jumlah Buku</label>
                    <input
                        type="number"
                        name="quantity"
                        min="0"
                        value="{{ old('_form') === 'add' ? old('quantity', 1) : 1 }}"
                        placeholder="Contoh: 10"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('quantity')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Deskripsi / Catatan</label>
                    <textarea
                        name="description"
                        rows="3"
                        placeholder="Catatan tambahan (opsional)"
                        class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >{{ old('_form') === 'add' ? old('description') : '' }}</textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Status</label>
                    <select
                        name="status"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        <option value="active" @selected(old('status') !== 'archived')>Aktif</option>
                        <option value="archived" @selected(old('status') === 'archived')>Diarsipkan</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-2 flex items-center gap-3">
                    <button
                        type="button"
                        onclick="closeModal('add-item-modal')"
                        class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Simpan Buku
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ EDIT ITEM MODAL ============ --}}
    <div id="edit-item-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
        <div onclick="closeModal('edit-item-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <div class="modal-panel relative w-full max-w-lg scale-95 rounded-[24px] bg-card p-6 opacity-0 shadow-2xl transition-all duration-200 ease-out sm:p-8">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-foreground">Edit Buku Pelajaran</h3>
                <button type="button" onclick="closeModal('edit-item-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                    <x-icon.x-mark class="h-5 w-5" />
                </button>
            </div>

            <form id="edit-item-form" method="POST" action="" class="mt-6 flex flex-col gap-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Judul Buku / Mata Pelajaran</label>
                    <input
                        type="text"
                        name="name"
                        id="edit-name"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kategori Buku</label>
                    <select
                        name="category"
                        id="edit-category"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        <option value="paket">Buku Paket</option>
                        <option value="tulis">Buku Tulis</option>
                        <option value="lks">Buku LKS</option>
                    </select>
                    @error('category')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">UID RFID</label>
                    <input
                        type="text"
                        name="rfid_uid"
                        id="edit-rfid_uid"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 font-mono text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('rfid_uid')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Jumlah Buku</label>
                    <input
                        type="number"
                        name="quantity"
                        id="edit-quantity"
                        min="0"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('quantity')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Deskripsi / Catatan</label>
                    <textarea
                        name="description"
                        id="edit-description"
                        rows="3"
                        class="block w-full resize-none rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    ></textarea>
                    @error('description')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Status</label>
                    <select
                        name="status"
                        id="edit-status"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        <option value="active">Aktif</option>
                        <option value="archived">Diarsipkan</option>
                    </select>
                    @error('status')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-2 flex items-center gap-3">
                    <button
                        type="button"
                        onclick="closeModal('edit-item-modal')"
                        class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                    >
                        Batal
                    </button>
                    <button
                        type="submit"
                        class="flex-1 rounded-full bg-primary py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ============ DELETE CONFIRMATION MODAL ============ --}}
    <div id="delete-item-modal" class="fixed inset-0 z-50 hidden items-center justify-center px-4">
        <div onclick="closeModal('delete-item-modal')" class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"></div>

        <div class="modal-panel relative w-full max-w-sm scale-95 rounded-[24px] bg-card p-6 text-center opacity-0 shadow-2xl transition-all duration-200 ease-out">
            <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-destructive/10 text-destructive">
                <x-icon.trash class="h-7 w-7" />
            </span>

            <h3 class="mt-4 text-lg font-bold text-foreground">Hapus buku ini?</h3>
            <p id="delete-item-name" class="mt-2 text-sm leading-relaxed text-muted-foreground"></p>

            <form id="delete-item-form" method="POST" action="" class="mt-6 flex items-center gap-3">
                @csrf
                @method('DELETE')

                <button
                    type="button"
                    onclick="closeModal('delete-item-modal')"
                    class="flex-1 rounded-full border border-border py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="flex-1 rounded-full bg-destructive py-2.5 text-sm font-semibold text-white transition-colors hover:bg-destructive/90"
                >
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>

    {{-- ============ VANILLA JS ============ --}}
    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            const panel = modal.querySelector('.modal-panel');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            requestAnimationFrame(function () {
                if (panel) {
                    panel.classList.remove('opacity-0', 'scale-95');
                    panel.classList.add('opacity-100', 'scale-100');
                }
            });
        }

        function closeModal(id) {
            const modal = document.getElementById(id);
            const panel = modal.querySelector('.modal-panel');

            if (panel) {
                panel.classList.remove('opacity-100', 'scale-100');
                panel.classList.add('opacity-0', 'scale-95');
            }

            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 150);
        }

        function openEditModal(button) {
            document.getElementById('edit-name').value = button.dataset.name;
            document.getElementById('edit-category').value = button.dataset.category || 'paket';
            document.getElementById('edit-rfid_uid').value = button.dataset.rfid;
            document.getElementById('edit-quantity').value = button.dataset.quantity;
            document.getElementById('edit-description').value = button.dataset.description;
            document.getElementById('edit-status').value = button.dataset.status;
            document.getElementById('edit-item-form').action = '/items/' + button.dataset.id;
            closeAllActionMenus();
            openModal('edit-item-modal');
        }

        function openDeleteModal(button) {
            document.getElementById('delete-item-name').textContent =
                'Buku "' + button.dataset.name + '" akan dihapus permanen dan tidak dapat dikembalikan.';
            document.getElementById('delete-item-form').action = '/items/' + button.dataset.id;
            closeAllActionMenus();
            openModal('delete-item-modal');
        }

        function toggleActionMenu(event, id) {
            event.stopPropagation();
            const menu = document.getElementById(id);
            const isOpen = !menu.classList.contains('hidden');
            closeAllActionMenus();
            if (!isOpen) {
                menu.classList.remove('hidden');
            }
        }

        function closeAllActionMenus() {
            document.querySelectorAll('.action-menu').forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }

        document.addEventListener('click', function () {
            closeAllActionMenus();
        });

        function filterItemsTable() {
            const search = document.getElementById('item-search').value.toLowerCase();
            const category = document.getElementById('item-category-filter').value.toLowerCase();
            const status = document.getElementById('item-status-filter').value;
            const rows = document.querySelectorAll('.item-row');
            let visibleCount = 0;

            rows.forEach(function (row) {
                const matchesSearch = row.dataset.name.includes(search) || row.dataset.rfid.includes(search);
                const matchesCategory = category === '' || row.dataset.category === category;
                const matchesStatus = status === '' || row.dataset.status === status;

                if (matchesSearch && matchesCategory && matchesStatus) {
                    row.classList.remove('hidden');
                    visibleCount++;
                } else {
                    row.classList.add('hidden');
                }
            });

            const noResults = document.getElementById('no-results-message');
            if (noResults) {
                noResults.classList.toggle('hidden', visibleCount !== 0);
            }
        }

        var reopenModal = @json($reopenModal);
        if (reopenModal) {
            document.addEventListener('DOMContentLoaded', function () {
                openModal(reopenModal);
            });
        }
    </script>
</x-layouts.dashboard>