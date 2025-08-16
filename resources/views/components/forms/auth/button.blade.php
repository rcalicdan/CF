@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'default',
    'fullWidth' => false,
    'disabled' => false,
    'loading' => false,
])

@php
    $baseClass =
        'inline-flex justify-center items-center font-semibold transition-all duration-300 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';

    $variants = [
        'primary' => 'btn-primary text-white shadow-sm',
        'secondary' => 'bg-gray-200 text-gray-900 hover:bg-gray-300',
        'outline' => 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
    ];

    $sizes = [
        'sm' => 'px-3 py-2 text-sm rounded-md',
        'default' => 'px-4 py-3 text-base rounded-lg',
        'lg' => 'px-6 py-4 text-lg rounded-lg',
    ];

    $classes =
        $baseClass . ' ' . ($variants[$variant] ?? $variants['primary']) . ' ' . ($sizes[$size] ?? $sizes['default']);

    if ($fullWidth) {
        $classes .= ' w-full';
    }

    $spinnerSize = match ($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5',
    };
@endphp

<button type="{{ $type }}" @if ($disabled) disabled @endif
    {{ $attributes->merge(['class' => $classes]) }}>

    <!-- Loading Spinner -->
    <svg wire:loading class="animate-spin {{ $spinnerSize }} mr-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
    </svg>

    <!-- Button Content -->
    <span wire:loading.remove>{{ $slot }}</span>
    <span wire:loading>{{ __('Processing...') }}</span>
</button>
