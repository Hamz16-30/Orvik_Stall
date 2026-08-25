@props([
    'headers' => [],
    'striped' => false,
    'emptyMessage' => 'No records found.',
])

<div class="w-full overflow-hidden rounded-xl border border-border/80 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full text-left border-collapse text-sm']) }}>
            @if(count($headers) > 0 || isset($thead))
                <thead class="bg-secondary/70 border-b border-border text-xs uppercase tracking-wider text-muted-foreground font-semibold">
                    @if(isset($thead))
                        {{ $thead }}
                    @else
                        <tr>
                            @foreach($headers as $header)
                                <th scope="col" class="px-5 py-3.5 whitespace-nowrap">{{ $header }}</th>
                            @endforeach
                        </tr>
                    @endif
                </thead>
            @endif

            <tbody class="divide-y divide-border/60 text-foreground">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
