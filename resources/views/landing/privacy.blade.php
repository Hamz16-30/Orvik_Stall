<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy — ORVIK Store</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-background text-foreground font-body antialiased selection:bg-accent/20 selection:text-accent">
    <x-navigation.marketing-navbar />

    <main class="py-16 md:py-24 px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-8">
        <div class="space-y-3">
            <h1 class="text-3xl md:text-5xl font-normal font-serif tracking-tight text-foreground">Privacy Policy</h1>
            <p class="text-xs text-muted-foreground">Last Updated: August 16, 2026</p>
        </div>

        <div class="prose prose-sm max-w-none text-foreground/80 space-y-6 text-xs leading-relaxed">
            <section class="space-y-2">
                <h2 class="text-sm font-bold text-foreground">1. Information We Collect</h2>
                <p>ORVIK Store collects information you provide directly when registering for an account, configuring your retail stall, completing POS sales transactions, and managing staff accounts. We collect names, email addresses, business metadata, product inventory records, and financial transaction logs necessary to operate your business management software.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm font-bold text-foreground">2. Multi-Tenant Data Isolation</h2>
                <p>All business inventory, sales, customer, supplier, and financial records are strictly tenant-isolated using database-level tenancy constraints (scoped by your unique business ID). No other stall owner or unauthorized user can access your store data.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm font-bold text-foreground">3. Payment & Billing Data Security</h2>
                <p>ORVIK Store does not store full credit card numbers, CVVs, or sensitive payment credentials on our servers. All billing transactions are processed through tokenized payment providers and authenticated webhooks.</p>
            </section>

            <section class="space-y-2">
                <h2 class="text-sm font-bold text-foreground">4. Data Retention & Account Lifecycle</h2>
                <p>Your business records (products, sales, customers, receipts) are never automatically deleted upon plan expiration or cancellation. You may export your records at any time in standard formats (CSV, Excel).</p>
            </section>
        </div>
    </main>

    <footer class="border-t border-border bg-white py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
        <p>&copy; {{ date('Y') }} ORVIK Store. All rights reserved.</p>
        <div class="flex items-center gap-6 font-medium">
            <a href="{{ route('pricing') }}" class="hover:text-foreground">Pricing</a>
            <a href="{{ route('privacy') }}" class="hover:text-foreground">Privacy Policy</a>
            <a href="{{ route('terms') }}" class="hover:text-foreground">Terms of Service</a>
            <a href="{{ route('cookie.policy') }}" class="hover:text-foreground">Cookie Policy</a>
        </div>
    </footer>
</body>
</html>
