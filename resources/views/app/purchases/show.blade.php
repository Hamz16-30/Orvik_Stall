@php
    $canVoid = ($user->isOwner() || $user->isManager()) && $purchase->status === 'completed';
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp

<x-layouts.app title="Purchase #{{ $purchase->purchase_number }}" subtitle="Stock procurement transaction and vendor invoice" activeNav="purchases">
    <div x-data="{ voidModalOpen: false }" class="max-w-4xl space-y-6">
        <!-- Back Navigation & Top Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <a href="{{ route('app.purchases.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Purchases</span>
            </a>

            <div class="flex items-center gap-2">
                <x-ui.button variant="secondary" size="sm" rounded="lg" icon="printer" :href="route('app.purchases.receipt', $purchase->id)" target="_blank">
                    Print Voucher
                </x-ui.button>

                @if($canVoid)
                    <x-ui.button variant="destructive" size="sm" rounded="lg" icon="ban" @click="voidModalOpen = true">
                        Void Purchase
                    </x-ui.button>
                @endif
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($purchase->status === 'voided')
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                <div class="flex items-center gap-2 font-bold">
                    <i data-lucide="alert-octagon" class="w-4 h-4 text-rose-600"></i>
                    <span>This purchase was VOIDED on {{ $purchase->voided_at ? $purchase->voided_at->format('M d, Y H:i') : '' }} by {{ $purchase->voidedByUser ? $purchase->voidedByUser->name : 'Manager' }}</span>
                </div>
                <p class="text-rose-700 pl-6"><strong>Reason:</strong> {{ $purchase->void_reason }}</p>
                <p class="text-[11px] text-rose-600 pl-6">Inventory quantities ingested by this shipment were reversed from stock.</p>
            </div>
        @endif

        <!-- Purchase Header Card -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-border/70">
                <div>
                    <div class="flex items-center gap-3">
                        <h2 class="text-xl font-bold font-mono text-foreground">{{ $purchase->purchase_number }}</h2>
                        @if($purchase->status === 'completed')
                            <x-ui.badge variant="in-stock" size="sm">Completed</x-ui.badge>
                        @elseif($purchase->status === 'voided')
                            <x-ui.badge variant="destructive" size="sm">Voided</x-ui.badge>
                        @endif
                    </div>
                    <p class="text-xs text-muted-foreground mt-1">
                        Procured on {{ $purchase->purchased_at ? $purchase->purchased_at->format('l, M d, Y') : 'N/A' }}
                    </p>
                </div>

                <div class="text-left sm:text-right">
                    <span class="text-xs text-muted-foreground block">Total Cost</span>
                    <span class="text-2xl font-extrabold text-foreground font-mono">{{ $currency }}{{ number_format($purchase->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Metadata Details Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs">
                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Supplier / Vendor</span>
                    <span class="font-bold text-foreground mt-0.5 block">
                        {{ $purchase->supplier ? $purchase->supplier->name : 'Unassigned Vendor' }}
                    </span>
                </div>

                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Recorded By</span>
                    <span class="font-bold text-foreground mt-0.5 block">{{ $purchase->user ? $purchase->user->name : 'N/A' }}</span>
                </div>

                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Payment Method</span>
                    <span class="font-bold text-foreground capitalize mt-0.5 block">{{ str_replace('_', ' ', $purchase->payment_method) }}</span>
                </div>

                <div class="p-3 bg-secondary/30 rounded-xl">
                    <span class="text-muted-foreground block text-[11px]">Payment Status</span>
                    <span class="font-bold uppercase mt-0.5 block {{ $purchase->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                        {{ $purchase->payment_status }}
                    </span>
                </div>
            </div>

            <!-- Ingested Items Table -->
            <div class="pt-2">
                <h3 class="text-sm font-bold text-foreground mb-3">Ingested Line Items ({{ $purchase->items->count() }})</h3>
                <x-ui.table :headers="['Product Name', 'SKU', 'Unit Cost', 'Quantity Ingested', 'Line Total']">
                    @foreach($purchase->items as $item)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-3.5 font-bold text-foreground">
                                {{ $item->product_name }}
                            </td>

                            <td class="px-5 py-3.5 font-mono text-muted-foreground">
                                {{ $item->sku ?: '—' }}
                            </td>

                            <td class="px-5 py-3.5 font-mono text-foreground">
                                {{ $currency }}{{ number_format($item->unit_cost, 2) }}
                            </td>

                            <td class="px-5 py-3.5 font-mono font-bold text-emerald-700">
                                +{{ (float)$item->quantity }}
                            </td>

                            <td class="px-5 py-3.5 font-mono font-bold text-foreground text-right">
                                {{ $currency }}{{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </div>

            <!-- Financial Summary Box -->
            <div class="flex justify-end pt-4 border-t border-border/70">
                <div class="w-full sm:w-72 space-y-2 text-xs">
                    <div class="flex justify-between text-muted-foreground">
                        <span>Subtotal:</span>
                        <span class="font-mono font-semibold text-foreground">{{ $currency }}{{ number_format($purchase->subtotal, 2) }}</span>
                    </div>

                    @if($purchase->discount_amount > 0)
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Discount:</span>
                            <span class="font-mono">-{{ $currency }}{{ number_format($purchase->discount_amount, 2) }}</span>
                        </div>
                    @endif

                    <div class="pt-2 border-t border-border flex justify-between text-base font-extrabold text-foreground">
                        <span>Total Cost:</span>
                        <span class="font-mono">{{ $currency }}{{ number_format($purchase->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Void Confirmation Modal -->
        @if($canVoid)
            <div x-show="voidModalOpen" x-cloak class="relative z-50">
                <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="voidModalOpen = false"></div>
                <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left space-y-4" @click.stop>
                        <div class="flex items-center justify-between pb-3 border-b border-border">
                            <h3 class="text-base font-bold text-foreground">Void Purchase #{{ $purchase->purchase_number }}</h3>
                            <button type="button" @click="voidModalOpen = false" class="text-muted-foreground hover:text-foreground">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                        </div>

                        <p class="text-xs text-muted-foreground">
                            Voiding this procurement order will deduct the ingested inventory quantities back out of active stock. This action is irreversible.
                        </p>

                        <form method="POST" action="{{ route('app.purchases.void', $purchase->id) }}" class="space-y-4">
                            @csrf

                            <x-ui.input
                                label="Reason for Voiding"
                                name="reason"
                                id="void_reason"
                                placeholder="e.g. Shipment rejected, duplicate entry, supplier return..."
                                required
                            />

                            <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                                <x-ui.button type="button" variant="ghost" size="sm" @click="voidModalOpen = false">
                                    Cancel
                                </x-ui.button>
                                <x-ui.button type="submit" variant="destructive" size="sm" rounded="lg">
                                    Confirm Void & Reverse Inventory
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
