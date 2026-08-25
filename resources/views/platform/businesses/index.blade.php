<x-layouts.platform title="Businesses Directory" subtitle="Manage all tenant stalls, plans, and subscription states" activeNav="businesses">
    <!-- Filter & Search Bar -->
    <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs space-y-3">
        <form method="GET" action="{{ route('platform.businesses') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="sm:col-span-2 relative">
                <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search by business name, owner name, email, or ID..."
                    class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                />
            </div>

            <div class="flex items-center gap-2">
                <select name="status" onchange="this.form.submit()" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="trialing" {{ request('status') === 'trialing' ? 'selected' : '' }}>Trialing</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid Subscriptions</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-secondary text-foreground font-bold text-xs rounded-xl hover:bg-muted shrink-0">
                    Filter
                </button>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('platform.businesses') }}" class="px-3 py-2 text-xs text-muted-foreground hover:text-foreground font-semibold shrink-0">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Businesses Table -->
    <div class="bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead class="bg-secondary/70 border-b border-border text-[11px] uppercase tracking-wider text-muted-foreground font-semibold">
                    <tr>
                        <th class="px-5 py-3.5">ID</th>
                        <th class="px-5 py-3.5">Business Name</th>
                        <th class="px-5 py-3.5">Owner Details</th>
                        <th class="px-5 py-3.5">Plan</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5">Products / Sales</th>
                        <th class="px-5 py-3.5">Created</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border/60 text-foreground">
                    @forelse($businesses as $b)
                        @php
                            $sub = $b->currentSubscription();
                        @endphp
                        <tr class="hover:bg-muted/30 transition-colors">
                            <td class="px-5 py-3.5 font-mono text-muted-foreground">
                                #{{ $b->id }}
                            </td>
                            <td class="px-5 py-3.5 font-bold text-foreground">
                                <a href="{{ route('platform.businesses.show', $b->id) }}" class="hover:text-accent hover:underline">
                                    {{ $b->name }}
                                </a>
                                <span class="block text-[10px] text-muted-foreground font-mono">{{ $b->slug }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-foreground">{{ $b->owner?->name ?? '—' }}</span>
                                <span class="block text-[10px] text-muted-foreground">{{ $b->owner?->email ?? '—' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold font-mono {{ $sub && $sub->isTrialing() ? 'bg-amber-100 text-amber-800' : 'bg-secondary text-foreground border border-border' }}">
                                    {{ $sub?->plan?->name ?? 'Free' }}
                                    @if($sub && $sub->isTrialing())
                                        (Trial)
                                    @endif
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($b->status === 'suspended')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800">SUSPENDED</span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">ACTIVE</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5 font-mono text-muted-foreground">
                                {{ $b->products->count() }} prod / {{ $b->sales->count() }} sales
                            </td>
                            <td class="px-5 py-3.5 text-muted-foreground font-mono">
                                {{ $b->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-3.5 text-right space-x-2">
                                <a href="{{ route('platform.businesses.show', $b->id) }}" class="px-2.5 py-1 rounded-lg bg-secondary text-foreground hover:bg-muted text-xs font-semibold border border-border">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-8 text-center text-muted-foreground">
                                No businesses matching your filters were found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($businesses->hasPages())
            <div class="p-4 border-t border-border/70">
                {{ $businesses->links() }}
            </div>
        @endif
    </div>
</x-layouts.platform>
