@php
    $items = [
        [
            'id' => 1,
            'name' => 'Laptop',
            'subtitle' => 'Ditambahkan 2 Jul 2026',
            'icon' => 'device-phone-mobile',
            'category' => 'Elektronik',
            'rfid' => 'RF-9F21AC',
            'compartment' => 'Kompartemen Utama',
            'status' => 'connected',
            'lastScanDate' => '2 Jul 2026',
            'lastScanTime' => '07:14',
            'createdAt' => strtotime('2026-07-02'),
        ],
        [
            'id' => 2,
            'name' => 'Buku Fisika',
            'subtitle' => 'Ditambahkan 2 Jul 2026',
            'icon' => 'book-open',
            'category' => 'Buku Pelajaran',
            'rfid' => 'RF-7B33D1',
            'compartment' => 'Kantong Depan',
            'status' => 'connected',
            'lastScanDate' => '2 Jul 2026',
            'lastScanTime' => '07:12',
            'createdAt' => strtotime('2026-07-02'),
        ],
        [
            'id' => 3,
            'name' => 'Kalkulator',
            'subtitle' => 'Ditambahkan 30 Jun 2026',
            'icon' => 'calculator',
            'category' => 'Alat Tulis',
            'rfid' => 'RF-4E88A0',
            'compartment' => 'Kantong Samping',
            'status' => 'not_scanned',
            'lastScanDate' => '30 Jun 2026',
            'lastScanTime' => '08:40',
            'createdAt' => strtotime('2026-06-30'),
        ],
        [
            'id' => 4,
            'name' => 'Botol Minum',
            'subtitle' => 'Ditambahkan 28 Jun 2026',
            'icon' => 'beaker',
            'category' => 'Perlengkapan',
            'rfid' => 'RF-2C915F',
            'compartment' => 'Kantong Samping',
            'status' => 'connected',
            'lastScanDate' => '28 Jun 2026',
            'lastScanTime' => '06:55',
            'createdAt' => strtotime('2026-06-28'),
        ],
        [
            'id' => 5,
            'name' => 'Buku Matematika',
            'subtitle' => 'Ditambahkan 25 Jun 2026',
            'icon' => 'book-open',
            'category' => 'Buku Pelajaran',
            'rfid' => 'RF-A17C62',
            'compartment' => 'Kompartemen Utama',
            'status' => 'not_scanned',
            'lastScanDate' => '25 Jun 2026',
            'lastScanTime' => '07:03',
            'createdAt' => strtotime('2026-06-25'),
        ],
        [
            'id' => 6,
            'name' => 'Sepatu Olahraga',
            'subtitle' => 'Ditambahkan 20 Jun 2026',
            'icon' => 'tag',
            'category' => 'Perlengkapan Olahraga',
            'rfid' => null,
            'compartment' => 'Kompartemen Bawah',
            'status' => 'no_rfid',
            'lastScanDate' => '—',
            'lastScanTime' => '',
            'createdAt' => strtotime('2026-06-20'),
        ],
    ];

    $categoryStyles = [
        'Elektronik' => 'bg-blue-50 text-blue-700',
        'Buku Pelajaran' => 'bg-purple-50 text-purple-700',
        'Alat Tulis' => 'bg-amber-50 text-amber-700',
        'Perlengkapan' => 'bg-emerald-50 text-emerald-700',
        'Perlengkapan Olahraga' => 'bg-slate-100 text-slate-700',
    ];

    $statusStyles = [
        'connected' => ['label' => 'Terhubung', 'class' => 'bg-success/10 text-success'],
        'not_scanned' => ['label' => 'Belum Dipindai', 'class' => 'bg-warning/10 text-warning'],
        'no_rfid' => ['label' => 'Tanpa RFID', 'class' => 'bg-destructive/10 text-destructive'],
    ];

    $itemsForAlpine = collect($items)->map(function ($item) {
        return [
            'id' => $item['id'],
            'name' => $item['name'],
            'category' => $item['category'],
            'status' => $item['status'],
            'createdAt' => $item['createdAt'],
        ];
    })->values();
@endphp

@push('scripts')
<script>
    function itemsPage() {
        return {
            items: [],
            deletedIds: [],
            search: '',
            category: 'all',
            status: 'all',
            sortBy: 'name-asc',

            drawerOpen: false,
            drawerMode: 'add',
            form: {
                name: '',
                category: 'Elektronik',
                rfid: '',
                compartment: '',
                description: '',
            },

            deleteModalOpen: false,
            deleteTarget: null,

            matches(id) {
                if (this.deletedIds.includes(id)) return false;
                const item = this.items.find(i => i.id === id);
                if (!item) return true;

                const searchMatch = item.name.toLowerCase().includes(this.search.toLowerCase());
                const categoryMatch = this.category === 'all' || item.category === this.category;
                const statusMatch = this.status === 'all' || item.status === this.status;

                return searchMatch && categoryMatch && statusMatch;
            },

            sortedIds() {
                let sorted = [...this.items];
                if (this.sortBy === 'name-asc') sorted.sort((a, b) => a.name.localeCompare(b.name));
                if (this.sortBy === 'name-desc') sorted.sort((a, b) => b.name.localeCompare(a.name));
                if (this.sortBy === 'newest') sorted.sort((a, b) => b.createdAt - a.createdAt);
                if (this.sortBy === 'oldest') sorted.sort((a, b) => a.createdAt - b.createdAt);
                return sorted.map(i => i.id);
            },

            visibleCount() {
                return this.items.filter(i => this.matches(i.id)).length;
            },

            reorder() {
                this.$nextTick(() => {
                    const tbody = this.$refs.tbody;
                    if (!tbody) return;
                    const rows = Array.from(tbody.querySelectorAll('tr[data-row-id]'));
                    this.sortedIds().forEach((id) => {
                        const row = rows.find(r => Number(r.dataset.rowId) === id);
                        if (row) tbody.appendChild(row);
                    });
                });
            },

            openAddDrawer() {
                this.drawerMode = 'add';
                this.form = { name: '', category: 'Elektronik', rfid: '', compartment: '', description: '' };
                this.drawerOpen = true;
            },

            openEditDrawer(id) {
                const item = this.items.find(i => i.id === id);
                this.drawerMode = 'edit';
                this.form = {
                    name: item ? item.name : '',
                    category: item ? item.category : 'Elektronik',
                    rfid: '',
                    compartment: '',
                    description: '',
                };
                this.drawerOpen = true;
            },

            closeDrawer() {
                this.drawerOpen = false;
            },

            openDeleteModal(id) {
                this.deleteTarget = id;
                this.deleteModalOpen = true;
            },

            closeDeleteModal() {
                this.deleteModalOpen = false;
                this.deleteTarget = null;
            },

            confirmDelete() {
                if (this.deleteTarget !== null) {
                    this.deletedIds.push(this.deleteTarget);
                }
                this.closeDeleteModal();
            },
        };
    }
</script>
@endpush

<x-layouts.dashboard title="Barang — InCase">
    <div
        x-data="itemsPage()"
        x-init="items = @js($itemsForAlpine); reorder(); $watch('sortBy', () => reorder())"
        class="flex h-screen bg-background"
    >
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <div class="mx-auto max-w-6xl px-6 py-8 sm:px-8">
                {{-- Header --}}
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                            Barang
                        </h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Kelola seluruh barang sekolah yang terhubung dengan tag RFID.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="openAddDrawer()"
                        class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-colors hover:bg-primary/90"
                    >
                        <x-icon.plus class="h-4 w-4" />
                        Tambah Barang
                    </button>
                </div>

                {{-- Toolbar --}}
                <div class="mt-6 flex flex-col gap-3 rounded-2xl border border-border bg-card p-3 sm:flex-row sm:items-center">
                    <div class="relative flex-1">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-muted-foreground">
                            <x-icon.magnifying-glass class="h-4 w-4" />
                        </span>
                        <input
                            type="text"
                            x-model="search"
                            placeholder="Cari nama barang..."
                            class="block w-full rounded-xl border border-border bg-background py-3 pl-11 pr-3.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                    </div>

                    <div class="relative">
                        <select
                            x-model="category"
                            class="appearance-none rounded-xl border border-border bg-background py-3 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="all">Semua Kategori</option>
                            <option value="Elektronik">Elektronik</option>
                            <option value="Buku Pelajaran">Buku Pelajaran</option>
                            <option value="Alat Tulis">Alat Tulis</option>
                            <option value="Perlengkapan">Perlengkapan</option>
                            <option value="Perlengkapan Olahraga">Perlengkapan Olahraga</option>
                        </select>
                        <x-icon.chevron-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>

                    <div class="relative">
                        <select
                            x-model="status"
                            class="appearance-none rounded-xl border border-border bg-background py-3 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="all">Semua Status</option>
                            <option value="connected">Terhubung</option>
                            <option value="not_scanned">Belum Dipindai</option>
                            <option value="no_rfid">Tanpa RFID</option>
                        </select>
                        <x-icon.funnel class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>

                    <div class="relative">
                        <select
                            x-model="sortBy"
                            class="appearance-none rounded-xl border border-border bg-background py-3 pl-3.5 pr-9 text-sm text-foreground focus:border-primary focus:outline-none focus:ring-4 focus:ring-primary/10"
                        >
                            <option value="name-asc">Nama A-Z</option>
                            <option value="name-desc">Nama Z-A</option>
                            <option value="newest">Terbaru</option>
                            <option value="oldest">Terlama</option>
                        </select>
                        <x-icon.arrows-up-down class="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    </div>
                </div>

                {{-- Table --}}
                <div class="mt-6 overflow-hidden rounded-[24px] border border-border bg-card shadow-sm" x-show="visibleCount() > 0">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead>
                                <tr class="border-b border-border bg-muted/40">
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Barang</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Kategori</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">UID RFID</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Kompartemen</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Status</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground">Pindai Terakhir</th>
                                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-wider text-muted-foreground"></th>
                                </tr>
                            </thead>
                            <tbody x-ref="tbody" class="divide-y divide-border">
                                @foreach ($items as $item)
                                    <tr
                                        data-row-id="{{ $item['id'] }}"
                                        x-show="matches({{ $item['id'] }})"
                                        class="cursor-pointer transition-colors hover:bg-muted/40"
                                    >
                                        <td class="px-6 py-5">
                                            <div class="flex items-center gap-3">
                                                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                                                    <x-dynamic-component :component="'icon.' . $item['icon']" class="h-5 w-5" />
                                                </span>
                                                <div class="min-w-0">
                                                    <p class="font-semibold text-foreground">{{ $item['name'] }}</p>
                                                    <p class="text-xs text-muted-foreground">{{ $item['subtitle'] }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $categoryStyles[$item['category']] }}">
                                                {{ $item['category'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            @if ($item['rfid'])
                                                <span class="inline-flex items-center rounded-md bg-muted px-2.5 py-1 font-mono text-xs text-foreground">
                                                    {{ $item['rfid'] }}
                                                </span>
                                            @else
                                                <span class="text-xs text-muted-foreground">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-sm text-muted-foreground">
                                            {{ $item['compartment'] }}
                                        </td>
                                        <td class="px-6 py-5">
                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusStyles[$item['status']]['class'] }}">
                                                {{ $statusStyles[$item['status']]['label'] }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-5">
                                            @if ($item['lastScanTime'])
                                                <p class="text-sm text-foreground">{{ $item['lastScanDate'] }}</p>
                                                <p class="text-xs text-muted-foreground">{{ $item['lastScanTime'] }}</p>
                                            @else
                                                <p class="text-sm text-muted-foreground">{{ $item['lastScanDate'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-6 py-5 text-right">
                                            <x-row-actions-menu :item-id="$item['id']" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Empty state --}}
                <div class="mt-6" x-show="visibleCount() === 0" x-cloak>
                    <x-empty-state
                        icon="archive-box"
                        title="Belum ada barang ditemukan"
                        description="Coba ubah kata kunci pencarian atau filter, atau tambahkan barang baru untuk mulai memantau lewat RFID."
                        button-label="Tambah Barang"
                        button-click="openAddDrawer()"
                    />
                </div>

                {{-- Pagination --}}
                <div class="mt-6 flex flex-col items-center justify-between gap-4 sm:flex-row" x-show="visibleCount() > 0">
                    <p class="text-sm text-muted-foreground">
                        Menampilkan <span class="font-medium text-foreground" x-text="visibleCount()"></span> dari {{ count($items) }} barang
                    </p>

                    <div class="flex items-center gap-1.5">
                        <button type="button" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:bg-muted" aria-label="Halaman sebelumnya">
                            <x-icon.chevron-left class="h-4 w-4" />
                        </button>
                        <button type="button" class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-sm font-semibold text-primary-foreground">
                            1
                        </button>
                        <button type="button" class="flex h-9 w-9 items-center justify-center rounded-lg border border-border bg-card text-muted-foreground transition-colors hover:bg-muted" aria-label="Halaman berikutnya">
                            <x-icon.chevron-right class="h-4 w-4" />
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <x-item-drawer />
        <x-delete-modal />
    </div>
</x-layouts.dashboard>
