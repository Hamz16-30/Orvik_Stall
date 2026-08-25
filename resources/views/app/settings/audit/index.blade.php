<x-layouts.app title="Security Audit Trail" subtitle="Append-only immutable record of critical security and operational actions" activeNav="settings">
    <div class="space-y-6 max-w-5xl">
        <!-- Back Navigation -->
        <a href="{{ route('app.settings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Settings</span>
        </a>

        <x-ui.card title="Audit Events for {{ $business->name }}" description="Chronological security stream (Immutable)">
            <x-ui.table :headers="['Timestamp', 'Operator', 'Action Taken', 'Entity Type', 'IP Address']">
                @forelse($logs as $log)
                    <tr class="hover:bg-muted/20 transition-colors text-xs">
                        <td class="px-5 py-3.5 font-mono text-muted-foreground whitespace-nowrap">
                            {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                        </td>

                        <td class="px-5 py-3.5 font-semibold text-foreground">
                            {{ $log->user ? $log->user->name : 'System / Guest' }}
                        </td>

                        <td class="px-5 py-3.5">
                            <span class="inline-flex items-center px-2 py-0.5 rounded font-mono text-[11px] font-bold bg-secondary text-foreground border border-border">
                                {{ $log->action }}
                            </span>
                        </td>

                        <td class="px-5 py-3.5 text-muted-foreground">
                            {{ $log->entity_type ? class_basename($log->entity_type) . ' #' . $log->entity_id : '—' }}
                        </td>

                        <td class="px-5 py-3.5 font-mono text-muted-foreground">
                            {{ $log->ip_address ?? '127.0.0.1' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-8 text-center text-xs text-muted-foreground">
                            No audit logs recorded yet.
                        </td>
                    </tr>
                @endforelse
            </x-ui.table>

            <div class="mt-4">
                {{ $logs->links() }}
            </div>
        </x-ui.card>
    </div>
</x-layouts.app>
