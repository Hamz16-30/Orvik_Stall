@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Voucher #{{ $purchase->purchase_number }} — {{ $business->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print { display: none !important; }
            .voucher-box { border: none !important; box-shadow: none !important; }
        }
    </style>
</head>
<body class="bg-muted/40 min-h-screen py-8 px-4 font-sans text-foreground antialiased">
    <div class="max-w-2xl mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('app.purchases.show', $purchase->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Details</span>
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-accent text-white font-bold text-xs rounded-lg shadow-sm hover:bg-accent/90 flex items-center gap-1.5">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print Voucher</span>
        </button>
    </div>

    <div class="voucher-box max-w-2xl mx-auto bg-white p-8 rounded-2xl border border-border shadow-sm space-y-6">
        <div class="flex items-start justify-between pb-6 border-b border-border">
            <div>
                <h1 class="text-xl font-bold text-foreground">{{ $business->name }}</h1>
                <p class="text-xs text-muted-foreground mt-0.5">Goods Received / Purchase Voucher</p>
            </div>
            <div class="text-right">
                <span class="text-base font-bold font-mono text-foreground">{{ $purchase->purchase_number }}</span>
                <p class="text-xs text-muted-foreground mt-0.5">Date: {{ $purchase->purchased_at ? $purchase->purchased_at->format('d M Y') : '' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-muted-foreground block text-[11px]">Vendor / Supplier:</span>
                <span class="font-bold text-foreground text-sm">{{ $purchase->supplier ? $purchase->supplier->name : 'Unassigned Vendor' }}</span>
                @if($purchase->supplier?->company_name)
                    <p class="text-muted-foreground">{{ $purchase->supplier->company_name }}</p>
                @endif
            </div>
            <div class="text-right">
                <span class="text-muted-foreground block text-[11px]">Payment Status:</span>
                <span class="font-bold uppercase {{ $purchase->payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700' }}">
                    {{ $purchase->payment_status }} &bull; {{ ucfirst(str_replace('_', ' ', $purchase->payment_method)) }}
                </span>
            </div>
        </div>

        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b border-border text-[11px] font-semibold text-muted-foreground">
                    <th class="py-2">Item Description</th>
                    <th class="py-2">SKU</th>
                    <th class="py-2 text-right">Unit Cost</th>
                    <th class="py-2 text-right">Qty Ingested</th>
                    <th class="py-2 text-right">Total Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border/40">
                @foreach($purchase->items as $item)
                    <tr>
                        <td class="py-2.5 font-semibold text-foreground">{{ $item->product_name }}</td>
                        <td class="py-2.5 font-mono text-muted-foreground">{{ $item->sku ?: '—' }}</td>
                        <td class="py-2.5 font-mono text-right">{{ $currency }}{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="py-2.5 font-mono font-bold text-right text-emerald-700">+{{ (float)$item->quantity }}</td>
                        <td class="py-2.5 font-mono font-bold text-right text-foreground">{{ $currency }}{{ number_format($item->line_total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="flex justify-end pt-4 border-t border-border">
            <div class="w-64 space-y-1.5 text-xs text-right">
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
                <div class="pt-2 border-t border-border flex justify-between text-base font-bold text-foreground">
                    <span>Total Procurement:</span>
                    <span class="font-mono">{{ $currency }}{{ number_format($purchase->total_amount, 2) }}</span>
                </div>
            </div>
        </div>

        <div class="pt-8 border-t border-dashed border-gray-300 flex justify-between text-[11px] text-muted-foreground">
            <div>
                <p>Received By: ___________________</p>
            </div>
            <div>
                <p>Authorized Signature: ___________________</p>
            </div>
        </div>
    </div>
</body>
</html>
