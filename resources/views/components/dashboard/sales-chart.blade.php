@props([
    'title' => 'Sales Overview',
    'subtitle' => 'Live stall transaction trends (Past 7 Days)',
])

<div class="bg-white p-5 rounded-xl border border-border/80 shadow-2xs flex flex-col justify-between h-full">
    <div class="flex items-center justify-between pb-3 border-b border-border/50">
        <div>
            <h4 class="text-sm font-semibold text-foreground">{{ $title }}</h4>
            <p class="text-[11px] text-muted-foreground">{{ $subtitle }}</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                <i data-lucide="arrow-up-right" class="w-3 h-3"></i>
                +18.4%
            </span>
        </div>
    </div>

    <!-- SVG Bézier Chart Area -->
    <div class="relative w-full pt-4 pb-2">
        <svg viewBox="0 0 500 160" class="w-full h-36 overflow-visible" preserveAspectRatio="none">
            <defs>
                <linearGradient id="salesGradient" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="hsl(239, 84%, 67%)" stop-opacity="0.25" />
                    <stop offset="100%" stop-color="hsl(239, 84%, 67%)" stop-opacity="0.0" />
                </linearGradient>
            </defs>

            <!-- Subtle Grid Lines -->
            <line x1="0" y1="30" x2="500" y2="30" stroke="hsl(var(--border))" stroke-dasharray="3 3" stroke-width="1" />
            <line x1="0" y1="80" x2="500" y2="80" stroke="hsl(var(--border))" stroke-dasharray="3 3" stroke-width="1" />
            <line x1="0" y1="130" x2="500" y2="130" stroke="hsl(var(--border))" stroke-dasharray="3 3" stroke-width="1" />

            <!-- Area Fill -->
            <path
                d="M 0 140
                   C 70 120, 110 50, 170 65
                   C 230 80, 270 30, 340 45
                   C 410 60, 450 15, 500 20
                   L 500 160 L 0 160 Z"
                fill="url(#salesGradient)"
            />

            <!-- Stroke Curve -->
            <path
                d="M 0 140
                   C 70 120, 110 50, 170 65
                   C 230 80, 270 30, 340 45
                   C 410 60, 450 15, 500 20"
                fill="none"
                stroke="hsl(239, 84%, 67%)"
                stroke-width="2.5"
                stroke-linecap="round"
            />

            <!-- Data Point Circles -->
            <circle cx="170" cy="65" r="4" fill="#ffffff" stroke="hsl(239, 84%, 67%)" stroke-width="2" />
            <circle cx="340" cy="45" r="4" fill="#ffffff" stroke="hsl(239, 84%, 67%)" stroke-width="2" />
            <circle cx="500" cy="20" r="4" fill="#ffffff" stroke="hsl(239, 84%, 67%)" stroke-width="2" />
        </svg>

        <div class="flex justify-between text-[11px] text-muted-foreground pt-2 px-1">
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
            <span>Sun</span>
        </div>
    </div>
</div>
