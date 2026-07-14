@php
    $faqs = [
        [
            'question' => 'Apa itu InCase?',
            'answer' => 'InCase adalah Smart School Bag Management System yang menggunakan RFID, IoT, dan AI untuk memastikan seluruh perlengkapan sekolah siswa telah siap sebelum berangkat.',
        ],
        [
            'question' => 'Bagaimana cara memasang RFID sticker?',
            'answer' => 'Cukup tempelkan RFID sticker pada setiap barang sekolah yang ingin dipantau, lalu daftarkan barang tersebut melalui dashboard InCase.',
        ],
        [
            'question' => 'Apakah proses pemindaian cepat?',
            'answer' => 'Ya. Scanner Box memindai seluruh isi tas dalam waktu kurang dari 1 detik dan langsung menampilkan hasilnya di dashboard.',
        ],
        [
            'question' => 'Apakah InCase cocok untuk semua jenjang sekolah?',
            'answer' => 'InCase dirancang untuk siswa SMP, SMA, dan SMK, serta dapat disesuaikan dengan jadwal pelajaran masing-masing siswa.',
        ],
        [
            'question' => 'Apakah saya butuh aplikasi khusus?',
            'answer' => 'Tidak. InCase menggunakan dashboard web yang dapat diakses dari perangkat apa pun, dan notifikasi dikirim langsung ke smartphone kamu.',
        ],
    ];
@endphp

<section id="faq" class="mx-auto max-w-3xl scroll-mt-20 px-6 py-24">
    <div class="text-center">
        <span class="text-sm font-semibold uppercase tracking-wide text-primary">FAQ</span>
        <h2 class="mt-3 text-balance text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
            Pertanyaan yang sering diajukan
        </h2>
    </div>

    <div class="mt-12 flex flex-col gap-3" x-data="{ open: 0 }">
        @foreach ($faqs as $index => $faq)
            <div class="overflow-hidden rounded-[20px] border border-border bg-card shadow-sm">
                <button
                    type="button"
                    @click="open = (open === {{ $index }} ? null : {{ $index }})"
                    class="flex w-full items-center justify-between gap-4 px-6 py-5 text-left"
                    :aria-expanded="open === {{ $index }}"
                >
                    <span class="text-base font-semibold text-foreground">{{ $faq['question'] }}</span>
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-muted text-primary">
                        <x-icon.minus x-show="open === {{ $index }}" class="h-4 w-4" />
                        <x-icon.plus x-show="open !== {{ $index }}" class="h-4 w-4" />
                    </span>
                </button>
                <p x-show="open === {{ $index }}" x-cloak class="px-6 pb-5 text-sm leading-relaxed text-muted-foreground">
                    {{ $faq['answer'] }}
                </p>
            </div>
        @endforeach
    </div>
</section>
