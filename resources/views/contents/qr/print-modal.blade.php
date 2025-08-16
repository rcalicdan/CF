<div x-show="showPrintModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
    class="fixed inset-0 bg-black/30 backdrop-blur-sm overflow-y-auto h-full w-full z-50" style="display: none;">

    <div class="relative top-20 mx-auto p-5 border border-gray-200 w-96 shadow-2xl rounded-xl bg-white"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95">

        <div class="mt-3 text-center">
            <!-- Header -->
            <div class="mb-6">
                <h3 class="text-xl font-semibold text-gray-900">{{ __('Print QR Codes') }}</h3>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Printing :count selected QR codes', ['count' => count($selectedQrCodes)]) }}
                </p>
            </div>

            <!-- Copies Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">
                    {{ __('Copies per QR Code') }}
                </label>
                <div class="flex items-center justify-center space-x-1">
                    <button @click="decrementCopies()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-3 rounded-l-lg border border-r-0 border-gray-300 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                        </svg>
                    </button>
                    <input type="number" x-model.number="printCopies" min="1" max="50"
                        class="w-16 text-center border-gray-300 border-l-0 border-r-0 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 py-2">
                    <button @click="incrementCopies()"
                        class="bg-gray-100 hover:bg-gray-200 text-gray-800 font-medium py-2 px-3 rounded-r-lg border border-l-0 border-gray-300 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                <button @click="showPrintModal = false"
                    class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg border border-gray-300 shadow-sm hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-1 transition-colors duration-200">
                    {{ __('Cancel') }}
                </button>
                <button @click="printSelected()"
                    class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition-colors duration-200">
                    {{ __('Print Now') }}
                </button>
            </div>
        </div>
    </div>
</div>
