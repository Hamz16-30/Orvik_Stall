@php
    $user = auth()->user();
@endphp

<x-layouts.app title="Settings" subtitle="Manage stall configurations, staff access, security, and account preferences" activeNav="settings">
    <div class="max-w-5xl space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <!-- Account Settings Card -->
            <a href="{{ route('app.settings.account') }}" class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs hover:shadow-xs hover:border-accent/50 transition-all flex flex-col justify-between group">
                <div>
                    <div class="w-10 h-10 rounded-xl bg-secondary text-foreground group-hover:bg-accent group-hover:text-white flex items-center justify-center transition-colors mb-4">
                        <i data-lucide="user" class="w-5 h-5"></i>
                    </div>
                    <h3 class="text-base font-bold text-foreground">Account & Security</h3>
                    <p class="text-xs text-muted-foreground mt-1.5 leading-relaxed">
                        Update your personal profile, operator email, and account password.
                    </p>
                </div>
                <div class="mt-5 pt-3 border-t border-border/50 flex items-center justify-between text-xs font-semibold text-accent">
                    <span>Manage Account</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                </div>
            </a>

            @if($user->hasPermissionTo('settings.view'))
                <!-- Business Settings Card -->
                <a href="{{ route('app.settings.business') }}" class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs hover:shadow-xs hover:border-accent/50 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-secondary text-foreground group-hover:bg-accent group-hover:text-white flex items-center justify-center transition-colors mb-4">
                            <i data-lucide="store" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-foreground">Stall & Business Profile</h3>
                        <p class="text-xs text-muted-foreground mt-1.5 leading-relaxed">
                            Configure stall title, contact details, currency symbol, and timezone.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-border/50 flex items-center justify-between text-xs font-semibold text-accent">
                        <span>Stall Settings</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endif

            @if($user->hasPermissionTo('staff.view'))
                <!-- Staff Management Card -->
                <a href="{{ route('app.settings.staff') }}" class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs hover:shadow-xs hover:border-accent/50 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-secondary text-foreground group-hover:bg-accent group-hover:text-white flex items-center justify-center transition-colors mb-4">
                            <i data-lucide="users" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-foreground">Staff & Operators</h3>
                        <p class="text-xs text-muted-foreground mt-1.5 leading-relaxed">
                            Invite team members, assign operational roles, and manage access statuses.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-border/50 flex items-center justify-between text-xs font-semibold text-accent">
                        <span>Manage Staff</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endif

            @if($user->hasPermissionTo('roles.view'))
                <!-- Roles & Permissions Card -->
                <a href="{{ route('app.settings.roles') }}" class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs hover:shadow-xs hover:border-accent/50 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-secondary text-foreground group-hover:bg-accent group-hover:text-white flex items-center justify-center transition-colors mb-4">
                            <i data-lucide="shield" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-foreground">Roles & Permissions</h3>
                        <p class="text-xs text-muted-foreground mt-1.5 leading-relaxed">
                            Review system role permissions and define custom operator access levels.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-border/50 flex items-center justify-between text-xs font-semibold text-accent">
                        <span>Role Permissions</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endif

            @if($user->hasPermissionTo('audit.view'))
                <!-- Security Audit Logs Card -->
                <a href="{{ route('app.settings.audit') }}" class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs hover:shadow-xs hover:border-accent/50 transition-all flex flex-col justify-between group">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-secondary text-foreground group-hover:bg-accent group-hover:text-white flex items-center justify-center transition-colors mb-4">
                            <i data-lucide="file-clock" class="w-5 h-5"></i>
                        </div>
                        <h3 class="text-base font-bold text-foreground">Security Audit Trail</h3>
                        <p class="text-xs text-muted-foreground mt-1.5 leading-relaxed">
                            Append-only chronological log of security, auth, and configuration changes.
                        </p>
                    </div>
                    <div class="mt-5 pt-3 border-t border-border/50 flex items-center justify-between text-xs font-semibold text-accent">
                        <span>View Audit Logs</span>
                        <i data-lucide="arrow-right" class="w-4 h-4 transition-transform group-hover:translate-x-1"></i>
                    </div>
                </a>
            @endif
        </div>
    </div>
</x-layouts.app>
