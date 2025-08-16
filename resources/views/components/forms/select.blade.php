@props([
    'name' => null,
    'value' => null,
    'required' => false,
    'disabled' => false,
    'error' => null,
    'placeholder' => null,
    'options' => [],
])

@php
    $fieldError = $error ?? ($name ? $errors->first($name) : null);
    $baseClasses =
        'block w-full px-4 py-3 pr-10 text-gray-900 bg-white border rounded-lg shadow-sm transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-1 appearance-none';
    $errorClasses = $fieldError
        ? 'border-red-300 focus:border-red-500 focus:ring-red-500/20'
        : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500/20 hover:border-gray-400';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed opacity-60' : '';

    $classes = $baseClasses . ' ' . $errorClasses . ' ' . $disabledClasses;
@endphp

<div class="relative">
    <select @if ($name) name="{{ $name }}" id="{{ $name }}" @endif
        @if ($required) required @endif @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => $classes]) }}>
        @if ($placeholder)
            <option value="" class="text-gray-500">{{ __($placeholder) }}</option>
        @endif

        @if (count($options) > 0)
            @foreach ($options as $optValue => $optLabel)
                <option value="{{ $optValue }}" @if ($value == $optValue) selected @endif>
                    {{ __($optLabel) }}
                </option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>

    <!-- Custom dropdown arrow -->
    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </div>
</div>
