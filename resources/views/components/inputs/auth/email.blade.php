@props([
    'name' => 'email',
    'label' => 'Email address',
    'placeholder' => 'Email address',
    'required' => true,
    'value' => '',
    'containerClass' => '',
    'inputClass' => '',
    'errorClass' => 'mt-1 text-sm text-red-600',
])

@php
    $emailIcon = '<svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
    <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
    <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
</svg>';

    $defaultInputClass =
        'block w-full rounded-lg border-0 py-3 pl-10 bg-transparent text-gray-900 ring-1 ring-inset ring-transparent placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6';
    $errorInputClass =
        'block w-full rounded-lg border-0 py-3 pl-10 bg-transparent text-gray-900 ring-1 ring-inset ring-red-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-red-500 sm:text-sm sm:leading-6';

    $hasError = $errors->has($name);
    $finalInputClass = $inputClass ? $inputClass : ($hasError ? $errorInputClass : $defaultInputClass);
@endphp

<div class="{{ $containerClass }}">
    <label for="{{ $name }}" class="sr-only">{{ __($label) }}</label>
    <div class="relative themed-input rounded-lg">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            {!! $emailIcon !!}
        </div>
        <input id="{{ $name }}" name="{{ $name }}" type="email" autocomplete="email"
            @if ($required) required @endif class="{{ $finalInputClass }}"
            placeholder="{{ __($placeholder) }}" value="{{ $value ?: old($name) }}"
            {{ $attributes->except(['name', 'label', 'placeholder', 'required', 'value', 'containerClass', 'inputClass', 'errorClass']) }}>
    </div>

    @error($name)
        <p class="{{ $errorClass }}">{{ $message }}</p>
    @enderror
</div>
