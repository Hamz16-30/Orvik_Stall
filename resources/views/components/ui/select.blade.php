@props([
    'label' => null,
    'id' => null,
    'name' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'error' => null,
    'hint' => null,
    'disabled' => false,
    'required' => false,
])

@php
    $id = $id ?? $name ?? 'select-' . uniqid();
    $hasError = !empty($error) || ($name && $errors->has($name));
    $errorMessage = $error ?? ($name ? $errors->first($name) : null);
    $currentVal = old($name, $selected);
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
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $disabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge([
                'class' => 'block w-full min-h-[44px] appearance-none rounded-lg border bg-white px-3.5 py-2.5 pr-10 text-sm text-foreground transition-colors focus:border-accent focus:outline-none focus:ring-2 focus:ring-accent/20 disabled:cursor-not-allowed disabled:bg-muted/50 disabled:text-muted-foreground ' .
                ($hasError ? 'border-rose-500 focus:border-rose-500 focus:ring-rose-500/20' : 'border-border')
            ]) }}
        >
            @if($placeholder)
                <option value="" disabled {{ is_null($currentVal) ? 'selected' : '' }}>{{ $placeholder }}</option>
            @endif

            @foreach($options as $val => $optLabel)
                <option value="{{ $val }}" {{ (string)$currentVal === (string)$val ? 'selected' : '' }}>
                    {{ $optLabel }}
                </option>
            @endforeach
            {{ $slot }}
        </select>

        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-muted-foreground">
            <i data-lucide="chevron-down" class="h-4 w-4"></i>
        </div>
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
