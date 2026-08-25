<x-layouts.app title="Account & Security" subtitle="Manage your personal profile and account credentials" activeNav="settings">
    <div class="max-w-3xl space-y-6">
        <!-- Back Navigation -->
        <a href="{{ route('app.settings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span>Back to Settings</span>
        </a>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <!-- Profile Section -->
        <x-ui.card title="Operator Profile" description="Your name and account email used for sign-in">
            <form method="POST" action="{{ route('app.settings.account.profile') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-ui.input
                    label="Full Name"
                    name="name"
                    id="name"
                    value="{{ old('name', $user->name) }}"
                    required
                />

                <x-ui.input
                    label="Email Address"
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email', $user->email) }}"
                    required
                />

                <div class="pt-4 border-t border-border flex justify-end">
                    <x-ui.button type="submit" variant="primary" size="md" rounded="lg">
                        Update Profile
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>

        <!-- Change Password Section -->
        <x-ui.card title="Change Password" description="Ensure your account is using a long, random password for security">
            <form method="POST" action="{{ route('app.settings.account.password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-ui.input
                    label="Current Password"
                    type="password"
                    name="current_password"
                    id="current_password"
                    placeholder="••••••••"
                    required
                />

                <x-ui.input
                    label="New Password"
                    type="password"
                    name="password"
                    id="password"
                    placeholder="••••••••"
                    required
                />

                <x-ui.input
                    label="Confirm New Password"
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="••••••••"
                    required
                />

                <div class="pt-4 border-t border-border flex justify-end">
                    <x-ui.button type="submit" variant="primary" size="md" rounded="lg">
                        Change Password
                    </x-ui.button>
                </div>
            </form>
        </x-ui.card>
    </div>
</x-layouts.app>
