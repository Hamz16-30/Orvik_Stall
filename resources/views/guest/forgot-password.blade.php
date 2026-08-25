<x-layouts.guest title="Reset Stall Password" subtitle="Enter your email to receive password recovery instructions">
    @if (session('status'))
        <div class="mb-4 text-xs font-medium text-emerald-700 bg-emerald-50 p-3 rounded-lg border border-emerald-200">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
        @csrf

        <x-ui.input
            label="Account Email"
            type="email"
            name="email"
            id="email"
            placeholder="operator@orvikstore.com"
            value="{{ old('email') }}"
            icon="mail"
            required
            autofocus
        />

        <div class="pt-2">
            <x-ui.button type="submit" variant="primary" size="md" rounded="lg" class="w-full justify-center">
                Send Reset Link
            </x-ui.button>
        </div>

        <div class="pt-4 text-center text-xs text-muted-foreground border-t border-border/60">
            Remember your credentials?
            <a href="{{ route('login') }}" class="font-semibold text-accent hover:underline ml-1">Back to Login</a>
        </div>
    </form>
</x-layouts.guest>
