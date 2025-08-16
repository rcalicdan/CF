@props([
    'name' => 'password',
    'label' => 'Password',
    'placeholder' => 'Password',
    'required' => true,
    'showToggle' => true,
    'autocomplete' => 'current-password',
    'containerClass' => '',
    'inputClass' => '',
    'errorClass' => 'mt-1 text-sm text-red-600',
])

@php
    $lockIcon = '<svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
    <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
</svg>';

    $defaultInputClass =
        'block w-full rounded-lg border-0 py-3 pl-10 bg-transparent text-gray-900 ring-1 ring-inset ring-transparent placeholder:text-gray-400 focus:ring-2 focus:ring-inset sm:text-sm sm:leading-6 pr-10';
    $errorInputClass =
        'block w-full rounded-lg border-0 py-3 pl-10 bg-transparent text-gray-900 ring-1 ring-inset ring-red-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-red-500 sm:text-sm sm:leading-6 pr-10';

    $hasError = $errors->has($name);
    $finalInputClass = $inputClass ? $inputClass : ($hasError ? $errorInputClass : $defaultInputClass);
@endphp

<div class="{{ $containerClass }}">
    <label for="{{ $name }}" class="sr-only">{{ __($label) }}</label>
    <div class="relative themed-input rounded-lg">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
            {!! $lockIcon !!}
        </div>
        <input id="{{ $name }}" name="{{ $name }}" :type="showPassword ? 'text' : 'password'"
            autocomplete="{{ $autocomplete }}" @if ($required) required @endif
            class="{{ $finalInputClass }}" placeholder="{{ __($placeholder) }}"
            {{ $attributes->except(['name', 'label', 'placeholder', 'required', 'showToggle', 'autocomplete', 'containerClass', 'inputClass', 'errorClass']) }}>

        @if ($showToggle)
            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                <button type="button" @click="showPassword = !showPassword"
                    class="text-gray-500 hover:text-primary focus:text-primary transition-colors duration-200 p-1 rounded-md hover:bg-gray-100">
                    <span class="sr-only">{{ __('Toggle password visibility') }}</span>
                    <div class="relative w-5 h-5">
                        <!-- Eye Open -->
                        <svg x-show="!showPassword" x-transition:enter="transition-opacity ease-in-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity ease-in-out duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="absolute inset-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M12 4.5C7.305 4.5 3.25 7.435 1.5 12c1.75 4.565 5.805 7.5 10.5 7.5s8.75-2.935 10.5-7.5c-1.75-4.565-5.805-7.5-10.5-7.5z" />
                            <circle cx="12" cy="12" r="3.5" fill="white" />
                            <circle cx="12" cy="12" r="2" fill="currentColor" />
                        </svg>
                        <!-- Eye Closed -->
                        <svg x-show="showPassword" x-cloak
                            x-transition:enter="transition-opacity ease-in-out duration-200"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition-opacity ease-in-out duration-200"
                            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                            class="absolute inset-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                            fill="currentColor">
                            <path
                                d="M12 4.5C7.305 4.5 3.25 7.435 1.5 12c1.75 4.565 5.805 7.5 10.5 7.5s8.75-2.935 10.5-7.5c-1.75-4.565-5.805-7.5-10.5-7.5z"
                                opacity="0.3" />
                            <path
                                d="M3.98 8.223A10.477 10.477 0 001.5 12c1.75 4.565 5.805 7.5 10.5 7.5a10.477 10.477 0 004.902-1.21l-1.414-1.414A8.5 8.5 0 0112 17.5c-3.866 0-7.168-2.44-8.5-5.5.326-.75.777-1.434 1.34-2.04L3.98 8.223z" />
                            <path
                                d="M20.02 15.777A10.477 10.477 0 0022.5 12c-1.75-4.565-5.805-7.5-10.5-7.5a10.477 10.477 0 00-4.902 1.21l1.414 1.414A8.5 8.5 0 0112 6.5c3.866 0 7.168 2.44 8.5 5.5-.326.75-.777 1.434-1.34 2.04l.86.737z" />
                            <path d="M8.5 12a3.5 3.5 0 007 0 3.5 3.5 0 00-7 0z" opacity="0.3" />
                            <path d="M2.5 2.5l19 19" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </div>
                </button>
            </div>
        @endif
    </div>

    @error($name)
        <p class="{{ $errorClass }}">{{ $message }}</p>
    @enderror
</div>
