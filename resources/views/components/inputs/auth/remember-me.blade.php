@props([
    'name' => 'remember',
    'label' => 'Remember me',
    'value' => '1',
    'checked' => false,
    'containerClass' => 'flex items-center',
    'inputClass' => 'h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary-accent',
    'labelClass' => 'ml-2 block text-sm text-gray-900',
])

@php
    $checked = $checked ?: old($name);
@endphp

<div class="{{ $containerClass }}" data-remember-me>
    <input id="{{ $name }}" name="{{ $name }}" type="checkbox" value="{{ $value }}"
        @if ($checked) checked @endif class="{{ $inputClass }}"
        {{ $attributes->except(['name', 'label', 'value', 'checked', 'containerClass', 'inputClass', 'labelClass']) }}>
    <label for="{{ $name }}" class="{{ $labelClass }}">{{ __($label) }}</label>
</div>
