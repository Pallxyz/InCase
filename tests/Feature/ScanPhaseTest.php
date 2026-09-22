<?php

namespace Tests\Feature;

use App\Models\Holiday;
use App\Models\Item;
use App\Models\ItemResolution;
use App\Models\ScanLog;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use App\Services\ReturnCheckService;
use App\Services\ScanService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\RplDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Scan berangkat / pulang + "dikumpulkan / hilang".
 * Hari uji: Senin 21 Sep 2026. Kelas X RPL 1 punya 3 pelajaran (07:00-08:30, 08:45-10:15, 10:30-12:00).
 */
class ScanPhaseTest extends TestCase
{
    use RefreshDatabase;

    private User $student;      // siswa01, X RPL 1
    private SchoolClass $class;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction(
                'FIELD',
                fn ($value, ...$list) => ($i = array_search($value, $list)) === false ? 0 : $i + 1
            );
        }

        $this->at('2026-09-21 07:00');   // Senin

        $this->seed(AdminSeeder::class);
        $this->seed(RplDemoSeeder::class);

        $this->student = User::where('email', 'siswa01@incase.test')->firstOrFail();
        $this->class = SchoolClass::where('name', 'X RPL 1')->firstOrFail();
    }

    private function at(string $datetime): void
    {
        $this->travelTo(\Carbon\Carbon::parse($datetime));
    }

    private function scan(string $itemName): array
    {
        $uid = Item::where('user_id', $this->student->id)->where('name', $itemName)->firstOrFail()->rfid_uid;

        return app(ScanService::class)->handle($uid)['body'];
    }

    /** Barang wajib hari Senin untuk X RPL 1, urut abjad. */
    private function requiredMonday(): array
    {
        return Subject::inActiveYear()->with('requiredItems')
            ->where('class_id', $this->class->id)->where('day', 'Monday')->get()
            ->flatMap(fn ($s) => $s->requiredItems->pluck('name'))->unique()->sort()->values()->all();
    }

    private function item(string $name): Item
    {
        return Item::where('user_id', $this->student->id)->where('name', $name)->firstOrFail();
    }

    /** Berangkat: scan semua barang wajib hari ini jam 06:30. */
    private function packEverything(): array
    {
        $this->at('2026-09-21 06:30');
        $required = $this->requiredMonday();

        foreach ($required as $name) {
            $last = $this->scan($name);
        }

        $this->assertSame('complete', $last['status']);

        return $required;
    }

    // ---------------------------------------------------------------- fase persiapan

    public function test_scan_pagi_adalah_persiapan_untuk_hari_ini(): void
    {
        $this->at('2026-09-21 06:30');
        $body = $this->scan($this->requiredMonday()[0]);

        $this->assertSame('packing', $body['phase']);
        $this->assertSame('2026-09-21', $body['for_date']);
        $this->assertSame('missing', $body['status']);

        $log = ScanLog::latest('id')->first();
        $this->assertSame('packing', $log->phase);
        $this->assertSame('2026-09-21', $log->for_date->toDateString());
    }

    public function test_scan_malam_dihitung_untuk_hari_sekolah_berikutnya(): void
    {
        $this->at('2026-09-21 19:00');   // Senin malam -> untuk Selasa
        $tuesday = Subject::inActiveYear()->with('requiredItems')->where('class_id', $this->class->id)->where('day', 'Tuesday')->get()
            ->flatMap(fn ($s) => $s->requiredItems->pluck('name'))->unique()->sort()->values()->all();

        foreach ($tuesday as $name) {
            $body = $this->scan($name);
        }

        $this->assertSame('packing', $body['phase']);
        $this->assertSame('2026-09-22', $body['for_date']);
        $this->assertSame('complete', $body['status']);

        // Selasa pagi, dashboard sudah menganggap barangnya siap
        $this->at('2026-09-22 06:30');
        $r = $this->actingAs($this->student)->get('/dashboard')->assertOk();
        $this->assertSame(count($tuesday), $r->viewData('packedCount'));
        $this->assertSame(count($tuesday), $r->viewData('totalItems'));
    }

    public function test_jumat_malam_menyiapkan_hari_senin_dan_libur_dilewati(): void
    {
        $this->at('2026-09-25 19:00');   // Jumat malam
        $body = $this->scan('Laptop');
        $this->assertSame('2026-09-28', $body['for_date']);   // Senin (Sabtu-Minggu tidak ada jadwal)

        // Senin 28 Sep libur -> yang dituju Selasa 29 Sep
        Holiday::create(['created_by' => User::where('role', 'admin')->first()->id, 'school_name' => 'SMKN 1 Cirebon', 'class_id' => null, 'date' => '2026-09-28', 'name' => 'Libur Uji']);
        $this->assertSame('2026-09-29', $this->scan('Laptop')['for_date']);
    }

    public function test_akhir_pekan_dan_libur_hanya_dicatat(): void
    {
        $this->at('2026-09-26 10:00');   // Sabtu siang
        $body = $this->scan('Laptop');
        $this->assertSame('success', $body['status']);
        $this->assertArrayNotHasKey('phase', $body);
        $this->assertSame(0, app(ReturnCheckService::class)->pending($this->student, today())->count());
    }

    // ---------------------------------------------------------------- fase pulang

    public function test_cek_pulang_melaporkan_barang_yang_belum_kembali(): void
    {
        $packed = $this->packEverything();
        $this->assertGreaterThanOrEqual(2, count($packed));

        $this->at('2026-09-21 12:30');   // setelah pelajaran terakhir (12:00)
        $body = $this->scan($packed[0]);

        $this->assertSame('return', $body['phase']);
        $this->assertSame('missing', $body['status']);
        $this->assertEqualsCanonicalizing(array_slice($packed, 1), $body['missing_items']->all());

        foreach (array_slice($packed, 1) as $name) {
            $body = $this->scan($name);
        }

        $this->assertSame('complete', $body['status']);
        $this->assertStringContainsString('Aman untuk pulang', $body['message']);
    }

    public function test_scan_pagi_tidak_dihitung_sebagai_barang_pulang(): void
    {
        $this->packEverything();
        $this->at('2026-09-21 12:30');

        $this->assertCount(
            count($this->requiredMonday()),
            app(ReturnCheckService::class)->pending($this->student, today()),
            'sebelum scan pulang, semua barang yang dibawa pagi dianggap belum kembali'
        );
    }

    public function test_batas_waktu_fase(): void
    {
        $packed = $this->packEverything();

        $this->at('2026-09-21 11:59');
        $this->assertSame('packing', $this->scan($packed[0])['phase']);

        $this->at('2026-09-21 12:00');
        $this->assertSame('return', $this->scan($packed[0])['phase']);

        $this->at('2026-09-21 17:59');
        $this->assertSame('return', $this->scan($packed[0])['phase']);

        $this->at('2026-09-21 18:00');
        $this->assertSame('packing', $this->scan($packed[0])['phase']);
    }

    // ---------------------------------------------------------------- dikumpulkan / hilang

    public function test_siswa_menjelaskan_barang_dikumpulkan_atau_hilang_dengan_konfirmasi(): void
    {
        $packed = $this->packEverything();
        $this->at('2026-09-21 12:30');
        $this->scan($packed[0]);                       // satu barang kembali
        $missing = $this->item($packed[1]);

        $url = "/items/{$missing->id}/resolve";

        // tanpa konfirmasi ("kamu yakin?") -> ditolak
        $this->actingAs($this->student)->post($url, ['status' => 'lost'])->assertSessionHasErrors('confirmed');
        $this->assertSame(0, ItemResolution::count());

        // status ngawur -> ditolak
        $this->actingAs($this->student)->post($url, ['status' => 'dimakan', 'confirmed' => 1])->assertSessionHasErrors('status');

        // dikonfirmasi -> tersimpan
        $this->actingAs($this->student)->post($url, ['status' => 'submitted', 'confirmed' => 1])
            ->assertSessionHasNoErrors()->assertSessionHas('success');
        $this->assertSame('submitted', ItemResolution::first()->status);

        // boleh ralat: ternyata hilang
        $this->actingAs($this->student)->post($url, ['status' => 'lost', 'confirmed' => 1])->assertSessionHasNoErrors();
        $this->assertSame(1, ItemResolution::count());
        $this->assertSame('lost', ItemResolution::first()->status);

        // yang sudah dijelaskan tidak lagi "tertinggal", tapi tetap tercatat belum kembali
        $returns = app(ReturnCheckService::class);
        $this->assertFalse($returns->pending($this->student, today())->contains('id', $missing->id));
        $this->assertTrue($returns->unreturned($this->student, today())->contains('id', $missing->id));

        // dashboard menerima datanya
        $r = $this->actingAs($this->student)->get('/dashboard')->assertOk();
        $this->assertTrue($r->viewData('returnCheckOpen'));
        $this->assertTrue($r->viewData('notReturned')->contains('id', $missing->id));
        $this->assertSame('lost', $r->viewData('resolutions')->get($missing->id)->status);
    }

    public function test_barang_yang_ternyata_ketemu_menghapus_catatan_hilang(): void
    {
        $packed = $this->packEverything();
        $this->at('2026-09-21 12:30');
        $this->scan($packed[0]);
        $lost = $this->item($packed[1]);

        $this->actingAs($this->student)->post("/items/{$lost->id}/resolve", ['status' => 'lost', 'confirmed' => 1]);
        $this->assertSame(1, ItemResolution::count());

        $this->scan($packed[1]);   // ketemu, discan lagi

        $this->assertSame(0, ItemResolution::count());
        $this->assertFalse(app(ReturnCheckService::class)->unreturned($this->student, today())->contains('id', $lost->id));
    }

    public function test_penjelasan_ditolak_kalau_belum_waktunya_atau_bukan_haknya(): void
    {
        $packed = $this->packEverything();
        $item = $this->item($packed[0]);
        $payload = ['status' => 'lost', 'confirmed' => 1];

        // pelajaran belum selesai
        $this->at('2026-09-21 10:00');
        $this->actingAs($this->student)->post("/items/{$item->id}/resolve", $payload)->assertSessionHasErrors('item');

        $this->at('2026-09-21 12:30');

        // barang siswa lain
        $other = User::where('email', 'siswa02@incase.test')->firstOrFail();
        $this->actingAs($other)->post("/items/{$item->id}/resolve", $payload)->assertForbidden();

        // barang yang tidak dibawa pagi
        $notPacked = Item::where('user_id', $this->student->id)->whereNotIn('name', $packed)->firstOrFail();
        $this->actingAs($this->student)->post("/items/{$notPacked->id}/resolve", $payload)->assertSessionHasErrors('item');

        // barang yang sudah kembali
        $this->scan($packed[0]);
        $this->actingAs($this->student)->post("/items/{$item->id}/resolve", $payload)->assertSessionHasErrors('item');

        // guru tidak punya akses
        $teacher = User::where('role', 'teacher')->first();
        $this->actingAs($teacher)->post("/items/{$item->id}/resolve", $payload)->assertForbidden();

        $this->assertSame(0, ItemResolution::count());
    }

    // ---------------------------------------------------------------- data lama

    public function test_dashboard_tetap_normal_sebelum_dan_sesudah_jam_pulang(): void
    {
        $this->packEverything();

        $this->at('2026-09-21 09:00');
        $r = $this->actingAs($this->student)->get('/dashboard')->assertOk();
        $this->assertFalse($r->viewData('returnCheckOpen'));
        $this->assertCount(0, $r->viewData('notReturned'));
        $this->assertSame($r->viewData('totalItems'), $r->viewData('packedCount'));

        // scan cek pulang tidak boleh mengubah progres persiapan
        $this->at('2026-09-21 12:30');
        $r = $this->actingAs($this->student)->get('/dashboard')->assertOk();
        $this->assertTrue($r->viewData('returnCheckOpen'));
        $this->assertSame($r->viewData('totalItems'), $r->viewData('packedCount'));
        $this->assertCount($r->viewData('totalItems'), $r->viewData('notReturned'));
    }
}