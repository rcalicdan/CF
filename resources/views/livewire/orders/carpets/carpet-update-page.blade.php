<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <x-flash-session />
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="space-y-6">
                <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Update Carpet') }}</h3>
                    <div class="text-sm text-gray-600">
                        {{ __('Order') }}
                        #{{ $carpet->order->id }}{{ $carpet->order->client?->full_name ? ' - ' . $carpet->order->client->full_name : '' }}
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Section -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.field label="{{ __('Status') }}" name="status" class="md:col-span-2" required>
                                <x-forms.select name="status" wire:model="status" placeholder="{{ __('Select a status...') }}"
                                    :options="$statusOptions" required />
                            </x-forms.field>

                            <x-forms.field label="Height (m)" name="height">
                                <x-forms.input type="number" step="0.01" min="0" name="height"
                                    wire:model.live="height" placeholder="{{ __('Enter height in meters') }}" />
                            </x-forms.field>

                            <x-forms.field label="Width (m)" name="width">
                                <x-forms.input type="number" step="0.01" min="0" name="width"
                                    wire:model.live="width" placeholder="{{ __('Enter width in meters') }}" />
                            </x-forms.field>
                        </div>

                        <!-- Services Section -->
                        <div class="space-y-4 pt-4">
                            <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                                {{ __('Services') }}</h4>

                            <!-- Selected Services Display -->
                            @if (count($this->selectedServicesData) > 0)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-2">{{ __('Selected Services') }}
                                        ({{ count($selectedServices) }}):</p>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($this->selectedServicesData as $service)
                                            <span
                                                class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-indigo-100 text-indigo-800">
                                                {{ $service->name }}
                                                <button type="button" wire:click="removeService({{ $service->id }})"
                                                    class="ml-2 inline-flex items-center justify-center w-4 h-4 text-indigo-600 hover:text-indigo-800">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                </button>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Search Input -->
                            <div class="relative">
                                <div class="relative">
                                    <input type="text" wire:model.live="serviceSearch" wire:focus="showAllServices"
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                        placeholder="{{ __('Search services...') }}">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Services Dropdown -->
                                @if ($showServicesDropdown && count($this->filteredServices) > 0)
                                    <div
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                        @foreach ($this->filteredServices as $service)
                                            <div wire:click="toggleService({{ $service->id }})"
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-indigo-50 {{ in_array($service->id, $selectedServices) ? 'bg-indigo-50' : '' }}">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <input type="checkbox"
                                                            {{ in_array($service->id, $selectedServices) ? 'checked' : '' }}
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300
                                                rounded
                                                pointer-events-none">
                                                        <span
                                                            class="ml-3 block font-medium text-gray-900">{{ $service->name }}</span>
                                                    </div>
                                                    <span class="text-sm font-semibold text-gray-600 whitespace-nowrap">
                                                        @if ($service->is_area_based)
                                                            {{ number_format($service->base_price, 2, ',', ' ') }} zł /
                                                            m²
                                                        @else
                                                            {{ number_format($service->base_price, 2, ',', ' ') }} zł
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($showServicesDropdown && count($this->filteredServices) === 0)
                                    <div
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-2 text-base ring-1 ring-black ring-opacity-5">
                                        <p class="text-sm text-gray-500 px-3">{{ __('No services found matching') }}
                                            "{{ $serviceSearch }}"
                                        </p>
                                    </div>
                                @endif
                            </div>

                            @if ($showServicesDropdown)
                                <div wire:click="hideServicesDropdown" class="fixed inset-0 z-0"></div>
                            @endif

                            @error('selectedServices')
                                <span class="text-sm text-red-600">{{ $message }}</span>
                            @enderror
                        </div>

                        <x-forms.field class="md:col-span-2" label="Remarks" name="remarks">
                            <textarea wire:model="remarks" name="remarks" rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="{{ __('Enter any additional remarks or notes...') }}"></textarea>
                        </x-forms.field>
                    </div>

                    <!-- Price Preview Section -->
                    <div class="lg:col-span-1">
                        <div class="bg-gray-50 rounded-lg p-4 sticky top-4">
                            <h4 class="text-md font-medium text-gray-900 mb-4">{{ __('Order Summary') }}</h4>

                            <!-- Dimensions -->
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Height') }}:</span>
                                    <span class="font-medium">{{ $height ? $height . ' m' : __('Not set') }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600">{{ __('Width') }}:</span>
                                    <span class="font-medium">{{ $width ? $width . ' m' : __('Not set') }}</span>
                                </div>
                                @if ($height && $width)
                                    <div class="flex justify-between text-sm border-t pt-2">
                                        <span class="text-gray-600">{{ __('Total Area') }}:</span>
                                        <span class="font-medium">{{ number_format($this->totalArea, 2) }} m²</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Services breakdown -->
                            @if (count($selectedServices) > 0)
                                <div class="space-y-2 mb-4">
                                    <h5 class="text-sm font-medium text-gray-700">{{ __('Services') }}:</h5>
                                    @foreach ($this->selectedServicesData as $service)
                                        <div class="flex justify-between text-sm">
                                            <span class="text-gray-600 truncate mr-2">{{ $service->name }}</span>
                                            <span class="font-medium whitespace-nowrap">
                                                {{ number_format($this->calculateServicePrice($service), 2) }} zł
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            <!-- Total Price -->
                            <div class="border-t pt-3">
                                <div class="flex justify-between">
                                    <span class="text-lg font-semibold text-gray-900">{{ __('Total') }}:</span>
                                    <span class="text-lg font-bold text-indigo-600">
                                        {{ number_format($this->totalPrice, 2) }} zł
                                    </span>
                                </div>
                            </div>

                            @if (count($selectedServices) === 0)
                                <p class="text-sm text-gray-500 mt-2">{{ __('No services selected') }}</p>
                            @elseif (!$height || !$width)
                                <p class="text-xs text-amber-600 mt-2">
                                    {{ __('* Some services require dimensions for accurate pricing') }}
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.submit-button bgColor="bg-gray-600" type="button" wire:click.prevent="cancel"
                    buttonText="{{ __('Cancel') }}" />
                <x-utils.submit-button wire:target="save" buttonText="{{ __('Update Carpet') }}" />
            </div>
        </form>
    </div>
</div>
