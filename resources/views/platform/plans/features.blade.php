<x-layouts.platform title="Feature Matrix: {{ $plan->name }}" subtitle="Configure enabled capabilities and quota limits for this plan" activeNav="plans">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('platform.plans.index') }}" class="text-xs text-muted-foreground hover:text-foreground flex items-center gap-1 mb-1">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Back to Plans</span>
                </a>
                <h2 class="text-base font-bold text-foreground">Configure Features for {{ $plan->name }} (Rs. {{ number_format($plan->price) }}/mo)</h2>
            </div>
        </div>

        <form method="POST" action="{{ route('platform.plans.features.update', $plan->id) }}" class="space-y-6">
            @csrf

            <div class="bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead class="bg-secondary/70 border-b border-border text-[11px] uppercase tracking-wider text-muted-foreground font-semibold">
                            <tr>
                                <th class="px-5 py-3.5">Feature Name</th>
                                <th class="px-5 py-3.5">Type</th>
                                <th class="px-5 py-3.5 text-center">Enabled</th>
                                <th class="px-5 py-3.5">Limit / Quota Value</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border/60 text-foreground">
                            @foreach($features as $feature)
                                @php
                                    $pivot = $plan->features->firstWhere('id', $feature->id)?->pivot;
                                    $isEnabled = $pivot ? (bool)$pivot->enabled : false;
                                    $limitVal = $pivot ? $pivot->limit_value : null;
                                @endphp
                                <tr class="hover:bg-muted/20 transition-colors">
                                    <td class="px-5 py-3.5">
                                        <p class="font-bold text-foreground">{{ $feature->name }}</p>
                                        <p class="text-[11px] text-muted-foreground">{{ $feature->description }}</p>
                                        <span class="text-[10px] text-muted-foreground font-mono">slug: {{ $feature->slug }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $feature->type === 'BOOLEAN' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $feature->type }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 text-center">
                                        <input
                                            type="checkbox"
                                            name="features[{{ $feature->id }}][enabled]"
                                            value="1"
                                            {{ $isEnabled ? 'checked' : '' }}
                                            class="w-4 h-4 rounded border-border text-accent focus:ring-accent"
                                        />
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if($feature->type !== 'BOOLEAN')
                                            <div class="flex items-center gap-2">
                                                <input
                                                    type="number"
                                                    step="1"
                                                    name="features[{{ $feature->id }}][limit_value]"
                                                    value="{{ $limitVal !== null ? (int)$limitVal : '' }}"
                                                    placeholder="Leave empty for unlimited (∞)"
                                                    class="w-48 px-3 py-1.5 text-xs rounded-xl border border-border bg-background text-foreground"
                                                />
                                                <span class="text-[10px] text-muted-foreground">Empty = Unlimited</span>
                                            </div>
                                        @else
                                            <span class="text-[11px] text-muted-foreground italic">Boolean flag (Yes / No)</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t border-border/70 flex items-center justify-end gap-3">
                    <a href="{{ route('platform.plans.index') }}" class="px-4 py-2 text-xs font-semibold text-muted-foreground hover:text-foreground">Cancel</a>
                    <button type="submit" class="px-5 py-2 bg-accent text-accent-foreground font-bold text-xs rounded-xl hover:bg-accent/90 shadow-sm">
                        Save Feature Matrix
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-layouts.platform>
