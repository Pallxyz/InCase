<?php

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\ScanLog;
use App\Models\Subject;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\MqttClient;

class MqttListen extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Dengerin pesan RFID dari broker MQTT';

    public function handle(): void
    {
        $server   = 'broker.emqx.io';
        $port     = 1883;
        $clientId = 'incase-laravel-' . uniqid();
        $topic    = 'incase-mybook2026/rfid/scan';

        $mqtt = new MqttClient($server, $port, $clientId);
        $settings = (new ConnectionSettings)->setKeepAliveInterval(60);

        $mqtt->connect($settings, true);
        $this->info("Tersambung ke broker MQTT, dengerin topic: {$topic}");

        $mqtt->subscribe($topic, function (string $topic, string $message) {
            $this->info("Pesan masuk: {$message}");
            $this->processScan(trim($message));
        }, 0);

        $mqtt->loop(true);
    }

protected function processScan(string $rfidUid): void
{
    $item = Item::where('rfid_uid', $rfidUid)->first();

    if (! $item) {
        $this->error("❌ RFID {$rfidUid} tidak terdaftar di database.");
        return;
    }

    $this->info("✅ {$item->name} berhasil discan!");

    $student = $item->user;

    ScanLog::create([
        'user_id' => $student->id,
        'item_id' => $item->id,
        'status' => 'success',
        'scanned_at' => now(),
    ]);

    // ... sisa kode di bawah ini TETAP SAMA (cek jadwal, barang kurang, dll)

        $now = now();

        $subject = Subject::with('requiredItems')
            ->where('class_id', $student->class_id)
            ->where('day', $now->englishDayOfWeek)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $now->format('H:i'))
            ->whereTime('end_time', '>=', $now->format('H:i'))
            ->first();

        if (! $subject) {
            $this->info("{$item->name} berhasil dipindai.");
            return;
        }

        $scannedNamesToday = ScanLog::where('scan_logs.user_id', $student->id)
            ->where('scan_logs.status', 'success')
            ->whereDate('scan_logs.scanned_at', today())
            ->join('items', 'items.id', '=', 'scan_logs.item_id')
            ->pluck('items.name')
            ->map(fn (string $name) => Str::lower(trim($name)));

        $missingOriginalNames = $subject->requiredItems->pluck('name')
            ->filter(fn (string $name) => ! $scannedNamesToday->contains(Str::lower(trim($name))));

        if ($missingOriginalNames->isEmpty()) {
            $this->info("Semua barang wajib buat {$subject->name} sudah lengkap.");
            return;
        }

        $this->warn('Barang belum lengkap: ' . $missingOriginalNames->implode(', '));
    }
}