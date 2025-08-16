@props([
    'title' => 'Form',
    'subtitle' => null,
    'action' => '#',
    'method' => 'POST',
    'enctype' => null,
    'wire' => null,
    'showCard' => true,
    'cardClass' => '',
    'formClass' => '',
    'headerClass' => '',
    'bodyClass' => '',
    'footerClass' => '',
    'showFooter' => true,
])

@php
    $cardClasses = 'bg-white rounded-lg shadow-sm themed-card ' . $cardClass;
    $headerClasses = 'px-6 py-4 border-b themed-table-header ' . $headerClass;
    $bodyClasses = 'px-6 py-6 ' . $bodyClass;
    $footerClasses = 'px-6 py-4 bg-gray-50 border-t themed-table-header flex justify-end space-x-3 ' . $footerClass;
    $formClasses = 'space-y-6 ' . $formClass;
@endphp

<div class="{{ $showCard ? $cardClasses : '' }}">
    <!-- Form Header -->
    @if ($title || $subtitle)
        <div class="{{ $showCard ? $headerClasses : 'mb-6' }}">
            <h2 class="text-xl font-semibold text-primary-dark">{{ __($title) }}</h2>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-600">{{ __($subtitle) }}</p>
            @endif
        </div>
    @endif

    <!-- Form Body -->
    <div class="{{ $showCard ? $bodyClasses : '' }}">
        <form
            @if ($wire) wire:submit="{{ $wire }}" @else action="{{ $action }}" method="{{ $method }}" @endif
            @if ($enctype) enctype="{{ $enctype }}" @endif class="{{ $formClasses }}"
            {{ $attributes->except(['title', 'subtitle', 'action', 'method', 'enctype', 'wire', 'showCard', 'cardClass', 'formClass', 'headerClass', 'bodyClass', 'footerClass', 'showFooter']) }}>
            @if (!$wire && $method !== 'GET')
                @csrf
                @if (strtoupper($method) !== 'POST')
                    @method($method)
                @endif
            @endif

            {{ $slot }}

            @if ($showFooter && isset($actions))
                <div class="{{ $showCard ? '' : 'mt-6 flex justify-end space-x-3' }}">
                    {{ $actions }}
                </div>
            @endif
        </form>
    </div>

    <!-- Form Footer (outside form for additional actions) -->
    @if ($showFooter && isset($footer))
        <div class="{{ $footerClasses }}">
            {{ $footer }}
        </div>
    @endif
</div>
