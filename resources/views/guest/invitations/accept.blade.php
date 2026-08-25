<x-layouts.guest title="Join Stall Team" subtitle="You've been invited to join {{ $invitation->business->name }} as {{ $invitation->role->display_name }}">
    <div class="mb-5 p-3 rounded-lg bg-accent/10 border border-accent/20 text-xs text-foreground flex items-center gap-2">
        <i data-lucide="shield" class="w-4 h-4 text-accent shrink-0"></i>
        <span>Invited by <strong>{{ $invitation->inviter->name }}</strong> ({{ $invitation->inviter->email }})</span>
    </div>

    <form method="POST" action="{{ route('invitations.process', ['token' => $invitation->token]) }}" class="space-y-4">
        @csrf

        <x-ui.input
            label="Email Address"
            type="email"
            name="email"
            value="{{ $invitation->email }}"
            disabled
            icon="mail"
        />

        <x-ui.input
            label="Your Full Name"
            type="text"
            name="name"
            placeholder="e.g. Sara Khan"
            value="{{ old('name') }}"
            icon="user"
            required
            autofocus
        />

        <x-ui.input
            label="Create Password"
            type="password"
            name="password"
            placeholder="••••••••"
            icon="lock"
            required
        />

        <x-ui.input
            label="Confirm Password"
            type="password"
            name="password_confirmation"
            placeholder="••••••••"
            icon="lock"
            required
        />

        <div class="pt-2">
            <x-ui.button type="submit" variant="accent" size="md" rounded="lg" class="w-full justify-center">
                Accept Invitation & Join Stall
            </x-ui.button>
        </div>
    </form>
</x-layouts.guest>
