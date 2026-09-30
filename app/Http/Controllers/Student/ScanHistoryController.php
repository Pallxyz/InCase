<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ScanLog;
use App\Services\ReturnCheckService;
use App\Services\SchoolDayResolver;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScanHistoryController extends Controller
{
    public function __construct(
        private SchoolDayResolver $days,
        private ReturnCheckService $returns,
    ) {}

    public function index(): View
    {
        $student = Auth::user();

        [$scans, $lastScan] = $this->buildScanData($student);

        $todayKey = today()->toDateString();
        $todayScans = $scans->filter(fn ($s) => $s['dateRaw'] === $todayKey);

        $totalScansToday = $todayScans->count();
        $successScansToday = $todayScans->where('status', 'success')->count();
        $missingAlertsToday = $todayScans->whereIn('status', ['missing', 'warning'])->count();
        $avgDuration = $todayScans->isNotEmpty()
            ? round($todayScans->avg('durationSeconds'), 1)
            : 0;

        return view('scan-history.index', compact(
            'scans',
            'totalScansToday',
            'successScansToday',
            'missingAlertsToday',
            'avgDuration',
            'lastScan',
        ));
    }

    public function latest(Request $request): JsonResponse
    {
        $latest = ScanLog::where('user_id', Auth::id())
            ->latest('scanned_at')
            ->first(['id', 'scanned_at']);

        return response()->json([
            'latestId' => $latest?->id,
            'latestAt' => $latest?->scanned_at?->toIso8601String(),
        ]);
    }

    /**
     * Unduh seluruh riwayat pindai siswa sebagai CSV (bisa dibuka di Excel/Google Sheets).
     * Pakai data yang sama persis dengan yang tampil di halaman, jadi tidak ada
     * perhitungan ganda yang bisa beda hasil.
     */
    public function export(): StreamedResponse
    {
        $student = Auth::user();

        [$scans] = $this->buildScanData($student);

        $statusLabels = [
            'success' => 'Berhasil',
            'missing' => 'Barang Kurang',
            'warning' => 'Peringatan',
        ];

        $filename = 'riwayat-pindai-' . Str::slug($student->name) . '-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($scans, $statusLabels) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Tanggal', 'Waktu', 'Jenis Pindai', 'Status', 'Durasi',
                'Barang Terdeteksi', 'Barang Kurang', 'Ringkasan',
            ]);

            foreach ($scans as $scan) {
                fputcsv($out, [
                    $scan['date'],
                    $scan['time'],
                    $scan['scanType'],
                    $statusLabels[$scan['status']] ?? $scan['status'],
                    $scan['duration'],
                    implode(', ', $scan['detectedItems']),
                    implode(', ', $scan['missingItems']),
                    $scan['aiSummary'],
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Bangun daftar $scans (dikelompokkan per hari+fase) dan scan log paling
     * baru. Dipakai bareng oleh index() (tampilan) dan export() (CSV), supaya
     * dua-duanya selalu menampilkan angka yang sama persis.
     *
     * @return array{0: \Illuminate\Support\Collection, 1: ScanLog|null}
     */
    private function buildScanData($student): array
    {
        $scanLogs = ScanLog::with('item')
            ->where('user_id', $student->id)
            ->whereNotNull('for_date')
            ->latest('scanned_at')
            ->get();

        $scans = $scanLogs
            ->groupBy(fn (ScanLog $log) => $log->for_date->toDateString() . '|' . ($log->phase ?: 'idle'))
            ->map(fn ($logs, string $key) => $this->summarizeGroup($student, $logs, $key))
            ->sortByDesc('timestamp')
            ->values();

        return [$scans, $scanLogs->first()];
    }

    private function summarizeGroup($student, $logs, string $key): array
    {
        $phase = explode('|', $key)[1] ?? 'idle';
        $date = $logs->first()->for_date->copy();

        $detectedItems = $logs->pluck('item.name')->filter()->unique()->values();

        [$requiredItems, $scanType] = match ($phase) {
            'packing' => [
                $this->days->subjectsOn($student, $date)
                    ->flatMap(fn ($subject) => $subject->requiredItems->pluck('name'))
                    ->map(fn ($name) => trim($name))
                    ->filter()
                    ->unique(fn ($name) => mb_strtolower($name))
                    ->values(),
                'Absen Berangkat',
            ],
            'return' => [
                $this->returns->packedItems($student, $date)->pluck('name')->values(),
                'Cek Pulang',
            ],
            default => [collect(), 'Pindai Bebas'],
        };

        $normalizedDetected = $detectedItems->map(fn ($name) => mb_strtolower(trim($name)));
        $missingItems = $requiredItems
            ->reject(fn ($name) => $normalizedDetected->contains(mb_strtolower($name)))
            ->values();

        $status = match (true) {
            $requiredItems->isEmpty() => 'success',
            $missingItems->isEmpty() => 'success',
            $detectedItems->isEmpty() => 'missing',
            default => 'warning',
        };

        $itemsTotal = $requiredItems->isEmpty() ? $detectedItems->count() : $requiredItems->count();

        $lastLog = $logs->sortByDesc('scanned_at')->first();
        $firstLog = $logs->sortBy('scanned_at')->first();
        $durationSeconds = max(0.1, $firstLog->scanned_at->diffInSeconds($lastLog->scanned_at) ?: 0.5);

        return [
            'id' => $key,
            'time' => $lastLog->scanned_at->format('H:i'),
            'date' => $date->locale('id')->translatedFormat('j M Y'),
            'dateRaw' => $date->toDateString(),
            'scanType' => $scanType,
            'status' => $status,
            'duration' => rtrim(rtrim(number_format($durationSeconds, 1), '0'), '.') . ' detik',
            'durationSeconds' => $durationSeconds,
            'itemsDetected' => $detectedItems->count(),
            'itemsTotal' => $itemsTotal,
            'detectedItems' => $detectedItems->all(),
            'missingItems' => $missingItems->all(),
            'aiSummary' => $this->summaryText($status, $phase, $date, $missingItems),
            'device' => 'ESP32-01',
            'location' => $student->box_location ?? '-',
            'signal' => '-',
            'timestamp' => $lastLog->scanned_at->timestamp,
        ];
    }

    private function summaryText(string $status, string $phase, Carbon $date, $missingItems): string
    {
        $dayLabel = match (true) {
            $date->isToday() => 'hari ini',
            $date->isTomorrow() => 'besok',
            default => 'tanggal ' . $date->format('d/m/Y'),
        };

        if ($phase === 'idle') {
            return 'Pindai tercatat. Tidak ada jadwal pelajaran pada tanggal ini, jadi barang tidak dicocokkan.';
        }

        if ($status === 'success' && $missingItems->isEmpty()) {
            return $phase === 'return'
                ? 'Semua barang yang dibawa pagi sudah kembali ke tas. Aman untuk pulang!'
                : "Semua barang wajib {$dayLabel} sudah lengkap.";
        }

        if ($status === 'missing') {
            return $phase === 'return'
                ? 'Belum ada barang yang terdeteksi kembali ke tas. Cek isi tas sebelum pulang.'
                : "Belum ada barang wajib {$dayLabel} yang terdeteksi. Cek kembali isi tas.";
        }

        return $phase === 'return'
            ? 'Barang berikut belum terdeteksi kembali ke tas: ' . $missingItems->implode(', ')
            : "Barang berikut belum dipindai untuk {$dayLabel}: " . $missingItems->implode(', ');
    }
}