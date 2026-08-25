@props([
    'tabs' => [],
    'defaultTab' => null,
])

@php
    $defaultTab = $defaultTab ?? (!empty($tabs) ? array_key_first($tabs) : 'tab1');
@endphp

<div x-data="{ activeTab: '{{ $defaultTab }}' }" class="w-full">
    <div class="border-b border-border/80 flex items-center gap-2 overflow-x-auto no-scrollbar">
        @foreach($tabs as $tabKey => $tabLabel)
            <button
                type="button"
                x-on:click="activeTab = '{{ $tabKey }}'"
                :class="activeTab === '{{ $tabKey }}'
                    ? 'border-accent text-accent font-semibold'
                    : 'border-transparent text-muted-foreground hover:text-foreground hover:border-border font-medium'"
                class="px-4 py-2.5 text-sm border-b-2 whitespace-nowrap transition-all focus:outline-none"
            >
                {{ $tabLabel }}
            </button>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $slot }}
    </div>
</div>
