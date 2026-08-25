<x-layouts.platform title="Feature Flags & Capabilities" subtitle="Define capabilities, feature flags, and quota dimensions" activeNav="features">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Create Feature Form -->
        <div class="bg-white p-6 rounded-2xl border border-border/80 shadow-2xs space-y-4">
            <h3 class="text-sm font-bold text-foreground">Add New Feature</h3>
            <form method="POST" action="{{ route('platform.features.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Feature Name</label>
                    <input type="text" name="name" required placeholder="e.g. Barcode Scanner" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Slug (optional)</label>
                    <input type="text" name="slug" placeholder="e.g. barcode_scanner" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground" />
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Feature Type</label>
                    <select name="type" class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground">
                        <option value="BOOLEAN">BOOLEAN (Yes / No toggle)</option>
                        <option value="LIMIT">LIMIT (Max items count)</option>
                        <option value="QUOTA">QUOTA (Monthly consumption)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-foreground mb-1">Description</label>
                    <textarea name="description" rows="2" placeholder="Describe this feature..." class="w-full px-3 py-2 text-xs rounded-xl border border-border bg-background text-foreground"></textarea>
                </div>

                <button type="submit" class="w-full py-2 bg-accent text-accent-foreground font-bold text-xs rounded-xl hover:bg-accent/90">
                    Add Feature
                </button>
            </form>
        </div>

        <!-- Features Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-border/80 shadow-2xs overflow-hidden">
            <div class="p-4 border-b border-border/70">
                <h3 class="text-sm font-bold text-foreground">System Feature Definitions</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-secondary/70 border-b border-border text-[11px] uppercase tracking-wider text-muted-foreground font-semibold">
                        <tr>
                            <th class="px-5 py-3.5">Feature</th>
                            <th class="px-5 py-3.5">Slug</th>
                            <th class="px-5 py-3.5">Type</th>
                            <th class="px-5 py-3.5">Plans Attached</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border/60 text-foreground">
                        @foreach($features as $feature)
                            <tr class="hover:bg-muted/20 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-bold text-foreground">{{ $feature->name }}</p>
                                    <p class="text-[11px] text-muted-foreground">{{ $feature->description }}</p>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-muted-foreground">
                                    {{ $feature->slug }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold {{ $feature->type === 'BOOLEAN' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                        {{ $feature->type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 font-mono text-muted-foreground">
                                    {{ $feature->plans_count }} plans
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.platform>
