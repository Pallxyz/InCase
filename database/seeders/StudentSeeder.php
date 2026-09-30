<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Siswa kelas XI RPL 2, SMKN 1 Cirebon (sumber: daftar absen).
 * Aman dijalankan berulang (updateOrCreate per email).
 *
 * Login demo: {nama}@incase.test, password "password".
 * Contoh: amelia@incase.test, jihan.riesty@incase.test.
 *
 * Harus dipanggil SETELAH SchoolClassSeeder.
 * Puji Wijayanto (12430143) sudah keluar sekolah, tidak dimasukkan.
 */
class StudentSeeder extends Seeder
{
    private const SCHOOL = 'SMKN 1 Cirebon';

    private const CLASS_NAME = 'XI RPL 2';

    /** [NIS, NAMA, bagian depan email] */
    private const STUDENTS = [
        ['12430117', 'ABDUL MUGHNI NUGRAHA', 'abdul.mughni'],
        ['12430118', 'AHMAD FATHAN ARROYYAN', 'ahmad.fathan'],
        ['12430119', 'AMELIA AGUSTIN', 'amelia'],
        ['12430120', 'AMMAR NUR FAISHOL', 'ammar'],
        ['12430121', 'ARYA MILITO', 'arya'],
        ['12430122', 'ATHALLAH ASYARIF KHOIRULINSAN', 'athallah'],
        ['12430123', 'CHIARA DEWI CHATLINA', 'chiara'],
        ['12430124', 'DIYANA PUTRI RAMADAN', 'diyana'],
        ['12430125', 'FATHAN APRIAN', 'fathan'],
        ['12430126', 'FERDY PRATAMA SURADI', 'ferdy'],
        ['12430127', 'INDAH NURAISYAH', 'indah'],
        ['12430128', 'JIHAN RIESTY APRILIA', 'jihan.riesty'],
        ['12430129', 'JIHAN SYAHIRA', 'jihan.syahira'],
        ['12430130', 'KAYNDRA NUR FAIQ', 'kayndra'],
        ['12430131', 'MELANI DETIANI', 'melani'],
        ['12430132', 'MELINA DETIANA', 'melina'],
        ['12430133', 'MOCHAMAD BINTANG LAKSAMANA SUMARDI', 'bintang'],
        ['12430134', 'MOHAMMAD RIDHO OKTOBERYL NUGRAHA', 'ridho'],
        ['12430135', 'MUHAIMIN', 'muhaimin'],
        ['12430136', 'NAFISAH ADELIA PUTRI', 'nafisah'],
        ['12430137', 'NINO ADITYO NUGROHO', 'nino'],
        ['12430138', 'NOVAL MAULANA', 'noval'],
        ['12430139', 'NOVVALINO PUTRA GIANTO', 'novvalino'],
        ['12430140', 'NUR FAJRINA RAMADANI', 'fajrina'],
        ['12430141', 'OKTA PUTRI SYLLAWATI HASSAN', 'okta'],
        ['12430142', 'PRIMA AL RASYID IRAWAN', 'prima'],
        ['12430144', 'RADHITYA RIZKI RAMADHAN', 'radhitya'],
        ['12430145', 'SAEFUDIN PUTRA MAGHFIROHTI', 'saefudin'],
        ['12430146', 'SARIF HIDAYAT', 'sarif'],
        ['12430147', 'SINDI MAULIDIYA', 'sindi'],
        ['12430148', 'SYAFA INESYA', 'syafa'],
        ['12430149', 'SYILLA MULYA RAMADHANI', 'syilla'],
        ['12430150', 'TIARA AFPACILA', 'tiara'],
        ['12430151', 'VETRI PUTRI RANTIKA', 'vetri'],
    ];

    public function run(): void
    {
        $class = SchoolClass::where('name', self::CLASS_NAME)->first()
            ?? SchoolClass::create([
                'name'        => self::CLASS_NAME,
                'major'       => 'PPLG',
                'grade'       => 'XI',
                'school_name' => self::SCHOOL,
            ]);

        foreach (self::STUDENTS as [$nis, $name, $emailName]) {
            User::updateOrCreate(
                ['email' => "{$emailName}@incase.test"],
                [
                    'name'        => mb_convert_case($name, MB_CASE_TITLE, 'UTF-8'),
                    'password'    => Hash::make('password'),
                    'role'        => 'student',
                    'class_id'    => $class->id,
                    'school_name' => self::SCHOOL,
                    'student_id'  => $nis,
                ],
            );
        }
    }
}