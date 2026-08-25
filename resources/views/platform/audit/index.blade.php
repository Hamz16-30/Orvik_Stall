<x-layouts.platform title="SaaS Audit Trail" subtitle="Immutable log of all platform-level and tenant administrative actions" activeNav="audit">
    <!-- Filter -->
    <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
        <form method="GET" action="{{ route('platform.audit') }}" class="flex items-center gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by action, user, or business name..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-border bg-background text-foreground"
                />
            </div>

            <button type="submit" class="px-4 py-2 bg-secondary text-foreground font-bold text-xs rounded-xl hover:bg-muted shrink-0">
                Filter
            </button>
            @if(request()->hasAny(['search', 'action']))
                <a href="{{ route('platform.audit') }}" class="px-3 py-2 text-xs text-muted-foreground hover:text-foreground font-semibold shrink-0">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-secondary/70 border-b border-border text-[11px] uppercase tracking-wider text-muted-foreground font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">Timestamp</th>
                        <th class="px-5 py-3.5">Action</th>
                        <th class="px-5 py-3.5">Actor (User)</th>
                        <th class="px-5 py-3.5">Business / Stall</th>
                        <th class="px-5 py-3.5">Details</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60 text-foreground">
                    @forelse($logs as $log)
                        <tr class="hover:bg-muted/20 transition-colors">
                            <td class="px-5 py-3.5 text-muted-foreground font-mono whitespace-nowrap">
                                {{ $log->created_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-secondary border border-border text-foreground">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-semibold text-foreground">{{ $log->user?->name ?? 'System' }}</span>
                                <span class="block text-[10px] text-muted-foreground">{{ $log->user?->email ?? '' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-foreground">{{ $log->business?->name ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-muted-foreground font-mono text-[11px]">
                                @if(!empty($log->new_values))
                                    {{ json_encode($log->new_values) }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-muted-foreground">
                                No audit records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-border/70">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-layouts.platform>
