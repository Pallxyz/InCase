<?php

namespace Tests\Feature;

use App\Models\AcademicYear;
use App\Models\Holiday;
use App\Models\Item;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectRoomChange;
use App\Models\User;
use App\Notifications\RoomChangedNotification;
use App\Services\ScanService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RplDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class IncaseFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $student;      // siswa01, X RPL 1
    private SchoolClass $class; // X RPL 1

    protected function setUp(): void
    {
        parent::setUp();

        // Senin, 21 Sep 2026, 07:30 -> tepat di pelajaran pertama
        $this->travelTo(now()->setDate(2026, 9, 21)->setTime(7, 30));

        // FIELD() adalah fungsi MySQL (dipakai untuk urut hari); SQLite untuk tes belum punya.
        \Illuminate\Support\Facades\DB::connection()->getPdo()->sqliteCreateFunction(
            'FIELD',
            fn ($value, ...$list) => ($i = array_search($value, $list)) === false ? 0 : $i + 1
        );

        $this->seed(AdminSeeder::class);
        $this->seed(RplDemoSeeder::class);

        $this->admin = User::where('email', 'admin@incase.test')->firstOrFail();
        $this->student = User::where('email', 'siswa01@incase.test')->firstOrFail();
        $this->class = SchoolClass::where('name', 'X RPL 1')->firstOrFail();
    }

    private function todaySubjects()
    {
        return Subject::inActiveYear()->with('requiredItems')
            ->where('class_id', $this->class->id)->where('day', 'Monday')->orderBy('start_time')->get();
    }

    private function requiredToday(): array
    {
        return $this->todaySubjects()->flatMap(fn ($s) => $s->requiredItems->pluck('name'))->unique()->sort()->values()->all();
    }

    private function rfid(string $itemName): string
    {
        return Item::where('user_id', $this->student->id)->where('name', $itemName)->firstOrFail()->rfid_uid;
    }

    private function teacherOf(Subject $subject): User
    {
        return User::findOrFail($subject->teacher_id);
    }

    // ---------------------------------------------------------------- akses & tampilan

    public function test_halaman_utama_bisa_dibuka_oleh_tiap_peran(): void
    {
        $teacher = $this->teacherOf($this->todaySubjects()->first());

        foreach ([
            [$this->student, ['/dashboard', '/schedule', '/items', '/scan-history']],
            [$teacher, ['/dashboard', '/subjects']],
            [$this->admin, ['/academic-years', '/holidays']],
        ] as [$user, $urls]) {
            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
    }

    public function test_admin_dialihkan_dari_dashboard_dan_peran_lain_ditolak(): void
    {
        $teacher = $this->teacherOf($this->todaySubjects()->first());

        $this->actingAs($this->admin)->get('/dashboard')->assertOk();

        $this->actingAs($teacher)->get('/academic-years')->assertForbidden();
        $this->actingAs($teacher)->get('/holidays')->assertForbidden();
        $this->actingAs($this->student)->get('/academic-years')->assertForbidden();
        $this->actingAs($this->student)->get('/subjects')->assertForbidden();

        // Sejak no.4: admin JUGA boleh mengelola jadwal (semua kelas/guru), bukan cuma guru.
        $this->actingAs($this->admin)->get('/subjects')->assertOk();
    }

    // ---------------------------------------------------------------- dashboard barang wajib (no. 12)

    public function test_dashboard_hanya_menampilkan_barang_wajib_hari_ini(): void
    {
        $required = $this->requiredToday();
        $this->assertNotEmpty($required);
        $this->assertLessThan(Item::where('user_id', $this->student->id)->count(), count($required) + 1, 'barang wajib harus lebih sedikit dari semua barang, kalau tidak tes ini tidak bermakna');

        $r = $this->actingAs($this->student)->get('/dashboard')->assertOk();

        $shown = $r->viewData('items')->pluck('name')->sort()->values()->all();
        $this->assertSame($required, $shown);
        $this->assertSame(count($required), $r->viewData('totalItems'));
        $this->assertSame(0, $r->viewData('packedCount'));
    }

    public function test_scan_barang_wajib_menambah_progress_dan_barang_lain_tidak(): void
    {
        $required = $this->requiredToday();
        $notRequired = collect(\Database\Seeders\RplTimetable::allItemNames())->diff($required)->first();

        $res = app(ScanService::class)->handle($this->rfid($notRequired));
        $this->assertSame(200, $res['code']);
        $r = $this->actingAs($this->student)->get('/dashboard');
        $this->assertSame(0, $r->viewData('packedCount'), 'barang tidak wajib tidak boleh menambah progress');

        app(ScanService::class)->handle($this->rfid($required[0]));
        $r = $this->actingAs($this->student)->get('/dashboard');
        $this->assertSame(1, $r->viewData('packedCount'));
        $this->assertSame((int) round(100 / count($required)), (int) $r->viewData('progress'));
    }

    // ---------------------------------------------------------------- hari libur (no. 9)

    public function test_hari_libur_mengosongkan_dashboard_dan_scan_memberi_tahu(): void
    {
        Holiday::create(['created_by' => $this->admin->id, 'school_name' => 'SMKN 1 Cirebon', 'class_id' => null, 'date' => today(), 'name' => 'Libur Uji']);

        $r = $this->actingAs($this->student)->get('/dashboard')->assertOk();
        $this->assertSame(0, $r->viewData('totalItems'));
        $this->assertSame('Libur Uji', $r->viewData('holiday')->name);

        $res = app(ScanService::class)->handle($this->rfid('Laptop'));
        $this->assertSame('success', $res['body']['status']);
        $this->assertSame('Libur Uji', $res['body']['holiday']);
    }

    public function test_libur_kelas_lain_tidak_mempengaruhi_kelas_ini(): void
    {
        $other = SchoolClass::where('name', 'XII RPL 2')->firstOrFail();
        Holiday::create(['created_by' => $this->admin->id, 'school_name' => 'SMKN 1 Cirebon', 'class_id' => $other->id, 'date' => today(), 'name' => 'Ujian Kelas 12']);

        $r = $this->actingAs($this->student)->get('/dashboard');
        $this->assertGreaterThan(0, $r->viewData('totalItems'));
        $this->assertNull($r->viewData('holiday'));
    }

    public function test_reminder_tidak_error_dan_tidak_mengirim_saat_libur(): void
    {
        Notification::fake();
        $this->artisan('incase:send-item-reminders')->assertSuccessful();

        Notification::fake();
        Holiday::create(['created_by' => $this->admin->id, 'school_name' => 'SMKN 1 Cirebon', 'class_id' => null, 'date' => today(), 'name' => 'Libur Uji']);
        $this->artisan('incase:send-item-reminders')->assertSuccessful();
        Notification::assertNothingSent();
    }

    // ---------------------------------------------------------------- anti bentrok (langkah 2)

    private function subjectPayload(array $override = []): array
    {
        return array_merge([
            'class_id' => $this->class->id, 'name' => 'Mapel Baru', 'location' => 'R999',
            'day' => 'Monday', 'start_time' => '13:00', 'end_time' => '14:00',
        ], $override);
    }

    public function test_jadwal_bentrok_ditolak_dan_jadwal_bebas_diterima(): void
    {
        $existing = $this->todaySubjects()->first();          // X RPL 1, Senin 07:00-08:30
        $teacher = $this->teacherOf($existing);
        $before = Subject::count();

        // kelas sama, jam bertumpuk
        $this->actingAs($teacher)->post('/subjects', $this->subjectPayload(['start_time' => '08:00', 'end_time' => '09:00']))
            ->assertSessionHasErrors('class_id');

        // kelas lain, ruang yang sama (R101) di jam bertumpuk
        $other = SchoolClass::where('name', 'X RPL 2')->firstOrFail();
        $this->actingAs($teacher)->post('/subjects', $this->subjectPayload(['class_id' => $other->id, 'location' => 'r101 ', 'start_time' => '07:30', 'end_time' => '08:00']))
            ->assertSessionHasErrors('location');

        $this->assertSame($before, Subject::count());

        // jam menempel (08:30 tepat) TIDAK bentrok, ruang & jam bebas -> berhasil
        $this->actingAs($teacher)->post('/subjects', $this->subjectPayload(['start_time' => '12:00', 'end_time' => '13:00']))
            ->assertSessionHasNoErrors();
        $this->assertSame($before + 1, Subject::count());
        $this->assertNotNull(Subject::where('name', 'Mapel Baru')->first()->academic_year_id);
    }

    public function test_edit_jadwal_tanpa_perubahan_tidak_bentrok_dengan_dirinya_sendiri(): void
    {
        $s = $this->todaySubjects()->first();

        $this->actingAs($this->teacherOf($s))->put("/subjects/{$s->id}", [
            'class_id' => $s->class_id, 'name' => $s->name, 'location' => $s->location, 'day' => $s->day,
            'start_time' => substr($s->start_time, 0, 5), 'end_time' => substr($s->end_time, 0, 5), 'is_active' => 1,
        ])->assertSessionHasNoErrors();
    }

    // ---------------------------------------------------------------- tahun ajaran, salin, ekspor (no. 10, 11)

    public function test_ganti_tahun_ajaran_menyembunyikan_jadwal_lama_lalu_salin_dan_ekspor(): void
    {
        $old = AcademicYear::where('is_active', true)->firstOrFail();

        $new = $this->actingAs($this->admin)->post('/academic-years', ['year_start' => 2026, 'year_end' => 2027, 'semester' => 'genap']);
        $new->assertSessionHasNoErrors();
        $newYear = AcademicYear::where('semester', 'genap')->firstOrFail();

        $this->actingAs($this->admin)->post("/academic-years/{$newYear->id}/activate")->assertRedirect();
        $this->assertTrue($newYear->fresh()->is_active);
        $this->assertFalse($old->fresh()->is_active);

        // jadwal lama hilang dari tampilan
        $r = $this->actingAs($this->student)->get('/dashboard');
        $this->assertSame(0, $r->viewData('totalItems'));
        $this->assertSame(0, Subject::inActiveYear()->count());

        // salin: semua 90 jadwal + barang wajibnya
        $this->actingAs($this->admin)->post("/academic-years/{$newYear->id}/copy-schedules", ['source_id' => $old->id])
            ->assertSessionHas('success');
        $this->assertSame(90, Subject::where('academic_year_id', $newYear->id)->count());
        $this->assertSame(
            Subject::where('academic_year_id', $old->id)->withCount('requiredItems')->get()->sum('required_items_count'),
            Subject::where('academic_year_id', $newYear->id)->withCount('requiredItems')->get()->sum('required_items_count'),
        );

        // salin kedua kali: tidak menggandakan
        $this->actingAs($this->admin)->post("/academic-years/{$newYear->id}/copy-schedules", ['source_id' => $old->id]);
        $this->assertSame(90, Subject::where('academic_year_id', $newYear->id)->count());

        // siswa melihat jadwal lagi
        $this->assertGreaterThan(0, $this->actingAs($this->student)->get('/dashboard')->viewData('totalItems'));

        // ekspor tahun LAMA tetap bisa
        $csv = $this->actingAs($this->admin)->get("/academic-years/{$old->id}/export");
        $csv->assertOk();
        $this->assertStringContainsString('jadwal-2026-2027-ganjil.csv', $csv->headers->get('content-disposition'));
        $body = $csv->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $body);
        $this->assertCount(91, array_filter(explode("\n", $body)));   // 1 header + 90 jadwal
    }

    // ---------------------------------------------------------------- pindah ruang (no. 5)

    public function test_guru_pindah_ruang_untuk_satu_tanggal(): void
    {
        Notification::fake();
        $subject = $this->todaySubjects()->first();     // Senin 07:00, ruang R101
        $teacher = $this->teacherOf($subject);
        $url = "/subjects/{$subject->id}/room-changes";
        $today = today()->toDateString();

        // hari salah (Selasa) ditolak
        $this->actingAs($teacher)->post($url, ['date' => today()->addDay()->toDateString(), 'location' => 'Masjid'])->assertSessionHasErrors('date');
        // ruang sama dengan ruang biasa ditolak
        $this->actingAs($teacher)->post($url, ['date' => $today, 'location' => 'r101'])->assertSessionHasErrors('location');
        // ruang yang sedang dipakai kelas lain (R102) ditolak
        $this->actingAs($teacher)->post($url, ['date' => $today, 'location' => 'R102'])->assertSessionHasErrors('location');
        // guru lain tidak boleh mengubah
        $intruder = User::where('role', 'teacher')->where('id', '!=', $teacher->id)->first();
        $this->actingAs($intruder)->post($url, ['date' => $today, 'location' => 'Masjid'])->assertForbidden();
        $this->assertSame(0, SubjectRoomChange::count());

        // berhasil
        $this->actingAs($teacher)->post($url, ['date' => $today, 'location' => 'Masjid'])->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame(1, SubjectRoomChange::count());
        Notification::assertSentTo($this->student, RoomChangedNotification::class);
        Notification::assertSentToTimes($this->student, RoomChangedNotification::class, 1);

        // dashboard hari ini menampilkan Masjid, jadwal utama tidak berubah
        $r = $this->actingAs($this->student)->get('/dashboard');
        $this->assertSame('Masjid', $r->viewData('todaySubjects')->first()->location);
        $this->assertSame('R101', $subject->fresh()->location);

        // minggu depan tetap R101
        $next = Subject::where('id', $subject->id)->get();
        Subject::applyRoomChanges($next, today()->addWeek());
        $this->assertSame('R101', $next->first()->location);

        // kelas lain tidak bisa pakai Masjid di jam yang sama pada tanggal itu
        $other = Subject::inActiveYear()->where('class_id', SchoolClass::where('name', 'X RPL 2')->first()->id)
            ->where('day', 'Monday')->where('start_time', '07:00')->firstOrFail();
        $this->actingAs($this->teacherOf($other))->post("/subjects/{$other->id}/room-changes", ['date' => $today, 'location' => 'masjid'])
            ->assertSessionHasErrors('location');

        // batalkan -> kembali & siswa diberi tahu lagi
        $change = SubjectRoomChange::first();
        $this->actingAs($teacher)->delete("{$url}/{$change->id}")->assertSessionHas('success');
        $this->assertSame(0, SubjectRoomChange::count());
        Notification::assertSentToTimes($this->student, RoomChangedNotification::class, 2);
    }

    public function test_pindah_ruang_ditolak_di_hari_libur(): void
    {
        Holiday::create(['created_by' => $this->admin->id, 'school_name' => 'SMKN 1 Cirebon', 'class_id' => null, 'date' => today(), 'name' => 'Libur Uji']);
        $subject = $this->todaySubjects()->first();

        $this->actingAs($this->teacherOf($subject))
            ->post("/subjects/{$subject->id}/room-changes", ['date' => today()->toDateString(), 'location' => 'Masjid'])
            ->assertSessionHasErrors('date');
    }

    // ---------------------------------------------------------------- seeder

    public function test_seeder_idempoten_dan_datanya_lengkap(): void
    {
        $this->seed(RplDemoSeeder::class);   // kedua kali

        $this->assertSame(1, AcademicYear::count());
        $this->assertSame(6, SchoolClass::where('major', 'PPLG')->count());
        $this->assertSame(90, Subject::count());
        $this->assertSame(18, User::where('role', 'student')->count());
        $this->assertSame(180, Item::count());
    }
}