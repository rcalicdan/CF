@props([
    'type' => 'text',
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'error' => null,
    'icon' => null,
])

@php
    $fieldError = $error ?? ($name ? $errors->first($name) : null);
    $baseClasses =
        'block w-full px-4 py-3 text-gray-900 placeholder-gray-500 bg-white border rounded-lg shadow-sm transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-1';
    $errorClasses = $fieldError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20'
        : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-gray-400';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed opacity-60' : '';
    $readonlyClasses = $readonly ? 'bg-gray-50 cursor-default' : '';

    $classes = $baseClasses . ' ' . $errorClasses . ' ' . $disabledClasses . ' ' . $readonlyClasses;
@endphp

<div class="relative">
    @if ($icon)
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $icon !!}
            </svg>
        </div>
    @endif

    <input type="{{ $type }}"
        @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        @if ($value !== null) value="{{ $value }}" @endif
        @if ($placeholder) placeholder="{{ __($placeholder) }}" @endif
        @if ($required) required @endif @if ($disabled) disabled @endif
        @if ($readonly) readonly @endif
        {{ $attributes->merge(['class' => $classes . ($icon ? ' pl-10' : '')]) }} />
</div>
