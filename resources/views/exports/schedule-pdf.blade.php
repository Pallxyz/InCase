<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Jadwal Pelajaran</title>
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            color: #111;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 13px;
            margin: 0;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .header h2 {
            font-size: 11px;
            margin: 3px 0 0;
            font-weight: bold;
        }

        .header p {
            font-size: 9px;
            margin: 2px 0 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #333;
            padding: 3px 4px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: #eee;
            font-weight: bold;
            font-size: 9px;
        }

        td.day-label {
            font-weight: bold;
            font-size: 9px;
            white-space: nowrap;
        }

        td.period-col {
            width: 22px;
        }

        td.time-col {
            width: 52px;
            white-space: nowrap;
            font-size: 8px;
        }

        td.subject-cell {
            text-align: left;
            font-size: 8px;
            white-space: pre-line;
        }

        td.empty-day {
            font-style: italic;
            color: #666;
        }

        .footer {
            margin-top: 24px;
            width: 100%;
        }

        .footer .signature {
            float: right;
            text-align: center;
            width: 220px;
            font-size: 10px;
        }

        .footer .signature .name {
            margin-top: 45px;
            font-weight: bold;
            text-decoration: underline;
        }

        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>{{ strtoupper($schoolName) }}</h1>
        <h2>JADWAL PELAJARAN{{ $activeYear ? ' — ' . strtoupper($activeYear->label) : '' }}</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 20px;">HARI</th>
                <th rowspan="2" style="width: 22px;">JAM<br>KE</th>
                <th rowspan="2" style="width: 52px;">WAKTU</th>
                @foreach ($classesByGrade as $grade => $classesInGrade)
                    <th colspan="{{ $classesInGrade->count() }}">{{ $grade }}</th>
                @endforeach
            </tr>
            <tr>
                @foreach ($classesByGrade as $classesInGrade)
                    @foreach ($classesInGrade as $class)
                        <th>{{ $class->name }}</th>
                    @endforeach
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($scheduleRows as $day)
                @if (count($day['rows']) === 0)
                    <tr>
                        <td class="day-label">{{ $day['label'] }}</td>
                        <td class="empty-day" colspan="{{ 2 + $totalColumns }}">Tidak ada jadwal</td>
                    </tr>
                @else
                    @foreach ($day['rows'] as $i => $row)
                        <tr>
                            @if ($i === 0)
                                <td class="day-label" rowspan="{{ count($day['rows']) }}">{{ $day['label'] }}</td>
                            @endif
                            <td class="period-col">{{ $row['period'] }}</td>
                            <td class="time-col">{{ $row['start'] }}–{{ $row['end'] }}</td>
                            @foreach ($classesByGrade as $classesInGrade)
                                @foreach ($classesInGrade as $class)
                                    <td class="subject-cell">{{ $row['cells'][$class->id] ?: '-' }}</td>
                                @endforeach
                            @endforeach
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer clearfix">
        <div class="signature">
            {{-- TODO: ganti "Kota" dengan nama kota asli sekolah kalau ada datanya --}}
            <p>Kota, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Mengetahui,<br>Kepala Sekolah</p>
            <p class="name">{{ $principalName ?? '(...........................)' }}</p>
        </div>
    </div>

</body>
</html>