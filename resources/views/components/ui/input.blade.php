@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => '',
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'required' => false,
    'icon' => null,
    'suffix' => null,
])

@php
    $id = $id ?? $name ?? 'input-' . uniqid();
    $hasError = !empty($error) || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
@endphp

<div class="w-full space-y-1.5">
    @if($label)
        <label for="{{ $id }}" class="block text-xs font-semibold uppercase tracking-wider text-foreground/80">
            {{ $label }}
            @if($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative rounded-lg shadow-sm">
        @if($icon)
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-muted-foreground">
                <i data-lucide="{{ $icon }}" class="h-4 w-4"></i>
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => 'block w-full min-h-[44px] rounded-lg border bg-white px-3.5 py-2.5 text-sm text-foreground placeholder:text-muted-foreground/70 transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20 disabled:cursor-not-allowed disabled:bg-muted/50 disabled:text-muted-foreground ' .
                ($icon ? 'pl-10 ' : '') .
                ($suffix ? 'pr-10 ' : '') .
                ($hasError ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' : 'border-border')
            ]) }}
        />

        @if($suffix)
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-muted-foreground">
                {{ $suffix }}
            </div>
        @endif
    </div>

    @if($errorMessage)
        <p class="text-xs text-rose-600 flex items-center gap-1 font-medium mt-1">
            <i data-lucide="alert-circle" class="w-3.5 h-3.5 shrink-0"></i>
            <span>{{ $errorMessage }}</span>
        </p>
    @elseif($hint)
        <p class="text-xs text-muted-foreground mt-1">{{ $hint }}</p>
    @endif
</div>
