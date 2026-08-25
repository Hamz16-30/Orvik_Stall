<x-layouts.guest title="Register New Stall" subtitle="Start managing your stall inventory, POS, and sales">
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <x-ui.input
            label="Your Full Name"
            type="text"
            name="name"
            id="name"
            placeholder="e.g. Tariq Mehmood"
            value="{{ old('name') }}"
            icon="user"
            required
            autofocus
        />

        <x-ui.input
            label="Stall / Business Name"
            type="text"
            name="business_name"
            id="business_name"
            placeholder="e.g. Main Stall #04"
            value="{{ old('business_name') }}"
            icon="store"
            required
        />

        <x-ui.input
            label="Email Address"
            type="email"
            name="email"
            id="email"
            placeholder="vendor@orvikstore.com"
            value="{{ old('email') }}"
            icon="mail"
            required
        />

        <x-ui.input
            label="Master Password"
            type="password"
            name="password"
            id="password"
            placeholder="••••••••"
            icon="lock"
            required
        />

        <x-ui.input
            label="Confirm Password"
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            placeholder="••••••••"
            icon="lock"
            required
        />

        <div class="pt-2">
            <x-ui.button type="submit" variant="accent" size="md" rounded="lg" class="w-full justify-center">
                Create Stall Account (Owner)
            </x-ui.button>
        </div>

        <div class="pt-4 text-center text-xs text-muted-foreground border-t border-border/60">
            Already registered?
            <a href="{{ route('login') }}" class="font-semibold text-accent hover:underline ml-1">Sign In</a>
        </div>
    </form>
</x-layouts.guest>
