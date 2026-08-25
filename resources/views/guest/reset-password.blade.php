<x-layouts.guest title="Create New Password" subtitle="Choose a strong new password for your account">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <x-ui.input
            label="Email Address"
            type="email"
            name="email"
            id="email"
            value="{{ old('email', $email) }}"
            icon="mail"
            required
        />

        <x-ui.input
            label="New Password"
            type="password"
            name="password"
            id="password"
            placeholder="••••••••"
            icon="lock"
            required
            autofocus
        />

        <x-ui.input
            label="Confirm New Password"
            type="password"
            name="password_confirmation"
            id="password_confirmation"
            placeholder="••••••••"
            icon="lock"
            required
        />

        <div class="pt-2">
            <x-ui.button type="submit" variant="primary" size="md" rounded="lg" class="w-full justify-center">
                Reset Password & Sign In
            </x-ui.button>
        </div>
    </form>
</x-layouts.guest>
