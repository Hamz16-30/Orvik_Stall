@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Suppliers" subtitle="Directory of vendor contacts and inventory purchase accounts" activeNav="suppliers">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-foreground">Supplier Directory</h2>
                <p class="text-xs text-muted-foreground">Manage vendors, procurement channels, and historical purchasing</p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" :href="route('app.suppliers.create')">
                    Add Supplier
                </x-ui.button>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Summary Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-dashboard.metric-card
                label="Total Suppliers"
                value="{{ $summary['total_suppliers'] }}"
                change="Registered Vendors"
                trend="neutral"
                icon="truck"
            />
            <x-dashboard.metric-card
                label="Active Suppliers"
                value="{{ $summary['active_suppliers'] }}"
                change="Active Procurement"
                trend="up"
                icon="check-circle"
            />
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs">
            <form method="GET" action="{{ route('app.suppliers.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                <div class="relative flex-1 w-full">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by contact name, company, phone, or email..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    />
                </div>

                <div class="w-full sm:w-48">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs font-bold rounded-lg bg-secondary text-foreground hover:bg-muted transition-colors">
                    Filter
                </button>
            </form>
        </div>

        <!-- Desktop Suppliers Table -->
        <div class="hidden md:block">
            <x-ui.card>
                <x-ui.table :headers="['Supplier Name', 'Company', 'Phone', 'Email', 'Purchases', 'Total Spent', 'Status', 'Actions']">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-4 font-bold text-foreground">
                                <a href="{{ route('app.suppliers.show', $supplier->id) }}" class="hover:text-accent hover:underline">
                                    {{ $supplier->name }}
                                </a>
                            </td>
                            <td class="px-5 py-4 font-semibold text-foreground">
                                {{ $supplier->company_name ?: '—' }}
                            </td>
                            <td class="px-5 py-4 font-mono text-muted-foreground">
                                {{ $supplier->phone ?: '—' }}
                            </td>
                            <td class="px-5 py-4 text-muted-foreground">
                                {{ $supplier->email ?: '—' }}
                            </td>
                            <td class="px-5 py-4 font-mono font-semibold text-foreground">
                                {{ $supplier->purchases_count ?? 0 }} purchase(s)
                            </td>
                            <td class="px-5 py-4 font-mono font-bold text-foreground">
                                {{ $currency }}{{ number_format($supplier->purchases_sum_total_amount ?? 0, 2) }}
                            </td>
                            <td class="px-5 py-4">
                                @if($supplier->status === 'active')
                                    <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                                @else
                                    <x-ui.badge variant="secondary" size="sm">Inactive</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('app.suppliers.show', $supplier->id) }}" class="p-1 rounded text-muted-foreground hover:text-accent" title="View Profile">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('app.suppliers.edit', $supplier->id) }}" class="p-1 rounded text-muted-foreground hover:text-accent" title="Edit Supplier">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center text-xs text-muted-foreground">
                                No suppliers found. <a href="{{ route('app.suppliers.create') }}" class="text-accent underline font-semibold">Add supplier</a>
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if($suppliers->hasPages())
                    <div class="px-5 py-4 border-t border-border">
                        {{ $suppliers->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- Mobile Cards List -->
        <div class="md:hidden space-y-3">
            @forelse($suppliers as $supplier)
                <div class="bg-white p-4 rounded-xl border border-border/80 shadow-2xs space-y-2 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <a href="{{ route('app.suppliers.show', $supplier->id) }}" class="font-bold text-foreground text-sm hover:text-accent">
                                {{ $supplier->name }}
                            </a>
                            <p class="text-[11px] text-muted-foreground">{{ $supplier->company_name ?: ($supplier->phone ?: 'No company') }}</p>
                        </div>
                        @if($supplier->status === 'active')
                            <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                        @else
                            <x-ui.badge variant="secondary" size="sm">Inactive</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex justify-between pt-1 border-t border-border/40">
                        <span class="text-muted-foreground">Purchased:</span>
                        <span class="font-mono font-bold text-foreground">{{ $currency }}{{ number_format($supplier->purchases_sum_total_amount ?? 0, 2) }}</span>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-border/40">
                        <a href="{{ route('app.suppliers.show', $supplier->id) }}" class="px-3 py-1.5 rounded-lg bg-secondary text-foreground font-semibold">View</a>
                        <a href="{{ route('app.suppliers.edit', $supplier->id) }}" class="px-3 py-1.5 rounded-lg bg-accent text-white font-semibold">Edit</a>
                    </div>
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-border text-center text-xs text-muted-foreground">
                    No suppliers found.
                </div>
            @endforelse

            @if($suppliers->hasPages())
                <div class="pt-2">
                    {{ $suppliers->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
