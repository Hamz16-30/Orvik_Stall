@props([
    'title' => null,
    'description' => 'ORVIK Store — The fastest, simplest SaaS stall management and POS platform for retail operators.',
    'activeNav' => 'home',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' — ' . config('app.name', 'ORVIK Store') : config('app.name', 'ORVIK Store') . ' — Fast Retail POS & Stall Management' }}</title>
    <meta name="description" content="{{ $description }}">

    <!-- Open Graph / Meta -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title ? $title . ' — ' . config('app.name', 'ORVIK Store') : config('app.name', 'ORVIK Store') }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:site_name" content="ORVIK Store">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-body antialiased flex flex-col min-h-screen">
    <!-- Marketing Navbar -->
    <x-navigation.marketing-navbar :active="$activeNav" />

    <!-- Main Content -->
    <main class="flex-1">
        {{ $slot }}
    </main>

    <!-- Global Toast Notifications -->
    <x-ui.toast />

    @stack('scripts')
</body>
</html>
