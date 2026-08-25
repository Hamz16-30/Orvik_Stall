@props([
    'title' => 'Dashboard',
    'subtitle' => null,
    'activeNav' => 'dashboard',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — {{ config('app.name', 'ORVIK Store') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-secondary/30 text-foreground font-body antialiased flex min-h-screen">
    <!-- Desktop Sidebar -->
    <x-navigation.app-sidebar :active="$activeNav" />

    <!-- Main Application Column -->
    <div class="flex-1 flex flex-col min-w-0 pb-20 lg:pb-8">
        <!-- Top App Header -->
        <x-navigation.app-header :title="$title" :subtitle="$subtitle" />

        <!-- Main Content Area -->
        <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto">
            {{ $slot }}
        </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <x-navigation.mobile-nav :active="$activeNav" />

    <!-- Global Toast Notifications -->
    <x-ui.toast />

    @stack('scripts')
</body>
</html>
