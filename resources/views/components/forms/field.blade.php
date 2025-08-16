@props([
    'label' => null,
    'name' => null,
    'required' => false,
    'error' => null,
    'help' => null,
    'labelClass' => '',
    'wrapperClass' => '',
    'errorClass' => '',
    'helpClass' => '',
])

@php
    $fieldError = $error ?? ($name ? $errors->first($name) : null);
    $fieldId = $name ?? 'field_' . uniqid();
    $labelClasses = 'block text-sm font-semibold text-gray-800 mb-2 ' . $labelClass;
    $wrapperClasses = 'relative ' . $wrapperClass;
    $errorClasses = 'mt-2 text-sm text-red-600 flex items-center gap-1 ' . $errorClass;
    $helpClasses = 'mt-2 text-sm text-gray-600 ' . $helpClass;
@endphp

<div {{ $attributes }}>
    @if ($label)
        <label for="{{ $fieldId }}" class="{{ $labelClasses }}">
            {{ __($label) }}
            @if ($required)
                <span class="text-red-500 ml-1">*</span>
            @endif
        </label>
    @endif

    <div class="{{ $wrapperClasses }}">
        {{ $slot }}
    </div>

    @if ($fieldError)
        <p class="{{ $errorClasses }}">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd"></path>
            </svg>
            {{ __($fieldError) }}
        </p>
    @endif

    @if ($help)
        <p class="{{ $helpClasses }}">{{ __($help) }}</p>
    @endif
</div>
