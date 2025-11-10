<div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
    <div class="flex-1 flex justify-between sm:hidden">
        <button wire:click="previousPage" @if ($modalPage <= 1) disabled @endif
            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Poprzednia
        </button>
        <button wire:click="nextPage" @if ($modalPage >= $this->getTotalPages()) disabled @endif
            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
            Następna
        </button>
    </div>
    
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Pokazuję
                <span class="font-medium">{{ ($modalPage - 1) * $perPage + 1 }}</span>
                do
                <span class="font-medium">{{ min($modalPage * $perPage, $this->getTotalOrdersCount()) }}</span>
                z
                <span class="font-medium">{{ $this->getTotalOrdersCount() }}</span>
                wyników
            </p>
        </div>
        
        <div>
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                <button wire:click="previousPage" @if ($modalPage <= 1) disabled @endif
                    class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="sr-only">Poprzednia</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                @php
                    $totalPages = $this->getTotalPages();
                    $currentPage = $modalPage;
                    $start = max(1, $currentPage - 2);
                    $end = min($totalPages, $currentPage + 2);
                @endphp

                @if ($start > 1)
                    <button wire:click="goToPage(1)"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        1
                    </button>
                    @if ($start > 2)
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <button wire:click="goToPage({{ $i }})"
                        class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i === $currentPage ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}">
                        {{ $i }}
                    </button>
                @endfor

                @if ($end < $totalPages)
                    @if ($end < $totalPages - 1)
                        <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                            ...
                        </span>
                    @endif
                    <button wire:click="goToPage({{ $totalPages }})"
                        class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50">
                        {{ $totalPages }}
                    </button>
                @endif

                <button wire:click="nextPage" @if ($modalPage >= $totalPages) disabled @endif
                    class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="sr-only">Następna</span>
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </nav>
        </div>
    </div>
</div>