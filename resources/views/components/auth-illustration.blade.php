<div class="relative hidden h-full lg:block">
    {{-- Foto: pensil warna-warni — Unsplash (Jess Bailey), free license (unsplash.com/license) --}}
    <img
        src="https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?q=80&w=1600&auto=format&fit=crop"
        alt="Colorful school pencils"
        class="absolute inset-0 h-full w-full object-cover"
    >

    {{-- Gelapin fotonya biar teks kebaca --}}
    <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-black/60"></div>

    {{-- Logo, pojok kiri atas --}}
    <div class="absolute left-0 top-0 flex items-center gap-3 p-12">
        <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/10 backdrop-blur-md">
            <x-icon.viewfinder-circle class="h-5 w-5 text-secondary" />
        </span>
        <span class="text-lg font-bold tracking-tight text-white">InCase</span>
    </div>

    {{-- Tagline, nempel di bawah --}}
    <div class="absolute inset-x-0 bottom-0 p-12">
        <div class="border-l-4 border-secondary pl-6">
            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-secondary/90">Never Miss an Item</span>
            <h2 class="mt-3 text-balance text-3xl font-bold leading-snug tracking-tight text-white">
                Never forget a school item again.
            </h2>
            <p class="mt-3 max-w-sm text-pretty text-sm leading-relaxed text-white/70">
                RFID, IoT, dan AI bekerja sama memastikan tasmu selalu lengkap sebelum berangkat.
            </p>
        </div>
    </div>
</div>