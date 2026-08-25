<x-layouts.marketing
    title="ORVIK Store — Simple Stall Management Software"
    description="Manage stall sales, inventory, stock levels, and business reports with ORVIK Store."
    activeNav="home"
>
    {{-- 1. Hero Section with Background Video and Coded Dashboard Preview --}}
    @include('landing.components.hero')

    {{-- 2. Core Features Section --}}
    @include('landing.components.features')

    {{-- 3. How It Works (3 Steps) --}}
    @include('landing.components.how-it-works')

    {{-- 4. Deep Product Experience & Live POS/Inventory Showcase --}}
    @include('landing.components.product-showcase')

    {{-- 5. Transparent Early-Access Pricing --}}
    @include('landing.components.pricing')

    {{-- 6. Business Data Privacy & Security --}}
    @include('landing.components.security')

    {{-- 7. Interactive Accordion FAQ --}}
    @include('landing.components.faq')

    {{-- 8. Final Conversion CTA --}}
    @include('landing.components.final-cta')

    {{-- 9. Clean Minimal Footer --}}
    @include('landing.components.footer')
</x-layouts.marketing>
