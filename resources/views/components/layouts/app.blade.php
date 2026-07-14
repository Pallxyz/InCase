<!DOCTYPE html>
<html lang="id" class="bg-background">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">

    <title>{{ $title ?? 'InCase — Pastikan Semua Barangmu Siap Sebelum Berangkat' }}</title>
    <meta name="description" content="InCase adalah Smart School Bag Management System berbasis RFID, IoT, dan AI yang membantu siswa memastikan seluruh perlengkapan sekolah telah siap sebelum berangkat.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased">
    {{ $slot }}
</body>
</html>