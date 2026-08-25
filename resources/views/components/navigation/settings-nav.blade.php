@props([
    'active' => 'index'
])

<div class="flex items-center gap-1.5 overflow-x-auto pb-2 border-b border-border/70 text-xs font-semibold select-none scrollbar-none">
    <a href="{{ route('app.settings') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'index' ? 'bg-white border border-border text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
        Overview
    </a>

    <a href="{{ route('app.settings.plan') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'plan' ? 'bg-white border border-border text-accent font-bold shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
        Plan & Usage
    </a>

    <a href="{{ route('app.settings.billing') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'billing' ? 'bg-white border border-border text-accent font-bold shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
        Billing & Invoices
    </a>

    <a href="{{ route('app.settings.account') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'account' ? 'bg-white border border-border text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
        Account & Password
    </a>

    @if(auth()->user()->hasPermissionTo('settings.view'))
        <a href="{{ route('app.settings.business') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'business' ? 'bg-white border border-border text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
            Business Settings
        </a>
    @endif

    @if(auth()->user()->hasPermissionTo('staff.view'))
        <a href="{{ route('app.settings.staff') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'staff' ? 'bg-white border border-border text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
            Staff & Operators
        </a>
    @endif

    @if(auth()->user()->hasPermissionTo('roles.view'))
        <a href="{{ route('app.settings.roles') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'roles' ? 'bg-white border border-border text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
            Roles
        </a>
    @endif

    @if(auth()->user()->hasPermissionTo('audit.view'))
        <a href="{{ route('app.settings.audit') }}" class="px-3.5 py-2 rounded-xl transition-all whitespace-nowrap {{ $active === 'audit' ? 'bg-white border border-border text-foreground shadow-2xs' : 'text-muted-foreground hover:text-foreground hover:bg-white/50' }}">
            Audit Trail
        </a>
    @endif
</div>
