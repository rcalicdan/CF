<div class="mt-6">
    <!-- Custom Pagination -->
    <div class="flex items-center justify-between">
        <!-- Mobile pagination -->
        <div class="flex-1 flex justify-between sm:hidden">
            @if ($currentPage > 1)
            <button wire:click="previousPage"
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                {{ __('Previous') }}
            </button>
            @endif
            @if ($currentPage < $lastPage) <button wire:click="nextPage"
                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                {{ __('Next') }}
                </button>
                @endif
        </div>

        <!-- Desktop pagination -->
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    {!! __('Showing :from to :to of :total results', [
                    'from' => '<span class="font-medium">' . $from . '</span>',
                    'to' => '<span class="font-medium">' . $to . '</span>',
                    'total' => '<span class="font-medium">' . $total . '</span>',
                    ]) !!}
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    @if ($currentPage > 1)
                    <button wire:click="previousPage"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">{{ __('Previous') }}</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    @else
                    <span
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                        <span class="sr-only">{{ __('Previous') }}</span>
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                            aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                clip-rule="evenodd" />
                        </svg>
                    </span>
                    @endif

                    @php
                    $start = max(1, $currentPage - 2);
                    $end = min($lastPage, $currentPage + 2);

                    if ($end - $start < 4) { if ($start==1) { $end=min($lastPage, $start + 4); } else { $start=max(1,
                        $end - 4); } } @endphp {{-- First page --}} @if ($start> 1)
                        <button wire:click="gotoPage(1)"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                            1
                        </button>
                        @if ($start > 2)
                        <span
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                        @endif
                        @endif

                        {{-- Page range --}}
                        @for ($i = $start; $i <= $end; $i++) <button wire:click="gotoPage({{ $i }})"
                            class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i == $currentPage ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                            {{ $i }}
                            </button>
                            @endfor

                            {{-- Last page --}}
                            @if ($end < $lastPage) @if ($end < $lastPage - 1) <span
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                                ...
                                </span>
                                @endif
                                <button wire:click="gotoPage({{ $lastPage }})"
                                    class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    {{ $lastPage }}
                                </button>
                                @endif

                                {{-- Next Page Link --}}
                                @if ($currentPage < $lastPage) <button wire:click="nextPage"
                                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    <span class="sr-only">{{ __('Next') }}</span>
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                        fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd"
                                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    </button>
                                    @else
                                    <span
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-gray-50 text-sm font-medium text-gray-300 cursor-not-allowed">
                                        <span class="sr-only">{{ __('Next') }}</span>
                                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                    @endif
                </nav>
            </div>
        </div>
    </div>
</div>