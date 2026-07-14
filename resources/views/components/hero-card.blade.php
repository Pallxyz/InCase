<div class="relative overflow-hidden rounded-[28px] p-8 shadow-[0_24px_60px_-24px_rgba(16,110,190,0.35)] sm:p-12">
    <div class="absolute inset-0 bg-gradient-to-r from-white via-[#3AA7FF] to-[#106EBE]"></div>

    <div class="pointer-events-none absolute -right-10 -top-20 h-72 w-72 rounded-full bg-white/20 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-24 right-0 h-80 w-80 rounded-full bg-[#0FFCBE]/20 blur-3xl"></div>
    <div class="pointer-events-none absolute left-10 top-6 h-40 w-40 rounded-full bg-primary/5 blur-2xl"></div>

    <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.12]" viewBox="0 0 800 400" preserveAspectRatio="none" fill="none">
        <circle cx="120" cy="60" r="120" stroke="#106EBE" stroke-width="1" />
        <circle cx="160" cy="340" r="70" stroke="#106EBE" stroke-width="1" />
        <rect x="130" y="120" width="90" height="90" rx="20" stroke="#106EBE" stroke-width="1" transform="rotate(18 175 165)" />
        <line x1="300" y1="20" x2="20" y2="300" stroke="#106EBE" stroke-width="1" />
    </svg>

    <div class="relative max-w-xl">
        <span class="inline-flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
            <x-icon.exclamation-triangle class="h-3.5 w-3.5" />
            2 Barang Belum Lengkap
        </span>

        <h2 class="mt-5 text-balance text-3xl font-bold leading-tight tracking-tight text-foreground sm:text-4xl">
            Tas Kamu Belum Siap
        </h2>
        <p class="mt-4 max-w-md text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
            Dua barang wajib masih belum terdeteksi sebelum kelas hari ini dimulai.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-full bg-primary px-6 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm transition-transform hover:-translate-y-0.5">
                <x-icon.sparkles class="h-4 w-4" />
                Mulai Pindai Pintar
            </button>
            <button type="button" class="inline-flex items-center justify-center gap-2 rounded-full border border-border bg-white/60 px-6 py-2.5 text-sm font-semibold text-foreground backdrop-blur transition-colors hover:bg-white">
                Lihat Barang Hari Ini
            </button>
        </div>
    </div>
</div>