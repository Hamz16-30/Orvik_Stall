@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Expenses" subtitle="Operational cash outflows and overhead cost logging" activeNav="expenses">
    <div x-data="{ voidModalOpen: false, activeExpenseId: null, activeExpenseDesc: '' }" class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold tracking-tight text-foreground">Operating Expenses</h2>
                <p class="text-xs text-muted-foreground">Track stall rent, utilities, packaging, transport, and miscellaneous costs</p>
            </div>

            <div class="flex items-center gap-2.5">
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="tag" :href="route('app.expenses.categories.index')">
                    Manage Categories
                </x-ui.button>
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" :href="route('app.expenses.create')">
                    Record Expense
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
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-dashboard.metric-card
                label="Today's Expenses"
                value="{{ $currency }}{{ number_format($summary['today_total'], 2) }}"
                change="Daily Overhead"
                trend="neutral"
                icon="receipt"
            />
            <x-dashboard.metric-card
                label="This Week's Expenses"
                value="{{ $currency }}{{ number_format($summary['week_total'], 2) }}"
                change="Weekly Cumulative"
                trend="neutral"
                icon="calendar"
            />
            <x-dashboard.metric-card
                label="This Month's Expenses"
                value="{{ $currency }}{{ number_format($summary['month_total'], 2) }}"
                change="Monthly Total Outflow"
                trend="neutral"
                icon="trending-down"
            />
        </div>

        <!-- Filter Bar -->
        <div class="bg-white p-4 rounded-2xl border border-border/80 shadow-2xs">
            <form method="GET" action="{{ route('app.expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <div class="lg:col-span-2 relative">
                    <i data-lucide="search" class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 -translate-y-1/2"></i>
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by description or reference..."
                        class="w-full pl-9 pr-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    />
                </div>

                <div>
                    <select
                        name="category_id"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <select
                        name="date_range"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all" {{ request('date_range') === 'all' ? 'selected' : '' }}>All Dates</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="yesterday" {{ request('date_range') === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                        <option value="this_week" {{ request('date_range') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_range') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select
                        name="status"
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 text-xs rounded-lg border border-border bg-background text-foreground focus:outline-none focus:ring-2 focus:ring-accent"
                    >
                        <option value="all">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Voided</option>
                    </select>

                    <button type="submit" class="px-3 py-2 text-xs font-bold rounded-lg bg-secondary text-foreground hover:bg-muted shrink-0">
                        Filter
                    </button>
                </div>
            </form>
        </div>

        <!-- Desktop Expenses Table -->
        <div class="hidden md:block">
            <x-ui.card>
                <x-ui.table :headers="['Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Status', 'Actions']">
                    @forelse($expenses as $expense)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-4 font-mono text-muted-foreground whitespace-nowrap">
                                {{ $expense->expense_date ? $expense->expense_date->format('M d, Y') : '' }}
                            </td>
                            <td class="px-5 py-4 font-bold text-foreground">
                                {{ $expense->category ? $expense->category->name : 'Uncategorized' }}
                            </td>
                            <td class="px-5 py-4 text-foreground">
                                <span class="font-medium block">{{ $expense->description }}</span>
                                @if($expense->reference)
                                    <span class="text-[10px] text-muted-foreground font-mono">Ref: {{ $expense->reference }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 font-mono font-extrabold text-foreground">
                                {{ $currency }}{{ number_format($expense->amount, 2) }}
                            </td>
                            <td class="px-5 py-4 capitalize">
                                {{ str_replace('_', ' ', $expense->payment_method) }}
                            </td>
                            <td class="px-5 py-4">
                                @if($expense->status === 'active')
                                    <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                                @else
                                    <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-xs">
                                @if($expense->status === 'active')
                                    <button
                                        type="button"
                                        @click="activeExpenseId = {{ $expense->id }}; activeExpenseDesc = '{{ addslashes($expense->description) }}'; voidModalOpen = true;"
                                        class="p-1 text-muted-foreground hover:text-rose-600"
                                        title="Void Expense"
                                    >
                                        <i data-lucide="ban" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-xs text-muted-foreground">
                                No expenses recorded. <a href="{{ route('app.expenses.create') }}" class="text-accent underline font-semibold">Add expense</a>
                            </td>
                        </tr>
                    @endforelse
                </x-ui.table>

                @if($expenses->hasPages())
                    <div class="px-5 py-4 border-t border-border">
                        {{ $expenses->links() }}
                    </div>
                @endif
            </x-ui.card>
        </div>

        <!-- Mobile Expenses List -->
        <div class="md:hidden space-y-3">
            @forelse($expenses as $expense)
                <div class="bg-white p-4 rounded-xl border border-border/80 shadow-2xs space-y-2 text-xs">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="font-bold text-foreground">{{ $expense->description }}</span>
                            <p class="text-[11px] text-muted-foreground">{{ $expense->category ? $expense->category->name : 'Uncategorized' }} &bull; {{ $expense->expense_date ? $expense->expense_date->format('M d') : '' }}</p>
                        </div>
                        @if($expense->status === 'active')
                            <x-ui.badge variant="in-stock" size="sm">Active</x-ui.badge>
                        @else
                            <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                        @endif
                    </div>

                    <div class="flex justify-between pt-1 border-t border-border/40">
                        <span class="text-muted-foreground">Amount:</span>
                        <span class="font-mono font-bold text-foreground">{{ $currency }}{{ number_format($expense->amount, 2) }}</span>
                    </div>

                    @if($expense->status === 'active')
                        <div class="flex justify-end pt-2 border-t border-border/40">
                            <button
                                type="button"
                                @click="activeExpenseId = {{ $expense->id }}; activeExpenseDesc = '{{ addslashes($expense->description) }}'; voidModalOpen = true;"
                                class="px-3 py-1 rounded-lg text-rose-600 border border-rose-200 text-xs font-semibold"
                            >
                                Void Expense
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white p-8 rounded-xl border border-border text-center text-xs text-muted-foreground">
                    No expenses recorded.
                </div>
            @endforelse

            @if($expenses->hasPages())
                <div class="pt-2">
                    {{ $expenses->links() }}
                </div>
            @endif
        </div>

        <!-- Void Expense Modal -->
        <div x-show="voidModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="voidModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left space-y-4" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border">
                        <h3 class="text-base font-bold text-foreground">Void Expense</h3>
                        <button type="button" @click="voidModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <p class="text-xs text-muted-foreground" x-text="'Are you sure you want to void: ' + activeExpenseDesc + '?'"></p>

                    <form :action="'{{ url('/app/expenses') }}/' + activeExpenseId + '/void'" method="POST" class="space-y-4">
                        @csrf

                        <x-ui.input
                            label="Reason for Voiding"
                            name="reason"
                            id="void_reason"
                            placeholder="e.g. Duplicate entry, incorrect amount, cancelled bill..."
                            required
                        />

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="voidModalOpen = false">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="destructive" size="sm" rounded="lg">
                                Confirm Void
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
