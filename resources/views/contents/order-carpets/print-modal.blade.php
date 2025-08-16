<div x-show="isPrinting" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0" class="modal-overlay-force"
    :class="{ 'modal-visible-force': isPrinting, 'modal-hidden-force': !isPrinting }" @click="isPrinting = false">

    <!-- Center the modal -->
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">​</span>

        <!-- Modal Content -->
        <div x-show="isPrinting" x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            class="modal-content-force relative inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full"
            @click.stop>

            <!-- Header -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div
                            class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-white bg-opacity-20">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                                </path>
                            </svg>
                        </div>
                        <h3 class="ml-3 text-lg font-semibold text-white">{{ __('Print QR Code') }}</h3>
                    </div>
                    <button @click="isPrinting = false"
                        class="text-white hover:text-gray-200 transition-colors duration-200">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="bg-white px-6 py-6">
                <!-- QR Code Preview -->
                <div class="text-center mb-6">
                    <div class="inline-block p-4 bg-gray-50 border border-gray-200 rounded-xl shadow-sm">
                        <img src="{{ Storage::url($orderCarpet->qr_code_path) }}" alt="{{ __('QR Code Preview') }}"
                            class="w-24 h-24 mx-auto">
                    </div>
                    <p class="mt-2 text-sm font-medium text-gray-900">{{ __('Carpet') }} #{{ $orderCarpet->id }}</p>
                    <p class="text-xs text-gray-500">{{ __('QR Code Preview') }}</p>
                </div>

                <!-- Print Options -->
                <div class="space-y-4">
                    <div>
                        <label for="print-copies" class="block text-sm font-medium text-gray-700 mb-2">
                            {{ __('Number of Copies') }}
                        </label>
                        <div class="relative">
                            <input type="number" id="print-copies" x-model="printCopies" min="1" max="50"
                                class="block w-full px-4 py-3 pr-20 bg-gray-50 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors duration-200">

                            <!-- Increment/Decrement Controls -->
                            <div class="absolute inset-y-0 right-0 flex flex-col">
                                <button type="button" @click="incrementCopies()"
                                    class="flex-1 flex items-center justify-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors duration-200">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 15l7-7 7 7"></path>
                                    </svg>
                                </button>
                                <div class="border-t border-gray-300"></div>
                                <button type="button" @click="decrementCopies()"
                                    class="flex-1 flex items-center justify-center px-3 text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600 transition-colors duration-200">
                                    <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="h-5 w-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div class="ml-3">
                                <p class="text-sm text-blue-800">
                                    <strong>{{ __('Print Tips') }}:</strong>
                                    {{ __('For best results, use high-quality paper and ensure your printer is set to the highest quality mode.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hidden print content -->
                <div id="qr-code-print-template" class="hidden">
                    <div class="print-item">
                        <div class="qr-container">
                            <img src="{{ Storage::url($orderCarpet->qr_code_path) }}" alt="{{ __('QR Code') }}" />
                        </div>
                        <div class="carpet-info">
                            <h3>{{ __('Carpet') }} #{{ $orderCarpet->id }}</h3>
                            <p>{{ $orderCarpet->order->client->full_name ?? __('N/A') }}</p>
                            <p>{{ $orderCarpet->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="bg-gray-50 px-6 py-4 sm:flex sm:flex-row-reverse sm:gap-3">
                <button type="button" @click="printQrCode()"
                    class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-base font-medium text-white hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:w-auto sm:text-sm transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z">
                        </path>
                    </svg>
                    {{ __('Print Now') }}
                </button>
                <button type="button" @click="isPrinting = false"
                    class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-6 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:mt-0 sm:w-auto sm:text-sm transition-colors duration-200">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>
