@props([
    'name' => null,
    'value' => '1',
    'checked' => false,
    'required' => false,
    'disabled' => false,
    'label' => null,
    'description' => null,
])

@php
    $fieldId = $name ?? 'checkbox_' . uniqid();
    $checkboxClasses = 'h-5 w-5 text-indigo-600 rounded border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500/20 focus:ring-offset-1 transition-colors duration-200';
    $labelClasses = 'ml-3 text-sm font-medium text-gray-900 cursor-pointer';
    $descriptionClasses = 'ml-8 text-sm text-gray-600 mt-1';
@endphp

<div class="flex items-start py-2">
    <div class="flex items-center h-6">
        <input type="checkbox"
            @if ($name) name="{{ $name }}" id="{{ $fieldId }}" @endif
            value="{{ $value }}" 
            @if ($checked) checked @endif
            @if ($required) required @endif 
            @if ($disabled) disabled @endif
            {{ $attributes->merge(['class' => $checkboxClasses]) }} />
    </div>

    @if ($label)
        <div class="flex-1">
            <label for="{{ $fieldId }}" class="{{ $labelClasses }}">
                {{ __($label) }} 
            </label>
            @if ($description)
                <p class="{{ $descriptionClasses }}">{{ __($description) }}</p>
            @endif
        </div>
    @endif
</div>