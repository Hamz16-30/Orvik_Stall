<x-layouts.guest title="Sign In to ORVIK Store" subtitle="Access your stall register, inventory, and operations">
    @if (session('status'))
        <div class="mb-4 text-xs font-medium text-emerald-700 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    @if (session('info'))
        <div class="mb-4 text-xs font-medium text-sky-700 bg-sky-50 p-3 rounded-lg border border-sky-200">
            {{ session('info') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <x-ui.input
            label="Operator Email"
            type="email"
            name="email"
            id="email"
            placeholder="operator@orvikstore.com"
            value="{{ old('email', 'owner@orvikstore.com') }}"
            icon="mail"
            required
            autofocus
        />

        <x-ui.input
            label="Password"
            type="password"
            name="password"
            id="password"
            placeholder="••••••••"
            value="password"
            icon="lock"
            required
        />

        <div class="flex items-center justify-between text-xs">
            <label class="flex items-center gap-2 cursor-pointer select-none text-muted-foreground">
                <input type="checkbox" name="remember" class="rounded border-border text-accent focus:ring-accent" {{ old('remember') ? 'checked' : '' }}>
                <span>Remember this stall device</span>
            </label>
            <a href="{{ route('password.request') }}" class="text-accent font-semibold hover:underline">Forgot password?</a>
        </div>

        <div class="pt-2">
            <x-ui.button type="submit" variant="primary" size="md" rounded="lg" class="w-full justify-center">
                Sign In to Stall
            </x-ui.button>
        </div>

        <div class="pt-4 text-center text-xs text-muted-foreground border-t border-border/60">
            Don't have a stall account?
            <a href="{{ route('register') }}" class="font-semibold text-accent hover:underline ml-1">Create Stall Account</a>
        </div>
    </form>
</x-layouts.guest>
