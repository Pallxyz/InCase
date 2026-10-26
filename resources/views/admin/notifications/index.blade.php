<x-layouts.dashboard title="Notifikasi — InCase">
    <div class="flex h-screen bg-background">
        <x-sidebar />

        <main class="scrollbar-none h-screen flex-1 overflow-y-auto lg:ml-64">
            <x-mobile-topbar title="Notifikasi — InCase" />

            <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">

                @if (session('success'))
                    <div class="mb-6 flex items-center gap-2 rounded-2xl bg-success/10 px-4 py-3 text-sm font-medium text-success">
                        <x-icon.check-circle class="h-4 w-4 shrink-0" />
                        {{ session('success') }}
                    </div>
                @endif

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">Notifikasi</h1>
                        <p class="mt-1.5 text-sm text-muted-foreground">
                            Laporan dari siswa, misalnya barang elektronik yang dilaporkan hilang.
                        </p>
                    </div>

                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                            @csrf
                            <button type="submit"
                                class="rounded-full border border-border bg-card px-4 py-2.5 text-sm font-semibold text-foreground transition-colors hover:bg-muted">
                                Tandai semua dibaca ({{ $unreadCount }})
                            </button>
                        </form>
                    @endif
                </div>

                <div class="mt-6 flex flex-col gap-3">
                    @forelse ($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isUnread = $notification->read_at === null;
                            $isItemLost = ($data['type'] ?? null) === 'item_lost';
                        @endphp

                        <div class="rounded-2xl border p-5 {{ $isUnread ? 'border-warning/40 bg-warning/5' : 'border-border bg-card' }}">
                            <div class="flex items-start gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $isItemLost ? 'bg-destructive/10 text-destructive' : 'bg-primary/10 text-primary' }}">
                                    @if ($isItemLost)
                                        <x-icon.exclamation-triangle class="h-5 w-5" />
                                    @else
                                        <x-icon.bell class="h-5 w-5" />
                                    @endif
                                </span>

                                <div class="min-w-0 flex-1">
                                    @if ($isItemLost)
                                        <p class="text-sm font-semibold text-foreground">
                                            {{ $data['student_name'] ?? 'Siswa' }} melaporkan {{ $data['item_name'] ?? 'barang' }} hilang
                                        </p>
                                        @if (!empty($data['note']))
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Keterangan: {{ $data['note'] }}
                                            </p>
                                        @endif
                                    @else
                                        <p class="text-sm font-semibold text-foreground">
                                            {{ $data['message'] ?? 'Notifikasi' }}
                                        </p>
                                    @endif

                                    <p class="mt-1.5 text-xs text-muted-foreground">
                                        {{ $notification->created_at->locale('id')->diffForHumans() }}
                                    </p>
                                </div>

                                @if ($isUnread)
                                    <form method="POST" action="{{ route('admin.notifications.read', $notification->id) }}">
                                        @csrf
                                        <button type="submit" class="shrink-0 text-xs font-semibold text-primary hover:underline">
                                            Tandai dibaca
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="rounded-2xl border border-dashed border-border py-12 text-center text-sm text-muted-foreground">
                            Belum ada notifikasi.
                        </p>
                    @endforelse
                </div>

                <div class="mt-6">
                    {{ $notifications->links() }}
                </div>
            </div>
        </main>
    </div>
</x-layouts.dashboard>