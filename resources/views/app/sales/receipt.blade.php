@php
    $currency = $business->currency === 'USD' ? '$' : 'Rs. ';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_number }} — {{ $business->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Courier+Prime:ital,wght@0,400;0,700;1,400&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @media print {
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .receipt-container {
                box-shadow: none !important;
                border: none !important;
                width: 80mm !important;
                max-width: 80mm !important;
                padding: 0 !important;
                margin: 0 auto !important;
            }
        }
        .receipt-font {
            font-family: 'Courier Prime', monospace, 'Courier New', Courier;
        }
    </style>
</head>
<body class="bg-muted/40 min-h-screen py-8 px-4 font-sans text-foreground antialiased">
    <!-- Top Action Buttons (Hidden when printing) -->
    <div class="max-w-xs mx-auto mb-4 flex items-center justify-between no-print">
        <a href="{{ route('app.sales') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to POS</span>
        </a>

        <button
            onclick="window.print()"
            class="px-4 py-2 bg-accent text-white font-bold text-xs rounded-lg shadow-sm hover:bg-accent/90 flex items-center gap-1.5"
        >
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Print Receipt</span>
        </button>
    </div>

    <!-- 80mm Thermal Receipt Canvas -->
    <div class="receipt-container max-w-[340px] mx-auto bg-white p-6 rounded-2xl border border-border shadow-md receipt-font text-xs leading-tight">
        <!-- Stall Brand & Header -->
        <div class="text-center space-y-1 pb-3 border-b border-dashed border-gray-400">
            <h1 class="text-base font-bold tracking-tight text-black font-sans uppercase">{{ $business->name }}</h1>
            @if($business->address)
                <p class="text-[11px] text-gray-700">{{ $business->address }}</p>
            @endif
            @if($business->phone)
                <p class="text-[11px] text-gray-700">Tel: {{ $business->phone }}</p>
            @endif
        </div>

        <!-- Receipt Meta Information -->
        <div class="py-2.5 space-y-1 text-[11px] border-b border-dashed border-gray-400">
            <div class="flex justify-between">
                <span>Invoice #:</span>
                <span class="font-bold font-mono">{{ $sale->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Date:</span>
                <span>{{ $sale->completed_at ? $sale->completed_at->format('d M Y, H:i') : '' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Cashier:</span>
                <span>{{ $sale->user ? $sale->user->name : 'Operator' }}</span>
            </div>
            @if($sale->status === 'voided')
                <div class="text-center font-bold text-rose-600 uppercase pt-1 tracking-wider text-xs">
                    *** VOIDED TRANSACTION ***
                </div>
            @endif
        </div>

        <!-- Items Breakdown -->
        <div class="py-2.5 border-b border-dashed border-gray-400 space-y-2">
            <div class="flex justify-between font-bold text-[11px] pb-1 border-b border-gray-200">
                <span>ITEM</span>
                <span>TOTAL</span>
            </div>

            @foreach($sale->items as $item)
                <div class="text-[11px] space-y-0.5">
                    <div class="flex justify-between font-semibold">
                        <span class="truncate pr-2">{{ $item->product_name }}</span>
                        <span class="shrink-0">{{ $currency }}{{ number_format($item->line_total, 2) }}</span>
                    </div>
                    <div class="text-[10px] text-gray-600">
                        {{ (float)$item->quantity }} &times; {{ $currency }}{{ number_format($item->unit_price, 2) }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Financial Totals -->
        <div class="py-2.5 border-b border-dashed border-gray-400 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>Subtotal:</span>
                <span>{{ $currency }}{{ number_format($sale->subtotal, 2) }}</span>
            </div>

            @if($sale->discount_amount > 0)
                <div class="flex justify-between font-bold">
                    <span>Discount:</span>
                    <span>-{{ $currency }}{{ number_format($sale->discount_amount, 2) }}</span>
                </div>
            @endif

            @if($sale->tax_amount > 0)
                <div class="flex justify-between">
                    <span>Tax:</span>
                    <span>{{ $currency }}{{ number_format($sale->tax_amount, 2) }}</span>
                </div>
            @endif

            <div class="flex justify-between font-bold text-sm pt-1.5 border-t border-black text-black">
                <span>TOTAL:</span>
                <span>{{ $currency }}{{ number_format($sale->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- Payment Details -->
        <div class="py-2.5 border-b border-dashed border-gray-400 space-y-1 text-[11px]">
            <div class="flex justify-between">
                <span>Payment Method:</span>
                <span class="uppercase font-semibold">{{ str_replace('_', ' ', $sale->payment_method) }}</span>
            </div>

            @if($sale->payment_method === 'cash')
                @if($sale->amount_received)
                    <div class="flex justify-between">
                        <span>Cash Tendered:</span>
                        <span>{{ $currency }}{{ number_format($sale->amount_received, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold">
                    <span>Change Returned:</span>
                    <span>{{ $currency }}{{ number_format($sale->change_amount ?? 0, 2) }}</span>
                </div>
            @endif
        </div>

        <!-- Thank You Footer -->
        <div class="text-center pt-4 space-y-1 text-[11px]">
            <p class="font-bold">THANK YOU FOR YOUR VISIT!</p>
            <p class="text-[10px] text-gray-600">✦ Powered by ORVIK Store</p>
        </div>
    </div>

    <script>
        // Optional auto-print on open if requested via query param ?autoprint=1
        if (new URLSearchParams(window.location.search).get('autoprint') === '1') {
            window.onload = () => window.print();
        }
    </script>
</body>
</html>
