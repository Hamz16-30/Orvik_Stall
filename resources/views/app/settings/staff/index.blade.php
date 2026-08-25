@php
    $currentUser = auth()->user();
@endphp

<x-layouts.app title="Staff & Operators" subtitle="Manage stall employees, assign operational roles, and invite team members" activeNav="settings">
    <div x-data="{ inviteModalOpen: false, editRoleModalOpen: false, selectedMember: null, selectedRoleId: null }" class="space-y-6 max-w-5xl">
        <!-- Back Navigation & Top CTA -->
        <div class="flex items-center justify-between">
            <a href="{{ route('app.settings') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-muted-foreground hover:text-foreground">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                <span>Back to Settings</span>
            </a>

            @if($currentUser->hasPermissionTo('staff.manage'))
                <x-ui.button variant="accent" size="sm" rounded="lg" icon="user-plus" @click="inviteModalOpen = true">
                    Invite Staff Member
                </x-ui.button>
            @endif
        </div>

        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800 flex items-center gap-2">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('info'))
            <div class="p-4 rounded-xl bg-sky-50 border border-sky-200 text-xs font-medium text-sky-800 flex items-center gap-2">
                <i data-lucide="info" class="w-4 h-4 text-sky-600"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        <!-- Active Staff Members Table -->
        <x-ui.card title="Stall Operators & Staff ({{ $staffMembers->count() }})" description="Active accounts assigned to this business stall">
            <x-ui.table :headers="['Operator Name', 'Email Address', 'Assigned Role', 'Status', 'Joined Date', 'Actions']">
                @foreach($staffMembers as $member)
                    @php
                        $isOwner = (int)$business->owner_id === (int)$member->user->id;
                        $isSelf = (int)$currentUser->id === (int)$member->user->id;
                    @endphp
                    <tr class="hover:bg-muted/20 transition-colors">
                        <td class="px-5 py-4 font-semibold text-foreground flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full bg-secondary font-bold text-xs flex items-center justify-center text-foreground">
                                {{ strtoupper(substr($member->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="text-xs font-bold text-foreground">{{ $member->user->name }}</p>
                                @if($isOwner)
                                    <span class="text-[10px] text-accent font-semibold">Store Owner</span>
                                @endif
                            </div>
                        </td>

                        <td class="px-5 py-4 text-xs text-muted-foreground">
                            {{ $member->user->email }}
                        </td>

                        <td class="px-5 py-4">
                            <x-ui.badge variant="accent" size="sm" :dot="false">
                                {{ $member->role->display_name }}
                            </x-ui.badge>
                        </td>

                        <td class="px-5 py-4">
                            @if($member->status === 'active')
                                <x-ui.badge variant="active" size="sm">Active</x-ui.badge>
                            @elseif($member->status === 'suspended')
                                <x-ui.badge variant="destructive" size="sm">Suspended</x-ui.badge>
                            @else
                                <x-ui.badge variant="secondary" size="sm">{{ ucfirst($member->status) }}</x-ui.badge>
                            @endif
                        </td>

                        <td class="px-5 py-4 text-xs text-muted-foreground">
                            {{ $member->joined_at ? $member->joined_at->format('M d, Y') : 'N/A' }}
                        </td>

                        <td class="px-5 py-4 text-xs">
                            @if($currentUser->hasPermissionTo('staff.manage') && !$isOwner)
                                <div class="flex items-center gap-2">
                                    <!-- Change Role Button -->
                                    <button
                                        type="button"
                                        @click="selectedMember = {{ $member->user->id }}; selectedRoleId = {{ $member->role_id }}; editRoleModalOpen = true"
                                        class="p-1 rounded text-muted-foreground hover:text-accent"
                                        title="Change Role"
                                    >
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>

                                    @if(!$isSelf)
                                        <!-- Suspend / Reactivate Form -->
                                        <form method="POST" action="{{ route('app.settings.staff.suspend', $member->user->id) }}" onsubmit="return confirm('Are you sure you want to change this member status?');">
                                            @csrf
                                            @method('PUT')
                                            <button
                                                type="submit"
                                                class="p-1 rounded {{ $member->status === 'suspended' ? 'text-emerald-600 hover:text-emerald-700' : 'text-amber-600 hover:text-amber-700' }}"
                                                title="{{ $member->status === 'suspended' ? 'Unsuspend' : 'Suspend' }}"
                                            >
                                                <i data-lucide="{{ $member->status === 'suspended' ? 'unlock' : 'lock' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>

                                        <!-- Deactivate Form -->
                                        <form method="POST" action="{{ route('app.settings.staff.deactivate', $member->user->id) }}" onsubmit="return confirm('Are you sure you want to deactivate this operator?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1 rounded text-rose-600 hover:text-rose-700" title="Deactivate">
                                                <i data-lucide="user-x" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @else
                                <span class="text-[11px] text-muted-foreground italic">Protected</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        <!-- Pending Invitations Section -->
        @if($invitations->count() > 0)
            <x-ui.card title="Pending Team Invitations ({{ $invitations->count() }})" description="Invitations awaiting staff signup">
                <x-ui.table :headers="['Invited Email', 'Target Role', 'Expires', 'Direct Link', 'Cancel']">
                    @foreach($invitations as $inv)
                        <tr class="hover:bg-muted/20 transition-colors text-xs">
                            <td class="px-5 py-3.5 font-semibold text-foreground">
                                {{ $inv->email }}
                            </td>
                            <td class="px-5 py-3.5">
                                <x-ui.badge variant="secondary" size="sm" :dot="false">
                                    {{ $inv->role->display_name }}
                                </x-ui.badge>
                            </td>
                            <td class="px-5 py-3.5 text-muted-foreground">
                                {{ $inv->expires_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-3.5">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ route('invitations.accept', ['token' => $inv->token]) }}"
                                    class="text-[10px] font-mono bg-secondary/50 border border-border px-2 py-1 rounded w-52 select-all focus:outline-none"
                                />
                            </td>
                            <td class="px-5 py-3.5">
                                <form method="POST" action="{{ route('app.settings.invitations.cancel', $inv->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:underline font-semibold text-xs">
                                        Cancel
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </x-ui.table>
            </x-ui.card>
        @endif

        <!-- Invite Staff Modal -->
        <div x-show="inviteModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="inviteModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border/60">
                        <h3 class="text-base font-bold text-foreground">Invite Staff Member</h3>
                        <button type="button" @click="inviteModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('app.settings.staff.invite') }}" class="mt-4 space-y-4">
                        @csrf
                        <x-ui.input
                            label="Staff Member Email"
                            type="email"
                            name="email"
                            id="invite_email"
                            placeholder="employee@domain.com"
                            required
                        />

                        <x-ui.select
                            label="Assign Role"
                            name="role_id"
                            id="invite_role"
                            required
                        >
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->display_name }} &bull; {{ $r->description }}</option>
                            @endforeach
                        </x-ui.select>

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="inviteModalOpen = false">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="accent" size="sm" rounded="lg">
                                Send Invitation
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Role Modal -->
        <div x-show="editRoleModalOpen" x-cloak class="relative z-50">
            <div class="fixed inset-0 bg-foreground/40 backdrop-blur-xs" @click="editRoleModalOpen = false"></div>
            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl border border-border shadow-2xl max-w-md w-full p-6 text-left" @click.stop>
                    <div class="flex items-center justify-between pb-3 border-b border-border/60">
                        <h3 class="text-base font-bold text-foreground">Change Staff Role</h3>
                        <button type="button" @click="editRoleModalOpen = false" class="text-muted-foreground hover:text-foreground">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <form :action="'{{ url('app/settings/staff') }}/' + selectedMember + '/role'" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <x-ui.select
                            label="Select New Role"
                            name="role_id"
                            id="edit_role_id"
                            required
                        >
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}">{{ $r->display_name }} &bull; {{ $r->description }}</option>
                            @endforeach
                        </x-ui.select>

                        <div class="pt-3 border-t border-border flex items-center justify-end gap-3">
                            <x-ui.button type="button" variant="ghost" size="sm" @click="editRoleModalOpen = false">
                                Cancel
                            </x-ui.button>
                            <x-ui.button type="submit" variant="primary" size="sm" rounded="lg">
                                Update Role
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
