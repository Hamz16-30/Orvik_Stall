<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Generate a unique sequential invoice number scoped to the business.
     */
    public function generateInvoiceNumber(Business $business, string $prefix = 'INV'): string
    {
        // Compute the highest existing integer suffix for this business
        $count = Sale::where('business_id', $business->id)->count();

        // Increment until we find a free number
        $sequence = $count + 1;
        do {
            $invoiceNumber = sprintf('%s-%06d', $prefix, $sequence);
            $exists = Sale::where('business_id', $business->id)
                ->where('invoice_number', $invoiceNumber)
                ->exists();

            if ($exists) {
                $sequence++;
            }
        } while ($exists);

        return $invoiceNumber;
    }
}
