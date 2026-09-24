<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jadwal {{ $schoolClass->name }}</title>
    <style>
        @page { size: A4 landscape; margin: 10px 14px; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #1e293b;
            font-size: 8px;
            line-height: 1.2;
        }

        /* ============ BORDER HALAMAN ============ */
        .page-wrapper {
            border: 2px solid #1e40af;
            border-radius: 4px;
            padding: 12px 16px;
        }

        /* ============ HEADER ============ */
        .header {
            border-bottom: 2px solid #1e40af;
            padding-bottom: 6px;
            margin-bottom: 8px;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .header-table td { border: none; padding: 0; vertical-align: middle; }

        .header-left { width: 25%; }
        .header-left .school-name {
            font-size: 9px;
            font-weight: bold;
            color: #1e40af;
        }
        .header-left .school-sub {
            font-size: 6.5px;
            color: #64748b;
            margin-top: 1px;
        }

        .header-title { text-align: center; width: 50%; }
        .header-title h1 {
            font-size: 13px;
            font-weight: bold;
            color: #1e40af;
            letter-spacing: 0.4px;
        }
        .header-title h2 {
            font-size: 9px;
            font-weight: normal;
            color: #475569;
            margin-top: 1px;
        }

        .header-meta {
            width: 25%;
            text-align: right;
            font-size: 7.5px;
            color: #475569;
            line-height: 1.4;
        }
        .header-meta strong { color: #1e293b; }

        /* ============ INFO BAR ============ */
        .info-bar {
            margin-bottom: 6px;
            background: #eff6ff;
            border-left: 3px solid #1e40af;
            padding: 4px 8px;
            font-size: 8px;
            color: #1e293b;
        }

        /* ============ SCHEDULE ============ */
        .schedule {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .schedule th,
        .schedule td {
            border: 1px solid #cbd5e1;
            padding: 3px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .schedule thead th {
            background: #1e40af;
            color: #fff;
            font-size: 8px;
            font-weight: bold;
            padding: 5px 2px;
        }
        .schedule thead th.time-col {
            width: 72px;
            background: #1e3a8a;
        }
        .schedule td.time-cell {
            background: #f1f5f9;
            width: 72px;
            font-size: 7.5px;
            font-weight: bold;
            color: #1e40af;
        }
        .schedule td.subject {
            font-size: 7.5px;
            vertical-align: middle;
        }
        .schedule td.subject .mapel {
            display: block;
            font-weight: bold;
            color: #0f172a;
            font-size: 8px;
        }
        .schedule td.subject .guru {
            display: block;
            font-size: 6.5px;
            color: #64748b;
            margin-top: 1px;
        }
        .schedule td.subject .ruang {
            display: block;
            font-size: 6px;
            color: #94a3b8;
            margin-top: 1px;
            font-style: italic;
        }
        .schedule td.subject.empty {
            background: #fafafa;
            color: #cbd5e1;
            font-style: italic;
        }

        /* ============ ISTIRAHAT ============ */
        .schedule tr.break-row td {
            background: #fef3c7;
            color: #92400e;
            font-weight: bold;
            font-size: 8px;
            letter-spacing: 0.6px;
            padding: 4px;
            border-top: 1.5px dashed #f59e0b;
            border-bottom: 1.5px dashed #f59e0b;
        }

        /* ============ FOOTER ============ */
        .footer {
            margin-top: 6px;
            font-size: 6.5px;
            color: #94a3b8;
            text-align: right;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>

<body>

<div class="page-wrapper">

    {{-- ============ HEADER ============ --}}
    <div class="header">
        <table class="header-table">
            <tr>
                {{-- KIRI: Identitas Sekolah --}}
                <td class="header-left">
                    <div class="school-name">{{ $schoolName }}</div>
                    <div class="school-sub">Sistem Jadwal Pelajaran</div>
                </td>

                {{-- TENGAH: Judul --}}
                <td class="header-title">
                    <h1>JADWAL PELAJARAN</h1>
                    <h2>
                        Kelas {{ $schoolClass->name }}
                        @if ($schoolClass->major) — {{ $schoolClass->major }} @endif
                        — T.A. {{ $academicYear->name ?? '-' }}
                    </h2>
                </td>

                {{-- KANAN: Meta --}}
                <td class="header-meta">
                    <div><span>Dicetak:</span> <strong>{{ $printedAt }}</strong></div>
                    <div><span>Semester:</span> <strong>{{ ucfirst($academicYear->semester ?? '-') }}</strong></div>
                    <div><span>T.A.:</span> <strong>{{ $academicYear->name ?? '-' }}</strong></div>
                </td>
            </tr>
        </table>
    </div>

    {{-- ============ INFO BAR ============ --}}
    <div class="info-bar">
        <strong>Kelas:</strong> {{ $schoolClass->name }} &nbsp;|&nbsp;
        <strong>Jurusan:</strong> {{ $schoolClass->major ?? '-' }} &nbsp;|&nbsp;
        <strong>Sekolah:</strong> {{ $schoolName }} &nbsp;|&nbsp;
        <strong>Tahun Ajaran:</strong> {{ $academicYear->name ?? '-' }}
    </div>

    @php
        /* =========================================================
         * 1. HELPER
         * ========================================================= */
        $toMin = function ($time) {
            [$h, $m] = explode(':', substr($time, 0, 5));
            return ((int) $h) * 60 + (int) $m;
        };
        $fmt = fn($min) => sprintf('%02d:%02d', intdiv($min, 60), $min % 60);

        /* =========================================================
         * 2. ISTIRAHAT (HARDCODE)
         * ========================================================= */
        $breakSlots = [
            ['start' => '08:45', 'end' => '09:15', 'label' => 'ISTIRAHAT'],
            ['start' => '11:30', 'end' => '12:30', 'label' => 'ISHOMA'],
        ];
        $breakRanges = array_map(fn($b) => [
            'start' => $toMin($b['start']),
            'end'   => $toMin($b['end']),
            'label' => $b['label'],
        ], $breakSlots);

        /* =========================================================
         * 3. PECAH MAPEL YANG MELINTASI ISTIRAHAT
         * ========================================================= */
        $flattened = [];
        foreach ($schedule as $day => $items) {
            foreach ($items as $item) {
                $curStart = $toMin($item['start']);
                $curEnd   = $toMin($item['end']);
                $isCont   = false;

                foreach ($breakRanges as $br) {
                    if ($br['start'] > $curStart && $br['end'] < $curEnd) {
                        $flattened[] = [
                            'day'             => $day,
                            'start'           => $curStart,
                            'end'             => $br['start'],
                            'subject'         => $item['subject'],
                            'is_continuation' => false,
                        ];
                        $curStart = $br['end'];
                        $isCont   = true;
                    }
                }
                $flattened[] = [
                    'day'             => $day,
                    'start'           => $curStart,
                    'end'             => $curEnd,
                    'subject'         => $item['subject'],
                    'is_continuation' => $isCont,
                ];
            }
        }

        /* =========================================================
         * 4. TIMELINE
         * ========================================================= */
        $points = [];
        foreach ($flattened as $f) {
            $points[] = $f['start'];
            $points[] = $f['end'];
        }
        foreach ($breakRanges as $br) {
            $points[] = $br['start'];
            $points[] = $br['end'];
        }
        $points = array_values(array_unique($points));
        sort($points);

        $rows = [];
        for ($i = 0; $i < count($points) - 1; $i++) {
            $rows[] = ['startMin' => $points[$i], 'endMin' => $points[$i + 1]];
        }

        /* =========================================================
         * 5. TANDAI BARIS ISTIRAHAT
         * ========================================================= */
        $breakRowIndex = [];
        foreach ($breakRanges as $br) {
            for ($r = 0; $r < count($rows); $r++) {
                if ($rows[$r]['startMin'] === $br['start'] && $rows[$r]['endMin'] === $br['end']) {
                    $breakRowIndex[$r] = $br['label'];
                    break;
                }
            }
        }

        /* =========================================================
         * 6. HARI AKTIF
         * ========================================================= */
        $activeDays = [];
        foreach ($dayLabels as $dayKey => $dayName) {
            if (!empty($schedule[$dayKey])) {
                $activeDays[$dayKey] = $dayName;
            }
        }

        /* =========================================================
         * 7. ISI GRID + ROWSPAN
         * ========================================================= */
        $grid = [];
        $skip = [];
        foreach ($activeDays as $dayKey => $dayName) {
            for ($r = 0; $r < count($rows); $r++) {
                $grid[$dayKey][$r] = null;
                $skip[$dayKey][$r] = false;
            }
        }

        foreach ($flattened as $f) {
            $dayKey   = $f['day'];
            $startMin = $f['start'];
            $endMin   = $f['end'];

            $startRow = null;
            $endRow   = null;
            for ($r = 0; $r < count($rows); $r++) {
                if ($rows[$r]['startMin'] === $startMin) $startRow = $r;
                if ($rows[$r]['endMin'] === $endMin)     $endRow   = $r;
            }
            if ($startRow === null || $endRow === null) continue;

            $span = $endRow - $startRow + 1;

            $grid[$dayKey][$startRow] = [
                'subject'         => $f['subject'],
                'rowspan'         => $span,
                'is_continuation' => $f['is_continuation'],
            ];
            for ($r = $startRow + 1; $r <= $endRow; $r++) {
                $skip[$dayKey][$r] = true;
            }
        }
    @endphp

    {{-- ============ TABEL JADWAL ============ --}}
    <table class="schedule">
        <thead>
            <tr>
                <th class="time-col">WAKTU</th>
                @foreach ($activeDays as $dayKey => $dayName)
                    <th>{{ $dayName }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r => $row)
                @if (isset($breakRowIndex[$r]))
                    {{-- Baris Istirahat --}}
                    <tr class="break-row">
                        <td colspan="{{ count($activeDays) + 1 }}">
                            ☕ {{ $breakRowIndex[$r] }} &nbsp;·&nbsp;
                            {{ $fmt($row['startMin']) }} – {{ $fmt($row['endMin']) }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td class="time-cell">
                            {{ $fmt($row['startMin']) }} – {{ $fmt($row['endMin']) }}
                        </td>
                        @foreach ($activeDays as $dayKey => $dayName)
                            @php
                                $cell   = $grid[$dayKey][$r] ?? null;
                                $isSkip = $skip[$dayKey][$r] ?? false;
                            @endphp
                            @if ($isSkip)
                                {{-- skip karena rowspan mapel di atasnya --}}
                            @elseif ($cell)
                                <td class="subject" rowspan="{{ $cell['rowspan'] }}">
                                    @if (empty($cell['is_continuation']))
                                        <span class="mapel">
                                            {{ $cell['subject']->subject_name ?? $cell['subject']->name ?? '-' }}
                                        </span>
                                        <span class="guru">
                                            {{ $cell['subject']->teacher->name ?? '-' }}
                                        </span>
                                        @if (!empty($cell['subject']->room))
                                            <span class="ruang">{{ $cell['subject']->room }}</span>
                                        @endif
                                    @endif
                                </td>
                            @else
                                <td class="subject empty">—</td>
                            @endif
                        @endforeach
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    {{-- ============ FOOTER ============ --}}
    <div class="footer">
        Jadwal dapat berubah sewaktu-waktu · Dicetak otomatis oleh sistem
    </div>

</div>

</body>
</html>