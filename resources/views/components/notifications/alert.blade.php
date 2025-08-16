@props([
    'dismissible' => true,
    'position' => 'top-right',
    'duration' => 5000,
    'showValidationErrors' => false,
])

@php
    $alerts = [];

    if (session()->has('success')) {
        $alerts[] = ['type' => 'success', 'message' => session('success')];
    }

    if (session()->has('error')) {
        $alerts[] = ['type' => 'error', 'message' => session('error')];
    }

    if (session()->has('warning')) {
        $alerts[] = ['type' => 'warning', 'message' => session('warning')];
    }

    if (session()->has('info')) {
        $alerts[] = ['type' => 'info', 'message' => session('info')];
    }

    if ($showValidationErrors && $errors->any()) {
        $errorMessages = $errors->all();
        $alerts[] = [
            'type' => 'error',
            'message' => count($errorMessages) > 1 ? 'Please fix the following errors:' : $errorMessages[0],
            'errors' => $errorMessages,
        ];
    }

    $alternativeKeys = [
        'message' => 'info',
        'status' => 'success',
        'alert' => 'info',
        'notification' => 'info',
        'flash_message' => 'info',
        'toast' => 'info',
    ];

    foreach ($alternativeKeys as $key => $defaultType) {
        if (session()->has($key)) {
            $alerts[] = ['type' => $defaultType, 'message' => session($key)];
        }
    }

    $typeClasses = [
        'success' => 'bg-green-50 border-green-200 text-green-800',
        'error' => 'bg-red-50 border-red-200 text-red-800',
        'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
        'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    ];

    $iconClasses = [
        'success' => 'text-green-400',
        'error' => 'text-red-400',
        'warning' => 'text-yellow-400',
        'info' => 'text-blue-400',
    ];

    $positionClasses = [
        'top-left' => 'top-4 left-4',
        'top-right' => 'top-4 right-4',
        'bottom-left' => 'bottom-4 left-4',
        'bottom-right' => 'bottom-4 right-4',
    ];

    $icons = [
        'success' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.23a.75.75 0 00-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>',
        'error' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
        </svg>',
        'warning' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
        </svg>',
        'info' => '<svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd" />
        </svg>',
    ];
@endphp

@if (count($alerts) > 0)
    <div class="fixed {{ $positionClasses[$position] }} z-50 space-y-2 max-w-sm w-full" {{ $attributes }}>
        @foreach ($alerts as $index => $alert)
            <div x-data="{
                show: true,
                init() {
                    if ({{ $duration }} > 0) {
                        setTimeout(() => this.show = false, {{ $duration + $index * 500 }});
                    }
                }
            }" x-show="show" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-x-full"
                x-transition:enter-end="opacity-100 transform translate-x-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 transform translate-x-0"
                x-transition:leave-end="opacity-0 transform translate-x-full">

                <div class="rounded-lg border p-4 shadow-lg {{ $typeClasses[$alert['type']] }}">
                    <div class="flex items-start">
                        <div class="flex-shrink-0 {{ $iconClasses[$alert['type']] }}">
                            {!! $icons[$alert['type']] !!}
                        </div>
                        <div class="ml-3 flex-1">
                            <div class="text-sm font-medium">
                                {{ $alert['message'] }}
                            </div>
                            @if (isset($alert['errors']) && count($alert['errors']) > 1)
                                <ul class="mt-2 text-xs space-y-1">
                                    @foreach ($alert['errors'] as $error)
                                        <li>• {{ $error }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        @if ($dismissible)
                            <div class="ml-4 flex-shrink-0">
                                <button @click="show = false"
                                    class="inline-flex rounded-md {{ $iconClasses[$alert['type']] }} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-offset-2">
                                    <span class="sr-only">Close</span>
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
