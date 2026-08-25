@php
    $currentUser = auth()->user();
@endphp

<x-layouts.app title="Roles & Permissions" subtitle="System roles and granular operator permission matrices" activeNav="settings">
    <div x-data="{ createRoleModalOpen: false }" class="space-y-6 max-w-5xl">
        <!-- Back Navigation & Top CTA -->
        <div class="flex items-center justify-between">
            <a href="{{ route('app.settings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Settings</span>
            </a>

            @if($currentUser->hasPermissionTo('roles.manage'))
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="plus" @click="createRoleModalOpen = true">
                    Create Custom Role
                </x-ui.button>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if ($errors->has('error'))
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs font-medium text-rose-800 flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                <span>{{ $errors->first('error') }}</span>
            </div>
        @endif

        <!-- Roles List -->
        <div class="space-y-6">
            @foreach($roles as $role)
                <x-ui.card>
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-border/70">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-secondary text-foreground flex items-center justify-center font-bold text-xs">
                                <i data-lucide="shield" class="w-4 h-4 text-accent"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-foreground">{{ $role->display_name }}</h3>
                                    @if($role->is_system)
                                        <x-ui.badge variant="accent" size="sm" :dot="false">System Role</x-ui.badge>
                                    @else
                                        <x-ui.badge variant="secondary" size="sm" :dot="false">Custom</x-ui.badge>
                                    @endif
                                </div>
                                <p class="text-xs text-muted-foreground mt-0.5">{{ $role->description }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground font-medium">
                                {{ $role->memberships->count() }} assigned staff
                            </span>

                            @if(!$role->is_system && $currentUser->hasPermissionTo('roles.manage'))
                                <form method="POST" action="{{ route('app.settings.roles.destroy', $role->id) }}" onsubmit="return confirm('Are you sure you want to delete this custom role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-600 hover:text-rose-700 rounded hover:bg-rose-50" title="Delete Role">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Role Permissions Matrix -->
                    <div class="mt-4">
                        <h4 class="text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-3">Assigned Permissions</h4>

                        @if($role->name === 'owner')
                            <div class="p-3 bg-secondary/40 rounded-lg text-xs text-foreground font-medium flex items-center gap-2">
                                <i data-lucide="check" class="w-4 h-4 text-emerald-600"></i>
                                <span>Owner has full master permissions across all modules.</span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('app.settings.roles.update', $role->id) }}">
                                @csrf
                                @method('PUT')

                                <input type="hidden" name="display_name" value="{{ $role->display_name }}">
                                <input type="hidden" name="description" value="{{ $role->description }}">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    @foreach($groupedPermissions as $module => $perms)
                                        <div class="p-3 rounded-xl bg-secondary/30 border border-border/60 space-y-2">
                                            <h5 class="text-xs font-bold text-foreground uppercase tracking-wider">{{ ucfirst($module) }}</h5>
                                            <div class="space-y-1.5">
                                                @foreach($perms as $p)
                                                    <label class="flex items-start gap-2 text-xs text-foreground cursor-pointer select-none">
                                                        <input
                                                            type="checkbox"
                                                            name="permissions[]"
                                                            value="{{ $p->id }}"
                                                            class="rounded border-border text-accent focus:ring-accent mt-0.5"
                                                            {{ $role->permissions->contains('id', $p->id) ? 'checked' : '' }}
                                                            {{ $role->name === 'owner' ? 'disabled' : '' }}
                                                        />
                                                        <div>
                                                            <p class="font-medium leading-none">{{ $p->display_name }}</p>
                                                            <p class="text-[10px] text-muted-foreground mt-0.5">{{ $p->description }}</p>
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                @if($currentUser->hasPermissionTo('roles.manage') && $role->name !== 'owner')
                                    <div class="mt-4 pt-3 border-t border-border flex justify-end">
                                        <x-ui.button type="submit" variant="primary" size="sm" rounded="lg">
                                            Save Permission Changes
                                        </x-ui.button>
                                    </div>
                                @endif
                            </form>
                        @endif
                    </div>
                </x-ui.card>
            @endforeach
        </div>

        <!-- Create Role Modal -->
        <div x-show="createRoleModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="createRoleModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-lg w-full p-6 text-left" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border/60">
                        <h3 class="text-base font-bold text-foreground">Create Custom Role</h3>
                        <button type="button" @click="createRoleModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('app.settings.roles.store') }}" class="mt-4 space-y-4">
                        @csrf

                        <x-ui.input
                            label="Role Identifier (slug)"
                            name="name"
                            placeholder="e.g. inventory_lead"
                            required
                        />

                        <x-ui.input
                            label="Display Name"
                            name="display_name"
                            placeholder="e.g. Inventory Lead"
                            required
                        />

                        <x-ui.textarea
                            label="Role Description"
                            name="description"
                            rows="2"
                            placeholder="Describe what this role is responsible for..."
                        />

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="createRoleModalOpen = false">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">
                                Create Role
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
