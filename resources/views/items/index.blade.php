@php
    // Peta kategori (enum asli dari migration) ke icon & label tampilan
    $categoryIcons = [
        'Book' => 'book-open',
        'Stationery' => 'document-text',
        'Electronics' => 'device-phone-mobile',
        'Sports' => 'tag',
        'Personal' => 'user',
        'Others' => 'cube',
    ];

    $categoryLabels = [
        'Book' => 'Buku',
        'Stationery' => 'Alat Tulis',
        'Electronics' => 'Elektronik',
        'Sports' => 'Olahraga',
        'Personal' => 'Pribadi',
        'Others' => 'Lainnya',
    ];

    // Statistik dihitung dari collection $items yang sudah di-load controller (bukan query tambahan)
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

<x-layouts.dashboard title="Barang — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
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
                            Barang
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Kelola semua barang sekolah yang terdaftar dengan RFID.
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
                            Tambah Barang
                        </button>
                    </div>
                </div>

                {{-- ============ STATISTIC CARDS ============ --}}
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <x-stat-card icon="cube" label="Total Barang" :value="$totalItems" tone="primary" />
                    <x-stat-card icon="check-circle" label="Barang Aktif" :value="$activeItems" tone="success" />
                    <x-stat-card icon="archive-box" label="Barang Diarsipkan" :value="$archivedItems" tone="warning" />
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
                            placeholder="Cari nama barang atau UID RFID..."
                            class="block w-full rounded-xl border border-border bg-background py-2.5 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>

                    <div class="relative">
                        <select
                            id="item-category-filter"
                            onchange="filterItemsTable()"
                            class="appearance-none rounded-xl border border-border bg-background py-2.5 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="">Semua Kategori</option>
                            @foreach ($categoryLabels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-icon.chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>

                    <div class="relative">
                        <select
                            id="item-status-filter"
                            onchange="filterItemsTable()"
                            class="appearance-none rounded-xl border border-border bg-background py-2.5 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="">Semua Status</option>
                            <option value="active">Aktif</option>
                            <option value="archived">Diarsipkan</option>
                        </select>
                        <x-icon.funnel class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>
                </div>

                {{-- ============ TABLE / EMPTY STATE ============ --}}
                @if ($items->isEmpty())
                    <div class="mt-6 flex flex-col items-center justify-center rounded-[24px] border border-dashed border-border bg-card px-8 py-20 text-center">
                        <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <x-icon.archive-box class="h-8 w-8" />
                        </span>
                        <h3 class="mt-5 text-lg font-bold text-foreground">Belum ada barang</h3>
                        <p class="mt-2 max-w-sm text-sm leading-relaxed text-muted-foreground">
                            Mulai daftarkan barang sekolahmu supaya bisa dipantau lewat RFID.
                        </p>
                        <button
                            type="button"
                            onclick="openModal('add-item-modal')"
                            class="mt-6 inline-flex items-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                        >
                            <x-icon.plus class="h-4 w-4" />
                            Tambah Barang Pertama
                        </button>
                    </div>
                @else
                    <div class="mt-6 hidden overflow-hidden rounded-[24px] border border-border bg-card shadow-sm sm:block">
                        <div class="max-h-[600px] overflow-auto">
                            <table class="w-full text-left">
                                <thead class="sticky top-0 z-10 bg-muted/95 backdrop-blur">
                                    <tr class="border-b border-border">
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Nama Barang</th>
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Deskripsi</th>
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Kategori</th>
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">UID RFID</th>
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Terakhir Diubah</th>
                                        <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-table-body" class="divide-y divide-border">
                                    @foreach ($items as $item)
                                        <tr
                                            class="item-row cursor-default transition-colors hover:bg-muted/40"
                                            data-name="{{ strtolower($item->name) }}"
                                            data-rfid="{{ strtolower($item->rfid_uid) }}"
                                            data-category="{{ $item->category }}"
                                            data-status="{{ $item->status }}"
                                        >
                                            <td class="px-6 py-5">
                                                <div class="flex items-center gap-3">
                                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                        <x-dynamic-component :component="'icon.' . ($categoryIcons[$item->category] ?? 'cube')" class="h-5 w-5" />
                                                    </span>
                                                    <p class="font-semibold text-foreground">{{ $item->name }}</p>
                                                </div>
                                            </td>

                                            <td class="px-6 py-5 max-w-xs">
                                                <p class="text-sm text-muted-foreground line-clamp-2">
                                                    {{ $item->description ?: '-' }}
                                                </p>
                                            </td>
                                            <td class="px-6 py-5">
                                                <span class="inline-flex items-center rounded-full bg-muted px-2.5 py-1 text-xs font-medium text-foreground">
                                                    {{ $categoryLabels[$item->category] ?? $item->category }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5">
                                                <span class="inline-flex items-center rounded-md bg-muted px-2.5 py-1 font-mono text-xs text-foreground">
                                                    {{ $item->rfid_uid }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-5">
                                                @if ($item->status === 'active')
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-1 text-xs font-semibold text-success">
                                                        <span class="relative flex h-1.5 w-1.5">
                                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75"></span>
                                                            <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-success"></span>
                                                        </span>
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground">
                                                        <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground/50"></span>
                                                        Diarsipkan
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-5 text-sm text-muted-foreground">
                                                {{ $item->updated_at->translatedFormat('d M Y, H:i') }}
                                            </td>
                                            <td class="px-6 py-5 text-right">
                                                <div class="relative inline-block text-left">


                                                        <button
                                                            type="button"
                                                            data-id="{{ $item->id }}"
                                                            data-name="{{ $item->name }}"
                                                            data-category="{{ $item->category }}"
                                                            data-rfid="{{ $item->rfid_uid }}"
                                                            data-description="{{ $item->description }}"
                                                            data-status="{{ $item->status }}"
                                                            onclick="openEditModal(this)"
                                                            class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-foreground transition-colors hover:bg-muted"
                                                        >
                                                            <x-icon.pencil class="h-4 w-4 text-muted-foreground" />
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

                    {{-- ============ MOBILE CARD LIST (di bawah breakpoint sm) ============ --}}
                    <div id="items-mobile-list" class="mt-6 flex flex-col gap-3 sm:hidden">
                        @foreach ($items as $item)
                            <div
                                class="item-row rounded-2xl border border-border bg-card p-4 shadow-sm transition-colors"
                                data-name="{{ strtolower($item->name) }}"
                                data-rfid="{{ strtolower($item->rfid_uid) }}"
                                data-category="{{ $item->category }}"
                                data-status="{{ $item->status }}"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex min-w-0 items-center gap-3">
                                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                            <x-dynamic-component :component="'icon.' . ($categoryIcons[$item->category] ?? 'cube')" class="h-5 w-5" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-foreground">{{ $item->name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ $categoryLabels[$item->category] ?? $item->category }}</p>
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
                                                data-description="{{ $item->description }}"
                                                data-status="{{ $item->status }}"
                                                onclick="openEditModal(this)"
                                                class="flex w-full items-center gap-2.5 px-4 py-2.5 text-left text-sm text-foreground transition-colors hover:bg-muted"
                                            >
                                                <x-icon.pencil class="h-4 w-4 text-muted-foreground" />
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
                                <p class="mt-2.5 text-xs text-muted-foregration line-clam-2">
                                    {{ $item->description }}
                                </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-md bg-muted px-2.5 py-1 font-mono text-xs text-foreground">
                                        {{ $item->rfid_uid }}
                                    </span>

                                    @if ($item->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-success/10 px-2.5 py-1 text-xs font-semibold text-success">
                                            <span class="relative flex h-1.5 w-1.5">
                                                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-success opacity-75"></span>
                                                <span class="relative inline-flex h-1.5 w-1.5 rounded-full bg-success"></span>
                                            </span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-muted px-2.5 py-1 text-xs font-semibold text-muted-foreground">
                                            <span class="h-1.5 w-1.5 rounded-full bg-muted-foreground/50"></span>
                                            Diarsipkan
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-2.5 text-xs text-muted-foreground">
                                    Diubah {{ $item->updated_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                        @endforeach
                    </div>

                    {{-- Pesan kalau hasil filter/pencarian kosong --}}
                    <p id="no-results-message" class="mt-6 hidden text-center text-sm text-muted-foreground">
                        Gak ada barang yang cocok sama pencarian atau filter kamu.
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
                <h3 class="text-lg font-bold text-foreground">Tambah Barang</h3>
                <button type="button" onclick="closeModal('add-item-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                    <x-icon.x-mark class="h-5 w-5" />
                </button>
            </div>

            <form method="POST" action="{{ route('items.store') }}" class="mt-6 flex flex-col gap-5">
                @csrf
                <input type="hidden" name="_form" value="add">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Barang</label>
                    <input
                        type="text"
                        name="name"
                        value="{{ old('_form') === 'add' ? old('name') : '' }}"
                        placeholder="Contoh: Buku Fisika"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                    @error('name')
                        <p class="mt-1.5 text-xs font-medium text-destructive">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kategori</label>
                    <select
                        name="category"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        @foreach ($categoryLabels as $value => $label)
                            <option value="{{ $value }}" @selected(old('category') === $value)>{{ $label }}</option>
                        @endforeach
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
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Deskripsi</label>
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
                        Simpan Barang
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
                <h3 class="text-lg font-bold text-foreground">Edit Barang</h3>
                <button type="button" onclick="closeModal('edit-item-modal')" class="flex h-8 w-8 items-center justify-center rounded-lg text-muted-foreground transition-colors hover:bg-muted hover:text-foreground">
                    <x-icon.x-mark class="h-5 w-5" />
                </button>
            </div>

            <form id="edit-item-form" method="POST" action="" class="mt-6 flex flex-col gap-5">
                @csrf
                @method('PUT')
                <input type="hidden" name="_form" value="edit">

                <div>
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Nama Barang</label>
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
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Kategori</label>
                    <select
                        name="category"
                        id="edit-category"
                        class="block w-full rounded-xl border border-border bg-background px-3.5 py-2.5 text-sm text-foreground transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                    >
                        @foreach ($categoryLabels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
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
                    <label class="mb-1.5 block text-sm font-medium text-foreground">Deskripsi</label>
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

            <h3 class="mt-4 text-lg font-bold text-foreground">Hapus barang ini?</h3>
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

    {{-- ============ VANILLA JS (No Alpine, No Vue, No React) ============ --}}
    <script>
        function openModal(id) {
            const modal = document.getElementById(id);
            const panel = modal.querySelector('.modal-panel');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // requestAnimationFrame biar browser sempet render state awal (opacity-0 scale-95)
            // sebelum ditransisiin ke state akhir — smooth fade + scale in
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

            // Tunggu transisi kelar (200ms) baru bener-bener disembunyiin
            setTimeout(function () {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }, 150);
        }

        function openEditModal(button) {
            document.getElementById('edit-name').value = button.dataset.name;
            document.getElementById('edit-category').value = button.dataset.category;
            document.getElementById('edit-rfid_uid').value = button.dataset.rfid;
            document.getElementById('edit-description').value = button.dataset.description;
            document.getElementById('edit-status').value = button.dataset.status;
            document.getElementById('edit-item-form').action = '/items/' + button.dataset.id;
            closeAllActionMenus();
            openModal('edit-item-modal');
        }

        function openDeleteModal(button) {
            document.getElementById('delete-item-name').textContent =
                'Barang "' + button.dataset.name + '" akan dihapus permanen dan gak bisa dibatalin.';
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
            const category = document.getElementById('item-category-filter').value;
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

        // Buka kembali modal yang relevan otomatis kalau ada validation error dari server
        var reopenModal = @json($reopenModal);
        if (reopenModal) {
            document.addEventListener('DOMContentLoaded', function () {
                openModal(reopenModal);
            });
        }
    </script>
</x-layouts.dashboard>