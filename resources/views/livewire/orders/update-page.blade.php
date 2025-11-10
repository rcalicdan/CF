<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6 pb-40">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Order Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Client Field -->
                    <x-forms.field label="Client" name="client_id" required>
                        <div class="relative">
                            <input type="text" wire:model.live="clientSearch" wire:focus="showAllClients"
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="{{ __('Search for a client...') }}">
                            <!-- Selected Client Display -->
                            @if ($client_id && $selectedClient)
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                            <!-- Clients Dropdown -->
                            @if ($showClientsDropdown && count($filteredClients) > 0)
                                <div
                                    class="absolute z-20 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    @foreach ($filteredClients as $client)
                                        <div wire:click="selectClient({{ $client->id }}, '{{ $client->full_name }}')"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 {{ $client->id == $client_id ? 'bg-indigo-50' : '' }}">
                                            <span
                                                class="block font-medium text-gray-900">{{ $client->full_name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($showClientsDropdown && count($filteredClients) === 0)
                                <div
                                    class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md py-2 text-base ring-1 ring-black ring-opacity-5">
                                    <p class="text-sm text-gray-500 px-3">
                                        {{ __('No clients found matching :search', ['search' => $clientSearch]) }}</p>
                                </div>
                            @endif
                        </div>
                        @if ($showClientsDropdown)
                            <div wire:click="hideClientsDropdown" class="fixed inset-0 z-10"></div>
                        @endif
                        @error('client_id')
                            <span class="text-sm text-red-600">{{ __($message) }}</span>
                        @enderror
                    </x-forms.field>
                    <!-- Driver Field -->
                    <x-forms.field label="Assigned Driver" name="assigned_driver_id">
                        <div class="relative">
                            <input type="text" wire:model.live="driverSearch" wire:focus="showAllDrivers"
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="{{ __('Search for a driver (optional)...') }}">
                            <!-- Selected Driver Display -->
                            @if ($assigned_driver_id && $selectedDriver)
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center space-x-1">
                                    <button type="button" wire:click="clearDriver"
                                        class="text-gray-400 hover:text-gray-600">
                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
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
                                    class="absolute z-20 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    @foreach ($filteredDrivers as $driver)
                                        <div wire:click="selectDriver({{ $driver->id }}, '{{ $driver->user->full_name }}')"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 {{ $driver->id == $assigned_driver_id ? 'bg-indigo-50' : '' }}">
                                            <span
                                                class="block font-medium text-gray-900">{{ $driver->user->full_name }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($showDriversDropdown && count($filteredDrivers) === 0)
                                <div
                                    class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md py-2 text-base ring-1 ring-black ring-opacity-5">
                                    <p class="text-sm text-gray-500 px-3">
                                        {{ __('No drivers found matching :search', ['search' => $driverSearch]) }}</p>
                                </div>
                            @endif
                        </div>
                        <!-- Click outside to close dropdown -->
                        @if ($showDriversDropdown)
                            <div wire:click="hideDriversDropdown" class="fixed inset-0 z-10"></div>
                        @endif
                        @error('assigned_driver_id')
                            <span class="text-sm text-red-600">{{ __($message) }}</span>
                        @enderror
                    </x-forms.field>
                    <x-forms.field label="Price List" name="price_list_id" required>
                        <div class="relative">
                            <input type="text" wire:model.live="priceListSearch" wire:focus="showAllPriceLists"
                                class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                placeholder="{{ __('Search for a price list...') }}">
                            <!-- Selected Price List Display -->
                            @if ($price_list_id && $selectedPriceList)
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                                    <svg class="h-5 w-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                            clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            @endif
                            <!-- Price Lists Dropdown -->
                            @if ($showPriceListsDropdown && count($filteredPriceLists) > 0)
                                <div
                                    class="absolute z-20 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    @foreach ($filteredPriceLists as $priceList)
                                        <div wire:click="selectPriceList({{ $priceList->id }}, '{{ $priceList->name }}')"
                                            class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 {{ $priceList->id == $price_list_id ? 'bg-indigo-50' : '' }}">
                                            <div>
                                                <span
                                                    class="block font-medium text-gray-900">{{ $priceList->name }}</span>
                                                @if ($priceList->location_postal_code)
                                                    <span
                                                        class="block text-sm text-gray-500">{{ $priceList->location_postal_code }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($showPriceListsDropdown && count($filteredPriceLists) === 0)
                                <div
                                    class="absolute z-20 mt-1 w-full bg-white shadow-lg rounded-md py-2 text-base ring-1 ring-black ring-opacity-5">
                                    <p class="text-sm text-gray-500 px-3">
                                        {{ __('No price lists found matching :search', ['search' => $priceListSearch]) }}
                                    </p>
                                </div>
                            @endif
                        </div>
                        <!-- Click outside to close dropdown -->
                        @if ($showPriceListsDropdown)
                            <div wire:click="hidePriceListsDropdown" class="fixed inset-0 z-10"></div>
                        @endif
                        @error('price_list_id')
                            <span class="text-sm text-red-600">{{ __($message) }}</span>
                        @enderror
                    </x-forms.field>
                    <x-forms.field label="Status" name="status" required>
                        <select wire:model="status"
                            class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <option value="">{{ __('Select a status...') }}</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('status')
                            <span class="text-sm text-red-600">{{ __($message) }}</span>
                        @enderror
                    </x-forms.field>
                    <x-forms.field class="md:col-span-2" label="Schedule Date" name="schedule_date">
                        <x-forms.input type="datetime-local" name="schedule_date" wire:model="schedule_date" />
                    </x-forms.field>
                    <x-forms.field class="md:col-span-2" label="Complaint Order" name="is_complaint">
                        <div class="flex items-center">
                            <input type="checkbox" id="is_complaint" wire:model="is_complaint"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                            <label for="is_complaint" class="ml-2 block text-sm text-gray-900">
                                {{ __('This is a complaint order') }}
                            </label>
                        </div>
                    </x-forms.field>
                </div>
            </div>
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('orders.index') }}" buttonText="{{ __('Cancel') }}"
                    spacing="" />
                <x-utils.submit-button wire-target="save" buttonText="{{ __('Update Order') }}"
                    bgColor="bg-indigo-600" hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
