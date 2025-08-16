@props([
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'disabled' => false,
    'readonly' => false,
    'rows' => 4,
    'error' => null,
])

@php
    $fieldError = $error ?? ($name ? $errors->first($name) : null);
    $baseClasses =
        'block w-full px-4 py-3 text-gray-900 placeholder-gray-500 bg-white border rounded-lg shadow-sm transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-1 resize-none';
    $errorClasses = $fieldError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20'
        : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-gray-400';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed opacity-60' : '';

    $classes = $baseClasses . ' ' . $errorClasses . ' ' . $disabledClasses;
@endphp

<textarea @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
    @if ($placeholder) placeholder="{{ __($placeholder) }}" @endif
    @if ($required) required @endif @if ($disabled) disabled @endif
    @if ($readonly) readonly @endif rows="{{ $rows }}"
    {{ $attributes->merge(['class' => $classes]) }}>{{ $value }}</textarea>
