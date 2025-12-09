<div>
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">

            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

                <!-- Backdrop with blur -->
                <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" wire:click="closeModal"
                    x-data x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                </div>

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
                    x-data x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <!-- Title Section -->
                            <div>
                                <h3 class="text-lg font-semibold text-white" id="modal-title">
                                    Przypisz kierowcę
                                </h3>
                                <p class="text-sm text-indigo-100">Zamówienie #{{ $orderId }}</p>
                            </div>

                            <!-- Modern Close Button -->
                            <button type="button" wire:click="closeModal"
                                class="rounded-full p-2 text-white bg-white/20 hover:bg-white hover:text-indigo-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-white/50 shadow-sm"
                                aria-label="Zamknij">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="bg-white px-6 py-6">
                        <div class="space-y-4">
                            <!-- Driver Search Field -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Wybierz kierowcę <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                    <input type="text" wire:model.live="driverSearch" wire:focus="showAllDrivers"
                                        class="block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-all"
                                        placeholder="Wyszukaj kierowcę po imieniu lub nazwisku...">

                                    <!-- Selected Driver Indicator -->
                                    @if ($selectedDriverId)
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                            <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                    @endif

                                    <!-- Drivers Dropdown -->
                                    @if ($showDriversDropdown && count($filteredDrivers) > 0)
                                        <div
                                            class="absolute z-20 mt-2 w-full bg-white shadow-lg max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                            @foreach ($filteredDrivers as $driver)
                                                <div wire:click="selectDriver({{ $driver->id }}, '{{ $driver->user->full_name }}')"
                                                    class="cursor-pointer select-none relative py-3 pl-3 pr-9 hover:bg-indigo-50 transition-colors {{ $driver->id == $selectedDriverId ? 'bg-indigo-50' : '' }}">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="flex-shrink-0">
                                                            <div
                                                                class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                                                <span class="text-sm font-medium text-indigo-600">
                                                                    {{ substr($driver->user->first_name, 0, 1) }}{{ substr($driver->user->last_name, 0, 1) }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                        <div class="flex-1">
                                                            <span
                                                                class="block font-medium text-gray-900">{{ $driver->user->full_name }}</span>
                                                            @if ($driver->phone_number)
                                                                <span
                                                                    class="block text-xs text-gray-500">{{ $driver->phone_number }}</span>
                                                            @endif
                                                        </div>
                                                        @if ($driver->id == $selectedDriverId)
                                                            <div class="flex-shrink-0">
                                                                <svg class="h-5 w-5 text-indigo-600" fill="currentColor"
                                                                    viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd"
                                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                        clip-rule="evenodd"></path>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @elseif($showDriversDropdown && count($filteredDrivers) === 0)
                                        <div
                                            class="absolute z-20 mt-2 w-full bg-white shadow-lg rounded-lg py-3 text-base ring-1 ring-black ring-opacity-5">
                                            <div class="flex items-center justify-center px-4">
                                                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                                    </path>
                                                </svg>
                                                <p class="text-sm text-gray-500">
                                                    Nie znaleziono kierowców dla: <span
                                                        class="font-medium">{{ $driverSearch }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Click outside to close dropdown -->
                                @if ($showDriversDropdown)
                                    <div wire:click="hideDriversDropdown" class="fixed inset-0 z-10"></div>
                                @endif

                                @error('selectedDriverId')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Info Box -->
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3">
                                        <h3 class="text-sm font-medium text-blue-800">
                                            Informacja
                                        </h3>
                                        <div class="mt-2 text-sm text-blue-700">
                                            <p>Wybrany kierowca zostanie przypisany do tego zamówienia i będzie mógł
                                                rozpocząć jego realizację.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3">
                        <button type="button" wire:click="closeModal"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                            Anuluj
                        </button>
                        <button type="button" wire:click="assignDriver" wire:loading.attr="disabled"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span wire:loading.remove wire:target="assignDriver">
                                Przypisz kierowcę
                            </span>
                            <span wire:loading wire:target="assignDriver" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none"
                                    viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10"
                                        stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                Przypisywanie...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
