<x-layouts.platform title="Platform Settings & Governance" subtitle="Global trial rules, currency defaults, and maintenance controls" activeNav="settings">
    <div class="max-w-3xl mx-auto bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-6">
        <form method="POST" action="{{ route('platform.settings.update') }}" class="space-y-6">
            @csrf

            <!-- Trial & Pricing Defaults -->
            <div class="space-y-4">
                <h3 class="text-sm font-bold text-foreground border-b border-border pb-2">Trial & Monetization Defaults</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-foreground mb-1">Default Trial Duration (Days)</label>
                        <input type="number" name="default_trial_days" value="{{ $settings['default_trial_days'] }}" required min="1" max="365" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-foreground mb-1">Base Currency</label>
                        <input type="text" name="default_currency" value="{{ $settings['default_currency'] }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Default Trial Plan</label>
                    <input type="text" name="default_plan" value="{{ $settings['default_plan'] }}" required class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                    <p class="text-[10px] text-muted-foreground mt-1">Plan slug assigned to newly registered stalls (e.g. starter).</p>
                </div>
            </div>

            <!-- Access Governance -->
            <div class="space-y-4 pt-4 border-t border-border">
                <h3 class="text-sm font-bold text-foreground border-b border-border pb-2">Access Governance & System State</h3>

                <div class="space-y-3">
                    <label class="flex items-start gap-3 p-3 rounded-xl border border-border bg-secondary/30 cursor-pointer">
                        <input type="checkbox" name="registration_enabled" value="1" {{ $settings['registration_enabled'] ? 'checked' : '' }} class="mt-0.5 rounded border-border text-accent focus:ring-accent" />
                        <div>
                            <span class="text-xs font-bold text-foreground block">Public Registration Enabled</span>
                            <span class="text-[11px] text-muted-foreground">When disabled, new users cannot create business accounts from /register.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3 rounded-xl border border-rose-200 bg-rose-50/40 cursor-pointer">
                        <input type="checkbox" name="maintenance_mode" value="1" {{ $settings['maintenance_mode'] ? 'checked' : '' }} class="mt-0.5 rounded border-rose-300 text-rose-600 focus:ring-rose-500" />
                        <div>
                            <span class="text-xs font-bold text-rose-900 block">Platform Maintenance Mode</span>
                            <span class="text-[11px] text-rose-700">Display maintenance notice to tenant stores while keeping platform admin accessible.</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- AdSense Monetization -->
            <div class="space-y-4 pt-4 border-t border-border">
                <h3 class="text-sm font-bold text-foreground border-b border-border pb-2">AdSense / Advertising Controls</h3>

                <label class="flex items-center gap-2 text-xs font-medium text-foreground cursor-pointer">
                    <input type="checkbox" name="ads_enabled" value="1" {{ $settings['ads_enabled'] ? 'checked' : '' }} class="rounded border-border text-accent focus:ring-accent" />
                    <span>Enable non-intrusive banner ads on Free tier dashboard</span>
                </label>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">AdSense Publisher ID (ca-pub-XXXXXXXXXXXXXXXX)</label>
                    <input type="text" name="adsense_publisher_id" value="{{ $settings['adsense_publisher_id'] }}" placeholder="ca-pub-xxxxxxxxxxxxxxxx" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>
            </div>

            <div class="flex items-center justify-end pt-4 border-t border-border">
                <button type="submit" class="px-6 py-2 bg-accent text-accent-foreground font-bold text-xs rounded-xl hover:bg-accent/90 shadow-sm">
                    Save Platform Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.platform>
