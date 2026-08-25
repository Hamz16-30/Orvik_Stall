@php
    $faqs = [
        [
            'q' => 'What is ORVIK Store?',
            'a' => 'ORVIK Store is a fast, web-based SaaS stall management and point-of-sale (POS) platform designed specifically for retail stalls, market vendors, and pop-up retailers.'
        ],
        [
            'q' => 'Who is ORVIK Store for?',
            'a' => 'It is built for market stall operators, food & beverage counters, artisan vendors, festival sellers, and boutique shops seeking an ultra-simple alternative to bloated ERP systems.'
        ],
        [
            'q' => 'Can I use it on my phone?',
            'a' => 'Yes. ORVIK Store is engineered mobile-first with large 44px+ touch targets, allowing you to ring up customer sales and check stock levels comfortably with one thumb on any modern smartphone.'
        ],
        [
            'q' => 'Can I manage my inventory?',
            'a' => 'Yes. You can catalog products, define SKUs, set reorder threshold alerts, and automatically identify dead stock that has not sold in over 30 to 45 days.'
        ],
        [
            'q' => 'Can I track daily, weekly, and monthly sales?',
            'a' => 'Yes. The live dashboard and reporting suite automatically aggregate sales numbers across custom intervals, showing gross revenue, transaction counts, and calculated profit margins.'
        ],
        [
            'q' => 'Can I generate reports?',
            'a' => 'Yes. You can view and print end-of-day cashier reconciliation summaries, inventory turnover audits, and dead stock capital lockup analyses.'
        ],
        [
            'q' => 'Can multiple staff members use it?',
            'a' => 'Yes. Multiple cashiers and stall operators can be assigned role-based accounts to ring up orders while keeping admin settings and margins protected.'
        ],
    ];
@endphp

<section id="faq" class="py-20 md:py-28 bg-secondary/25 border-b border-border/60">
    <div class="mx-auto max-w-4xl px-6 md:px-12">
        <div class="text-center mb-14">
            <span class="text-xs font-semibold uppercase tracking-wider text-accent">Questions & Answers</span>
            <h2 class="mt-2 text-3xl sm:text-4xl font-display font-normal text-foreground tracking-tight">
                Frequently Asked Questions
            </h2>
            <p class="mt-4 text-base text-muted-foreground leading-relaxed">
                Everything you need to know about setting up and running your stall with ORVIK Store.
            </p>
        </div>

        <div x-data="{ openFaq: null }" class="space-y-3">
            @foreach($faqs as $index => $faq)
                <div class="rounded-xl border border-border/80 bg-white overflow-hidden shadow-2xs transition-all">
                    <button
                        type="button"
                        @click="openFaq = (openFaq === {{ $index }} ? null : {{ $index }})"
                        class="w-full px-6 py-4.5 text-left flex items-center justify-between gap-4 font-semibold text-sm sm:text-base text-foreground focus:outline-none focus-visible:bg-secondary/40"
                        :aria-expanded="openFaq === {{ $index }}"
                    >
                        <span>{{ $faq['q'] }}</span>
                        <span class="shrink-0 rounded-full p-1 text-muted-foreground transition-transform duration-200" :class="openFaq === {{ $index }} ? 'rotate-180 text-accent' : ''">
                            <i data-lucide="chevron-down" class="w-4 h-4"></i>
                        </span>
                    </button>

                    <div
                        x-show="openFaq === {{ $index }}"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="px-6 pb-5 pt-1 text-sm text-muted-foreground leading-relaxed border-t border-border/40"
                        style="display: none;"
                    >
                        {{ $faq['a'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
