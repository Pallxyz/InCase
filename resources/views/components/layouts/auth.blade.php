<!DOCTYPE html>
<html lang="id" class="bg-background">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#ffffff">
    <title>{{ $title ?? 'InCase' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background font-sans text-foreground antialiased lg:h-screen lg:overflow-hidden">
    <div class="grid min-h-screen lg:h-screen lg:grid-cols-2">
        <x-auth-illustration />
        <div class="flex min-h-screen items-center justify-center px-6 py-12 sm:px-12 lg:h-screen lg:overflow-y-auto">
            {{ $slot }}
        </div>
    </div>
</body>
</html>