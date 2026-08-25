@props([
    'title' => 'Platform Administration',
    'subtitle' => null,
    'activeNav' => 'dashboard',
])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} — ORVIK Platform Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Scripts and Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-secondary/30 text-foreground font-body antialiased flex min-h-screen">
    <!-- Platform Sidebar -->
    <aside class="hidden lg:flex flex-col w-64 bg-slate-900 text-slate-100 min-h-screen shrink-0 select-none border-r border-slate-800">
        <!-- Brand Header -->
        <div class="h-16 px-6 flex items-center justify-between border-b border-slate-800">
            <div class="flex items-center gap-2">
                <span class="text-accent text-lg">✦</span>
                <span class="font-bold text-white tracking-tight">ORVIK <span class="text-xs px-1.5 py-0.5 rounded bg-accent/20 text-accent font-mono font-semibold ml-1">PLATFORM</span></span>
            </div>
        </div>

        <!-- Navigation Menu -->
        <div class="flex-1 py-6 px-4 space-y-1.5 overflow-y-auto">
            <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                SaaS Operations
            </div>

            <a href="{{ route('platform.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'dashboard' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>SaaS Dashboard</span>
            </a>

            <a href="{{ route('platform.businesses') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'businesses' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="building-2" class="w-4 h-4"></i>
                <span>Businesses</span>
            </a>

            <a href="{{ route('platform.plans.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'plans' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="credit-card" class="w-4 h-4"></i>
                <span>Plans & Limits</span>
            </a>

            <a href="{{ route('platform.features.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'features' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="toggle-right" class="w-4 h-4"></i>
                <span>Feature Flags</span>
            </a>

            <a href="{{ route('platform.analytics') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'analytics' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="trending-up" class="w-4 h-4"></i>
                <span>SaaS Analytics</span>
            </a>

            <div class="pt-4 px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400">
                System Governance
            </div>

            <a href="{{ route('platform.settings') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'settings' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="settings" class="w-4 h-4"></i>
                <span>Platform Settings</span>
            </a>

            <a href="{{ route('platform.audit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $activeNav === 'audit' ? 'bg-accent text-white shadow-sm' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <i data-lucide="shield-check" class="w-4 h-4"></i>
                <span>SaaS Audit Trail</span>
            </a>
        </div>

        <!-- Platform User Footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-950/40">
            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-800/80 border border-slate-700/60">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-7 h-7 rounded-full bg-accent text-white flex items-center justify-center text-xs font-bold shrink-0">
                        PA
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-slate-400">Platform Admin</p>
                    </div>
                </div>
                <a href="{{ route('app.dashboard') }}" title="Go to Stall App" class="p-1.5 text-slate-400 hover:text-white hover:bg-slate-700 rounded-lg transition-colors">
                    <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content Column -->
    <div class="flex-1 flex flex-col min-w-0 pb-12">
        <!-- Header -->
        <header class="h-16 bg-white border-b border-border px-4 md:px-8 flex items-center justify-between gap-4 sticky top-0 z-30 shadow-2xs">
            <div class="flex items-center gap-3">
                <div>
                    <h1 class="text-base md:text-lg font-bold tracking-tight text-foreground">{{ $title }}</h1>
                    @if($subtitle)
                        <p class="text-xs text-muted-foreground hidden sm:block">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('app.dashboard') }}" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-secondary border border-border text-foreground hover:bg-muted transition-colors flex items-center gap-1.5">
                    <i data-lucide="store" class="w-3.5 h-3.5"></i>
                    <span>Stall Application</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-2 text-muted-foreground hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors" title="Sign Out">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
            </div>
        </header>

        <!-- Flash Messages -->
        <main class="flex-1 p-4 md:p-8 max-w-7xl w-full mx-auto space-y-6">
            @if(session('success'))
                <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-medium flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if($errors->any())
                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-medium space-y-1">
                    <div class="flex items-center gap-2 font-bold">
                        <i data-lucide="alert-triangle" class="w-4 h-4 text-rose-600 shrink-0"></i>
                        <span>Please correct the errors below:</span>
                    </div>
                    <ul class="list-disc pl-6 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <!-- Global Toast Notifications -->
    <x-ui.toast />
    @stack('scripts')
</body>
</html>
