<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectRequiredItem;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Jadwal ASLI SMKN 1 Cirebon (TP 2025/2026, berlaku mulai 14 Juli 2025)
 * untuk kelas XI RPL 1 dan XI RPL 2.
 *
 * Satu baris jadwal = satu blok pelajaran berurutan (mis. MKK jam ke-2 sampai ke-5).
 * Jam mulai = mulai jam pertama blok, jam selesai = selesai jam terakhir blok.
 * Upacara Bendera (Senin) dan Kegiatan Pembiasaan (Jumat) tidak dimasukkan
 * karena bukan mata pelajaran (tidak ada guru dan tidak ada barang wajib).
 *
 * Syarat: AcademicYearSeeder, SchoolClassSeeder, dan TeacherSeeder sudah jalan.
 * Aman dijalankan ulang: jadwal lama kedua kelas di tahun ajaran aktif dihapus dulu.
 */
class SubjectSeeder extends Seeder
{
    /** Jam pelajaran: nomor jam => [mulai, selesai]. Istirahat 08.45-09.15 dan 11.30-12.30. */
    private const JAM = [
        1 => ['06:30', '07:15'],
        2 => ['07:15', '08:00'],
        3 => ['08:00', '08:45'],
        4 => ['09:15', '10:00'],
        5 => ['10:00', '10:45'],
        6 => ['10:45', '11:30'],
        7 => ['12:30', '13:15'],
        8 => ['13:15', '14:00'],
        9 => ['14:00', '14:45'],
        10 => ['14:45', '15:15'],
        11 => ['15:15', '15:45'],
        12 => ['15:45', '16:15'],
    ];

    /**
     * Nama singkat di jadwal sekolah => nama lengkap di TeacherSeeder.
     * "Dwi Putri" dipasangkan ke Vihantika Rachma Fitri berdasarkan eliminasi
     * (belum dikonfirmasi) -- ganti di sini kalau salah.
     */
    private const GURU = [
        'Eri' => 'Eri Rumsari',
        'Dudung' => 'Dudung Zulkipli',
        'Dwi Putri' => 'Vihantika Rachma Fitri',
        'Afika' => 'Afika Awwaliyah Rozzaq',
        'Zaenal' => 'Zaenal Abidin',
        'Dedi' => 'Dedi Supriyadi',
        'Rizal' => 'Rizal Murtiyono',
        'Linde' => 'Insulinde Yuliyati',
        'Pipit' => 'Pipit Komariah',
        'Rudi' => 'Rudi Hermanto',
        'Sri' => 'Sri Prihantoro',
        'Ronny' => 'Syahrul Ronny',
        'Bambang' => 'Bambang Tri Setiadi',
    ];

    /** Ruang untuk pelajaran teori (jadwal sekolah tidak menyebut kode ruangnya). */
    private const RUANG_KELAS = 'Ruang Kelas';

    /** Barang wajib per mata pelajaran. */
    private function barang(): array
    {
        $buku = fn (string $n) => ["Buku Paket {$n}", "Buku Tulis {$n}"];
        $praktik = ['Laptop', 'Charger Laptop'];

        return [
            'MKK' => $praktik,
            'PKK' => $praktik,
            'MPP' => $praktik,
            'Desain Grafis' => $praktik,
            'Matematika' => $buku('Matematika'),
            'Bahasa Indonesia' => $buku('Bahasa Indonesia'),
            'Bahasa Inggris' => $buku('Bahasa Inggris'),
            'Sejarah Indonesia' => $buku('Sejarah Indonesia'),
            'PAI & BP' => $buku('PAI'),
            'Pendidikan Pancasila' => $buku('Pendidikan Pancasila'),
            'BK' => ['Buku Tulis BK'],
            'PJOK' => [], // baju olahraga tidak dipantau (stiker rawan luntur)
        ];
    }

    /** [mapel, guru, ruang (null = ruang kelas), jam ke- awal, jam ke- akhir] */
    private function jadwal(): array
    {
        return [
            'XI RPL 1' => [
                'Monday' => [
                    ['MKK', 'Dwi Putri', 'Lab 2', 2, 5],
                    ['PAI & BP', 'Dedi', null, 6, 8],
                    ['Bahasa Indonesia', 'Linde', null, 9, 11],
                ],
                'Tuesday' => [
                    ['MKK', 'Dwi Putri', 'Lab 2', 1, 5],
                    ['PKK', 'Dudung', 'Lab 2', 6, 10],
                ],
                'Wednesday' => [
                    ['MKK', 'Dwi Putri', 'Lab 2', 1, 4],
                    ['Desain Grafis', 'Rizal', 'Lab 2', 5, 6],
                    ['Matematika', 'Zaenal', null, 7, 9],
                    ['Sejarah Indonesia', 'Pipit', null, 10, 11],
                ],
                'Thursday' => [
                    ['MPP', 'Bambang', 'Lab 1', 1, 4],
                    ['PJOK', 'Ronny', null, 5, 6],
                    ['Bahasa Inggris', 'Eri', null, 7, 10],
                ],
                'Friday' => [
                    ['MKK', 'Dwi Putri', 'Lab 2', 2, 6],
                    ['BK', 'Sri', null, 7, 7],
                    ['Pendidikan Pancasila', 'Rudi', null, 8, 9],
                ],
            ],

            'XI RPL 2' => [
                'Monday' => [
                    ['MKK', 'Afika', 'Lab 1', 2, 5],
                    ['Matematika', 'Zaenal', null, 6, 8],
                    ['PAI & BP', 'Dedi', null, 9, 11],
                ],
                'Tuesday' => [
                    ['Bahasa Inggris', 'Eri', null, 1, 4],
                    ['MKK', 'Afika', 'Lab 1', 5, 9],
                    ['Sejarah Indonesia', 'Pipit', null, 10, 11],
                ],
                'Wednesday' => [
                    ['MPP', 'Bambang', 'Lab 1', 1, 4],
                    ['PJOK', 'Ronny', null, 5, 6],
                    ['Desain Grafis', 'Rizal', 'Lab 2', 7, 8],
                    ['Bahasa Indonesia', 'Linde', null, 9, 11],
                ],
                'Thursday' => [
                    ['PKK', 'Dudung', 'Lab 2', 1, 5],
                    ['MKK', 'Afika', 'Lab 1', 6, 9],
                    ['Pendidikan Pancasila', 'Rudi', null, 10, 11],
                ],
                'Friday' => [
                    ['BK', 'Sri', null, 2, 2],
                    ['MKK', 'Afika', 'Lab 1', 3, 7],
                ],
            ],
        ];
    }

    public function run(): void
    {
        $year = AcademicYear::where('is_active', true)->first();

        if (! $year) {
            $this->command?->warn('Tidak ada tahun ajaran aktif. Jalankan AcademicYearSeeder dulu.');

            return;
        }

        $jadwal = $this->jadwal();
        $barang = $this->barang();

        $classes = SchoolClass::whereIn('name', array_keys($jadwal))->get()->keyBy('name');
        $teachers = User::where('role', 'teacher')->pluck('id', 'name');

        foreach (array_keys($jadwal) as $className) {
            if (! $classes->has($className)) {
                throw new \RuntimeException("Kelas '{$className}' tidak ditemukan. Jalankan SchoolClassSeeder dulu.");
            }
        }

        // Bersihkan jadwal lama kedua kelas ini di tahun ajaran aktif.
        $oldIds = Subject::where('academic_year_id', $year->id)
            ->whereIn('class_id', $classes->pluck('id'))
            ->pluck('id');

        SubjectRequiredItem::whereIn('subject_id', $oldIds)->delete();
        Subject::withoutEvents(fn () => Subject::whereIn('id', $oldIds)->delete());

        Subject::withoutEvents(function () use ($jadwal, $barang, $classes, $teachers, $year) {
            foreach ($jadwal as $className => $days) {
                foreach ($days as $day => $blocks) {
                    foreach ($blocks as [$mapel, $guru, $ruang, $dari, $sampai]) {
                        $namaGuru = self::GURU[$guru] ?? $guru;

                        if (! $teachers->has($namaGuru)) {
                            throw new \RuntimeException("Guru '{$namaGuru}' tidak ditemukan. Jalankan TeacherSeeder dulu.");
                        }

                        $subject = Subject::create([
                            'academic_year_id' => $year->id,
                            'class_id' => $classes[$className]->id,
                            'teacher_id' => $teachers[$namaGuru],
                            'name' => $mapel,
                            'location' => $ruang ?? self::RUANG_KELAS,
                            'day' => $day,
                            'start_time' => self::JAM[$dari][0],
                            'end_time' => self::JAM[$sampai][1],
                            'homework' => null,
                            'has_exam' => false,
                            'is_active' => true,
                        ]);

                        foreach ($barang[$mapel] as $item) {
                            $subject->requiredItems()->create(['name' => $item]);
                        }
                    }
                }
            }
        });
    }
}