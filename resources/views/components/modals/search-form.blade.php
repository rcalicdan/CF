<div x-show="isSearchModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center"
    style="display: none; background-color: rgba(0, 0, 0, 0.5);" x-cloak>
    <div @click.away="isSearchModalOpen = false" class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="flex justify-between items-center pb-3 border-b">
            <p class="text-2xl font-bold">{{ __($title ?? 'Search') }}</p>
            <button @click="isSearchModalOpen = false" class="text-gray-400 hover:text-gray-500">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form wire:submit.prevent='performSearch' class="space-y-4 mt-4">
            {{ $slot }}
            <div class="mt-6 flex justify-end space-x-2">
                <button type="button" @click="isSearchModalOpen = false" wire:click="clearSearch"
                    class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 transition">
                    {{ __('Clear & Close') }}
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                    {{ __('Search') }}
                </button>
            </div>
        </form>
    </div>
</div>
