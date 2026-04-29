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

                        <!-- Status Field -->
                        <x-forms.field label="{{ __('Status') }}" name="status" required>
                            <x-forms.select name="status" wire:model="status"
                                placeholder="{{ __('Select a status...') }}" :options="$statusOptions" required />
                        </x-forms.field>

                        <!-- Services Section -->
                        <div class="space-y-4 pt-4">
                            <h4 class="text-md font-medium text-gray-900 border-b border-gray-200 pb-2">
                                {{ __('Services') }}</h4>

                            <!-- Selected Services Display with Quantity -->
                            @if (count($this->selectedServicesData) > 0)
                                <div class="mb-4">
                                    <p class="text-sm font-medium text-gray-700 mb-3">{{ __('Selected Services') }}
                                        ({{ count($selectedServices) }}):</p>

                                    <div class="space-y-3">
                                        @foreach ($this->selectedServicesData as $service)
                                            <div
                                                class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                                <!-- Service Info -->
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            class="font-medium text-gray-900">{{ $service->name }}</span>
                                                        @if ($service->is_area_based)
                                                            <span
                                                                class="text-xs bg-blue-100 text-blue-800 px-2 py-0.5 rounded whitespace-nowrap">
                                                                {{ __('Area-based') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                    <div class="text-xs text-gray-600 mt-0.5">
                                                        {{ __('Unit price') }}:
                                                        {{ number_format($this->getServiceEffectivePrice($service->id), 2, ',', ' ') }}
                                                        zł
                                                        @if ($service->is_area_based)
                                                            / m²
                                                        @endif
                                                    </div>
                                                </div>

                                                <!-- Quantity Input -->
                                                <div class="flex items-center gap-2">
                                                    <div class="flex flex-col items-end">
                                                        <label for="quantity_{{ $service->id }}"
                                                            class="text-xs text-gray-600 mb-1 whitespace-nowrap">
                                                            {{ __('Multiplier') }}
                                                        </label>
                                                        <input type="number" id="quantity_{{ $service->id }}"
                                                            step="0.01" min="0.01" max="9999.99"
                                                            placeholder="{{ __('Auto') }}"
                                                            wire:model.live="serviceQuantities.{{ $service->id }}"
                                                            class="w-20 px-2 py-1 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                                                        @if ($service->is_area_based && is_null($serviceQuantities[$service->id] ?? null))
                                                            <span
                                                                class="text-xs text-gray-500 mt-0.5 whitespace-nowrap">
                                                                ({{ __('uses area') }})
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <!-- Price Display -->
                                                    <div class="text-right min-w-[80px]">
                                                        <div class="font-semibold text-gray-900 text-sm">
                                                            {{ number_format($this->calculateServicePrice($service), 2, ',', ' ') }}
                                                            zł
                                                        </div>
                                                        @if (!is_null($serviceQuantities[$service->id] ?? null) && $serviceQuantities[$service->id] > 0)
                                                            <div class="text-xs text-gray-600">
                                                                ×
                                                                {{ number_format($serviceQuantities[$service->id], 2, ',', ' ') }}
                                                            </div>
                                                        @elseif($service->is_area_based && $this->totalArea > 0)
                                                            <div class="text-xs text-gray-600">
                                                                × {{ number_format($this->totalArea, 2, ',', ' ') }} m²
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Remove Button -->
                                                    <button type="button"
                                                        wire:click="removeService({{ $service->id }})"
                                                        class="text-red-600 hover:text-red-800 p-1 rounded hover:bg-red-50"
                                                        title="{{ __('Remove service') }}">
                                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                                clip-rule="evenodd"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
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
                                                    <div class="flex items-center flex-1">
                                                        <input type="checkbox"
                                                            {{ in_array($service->id, $selectedServices) ? 'checked' : '' }}
                                                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded pointer-events-none">
                                                        <div class="ml-3 flex-1">
                                                            <span
                                                                class="block font-medium text-gray-900">{{ $service->name }}</span>
                                                            @if ($order->priceList)
                                                                <span class="block text-xs text-gray-500 mt-0.5">
                                                                    {{ $order->priceList->name }}
                                                                    @if ($order->priceList->location_postal_code)
                                                                        ({{ $order->priceList->location_postal_code }})
                                                                    @endif
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-sm font-semibold text-gray-600 whitespace-nowrap ml-2">
                                                        @php
                                                            $effectivePrice = $this->getServiceEffectivePrice(
                                                                $service->id,
                                                            );
                                                        @endphp
                                                        @if ($service->is_area_based)
                                                            {{ number_format($effectivePrice, 2, ',', ' ') }} zł / m²
                                                        @else
                                                            {{ number_format($effectivePrice, 2, ',', ' ') }} zł
                                                        @endif

                                                        @if ($effectivePrice != $service->base_price)
                                                            <span class="text-xs text-gray-400 line-through ml-1">
                                                                {{ number_format($service->base_price, 2, ',', ' ') }}
                                                            </span>
                                                        @endif
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($showServicesDropdown && count($this->filteredServices) === 0)
                                    <div
                                        class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md py-2 text-base ring-1 ring-black ring-opacity-5">
                                        <p class="text-sm text-gray-500 px-3">
                                            {{ __('No services found matching') }} "{{ $serviceSearch }}"
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

                        <!-- Height and Width Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.field label="Height (m)" name="height">
                                <x-forms.input type="number" step="0.01" min="0" name="height"
                                    wire:model.live="height" placeholder="{{ __('Enter height in meters') }}" />
                            </x-forms.field>

                            <x-forms.field label="Width (m)" name="width">
                                <x-forms.input type="number" step="0.01" min="0" name="width"
                                    wire:model.live="width" placeholder="{{ __('Enter width in meters') }}" />
                            </x-forms.field>
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
                                    <h5 class="text-sm font-medium text-gray-700 border-b pb-1">{{ __('Services') }}:
                                    </h5>
                                    @foreach ($this->selectedServicesData as $service)
                                        <div class="space-y-0.5">
                                            <div class="flex justify-between text-sm">
                                                <span class="text-gray-600 truncate mr-2">{{ $service->name }}</span>
                                                <span class="font-medium whitespace-nowrap">
                                                    {{ number_format($this->calculateServicePrice($service), 2, ',', ' ') }}
                                                    zł
                                                </span>
                                            </div>
                                            @php
                                                $quantity = $serviceQuantities[$service->id] ?? null;
                                                $effectivePrice = $this->getServiceEffectivePrice($service->id);
                                            @endphp
                                            @if (!is_null($quantity) && $quantity > 0)
                                                <div class="text-xs text-gray-500 pl-2">
                                                    {{ number_format($effectivePrice, 2, ',', ' ') }} zł ×
                                                    {{ number_format($quantity, 2, ',', ' ') }} =
                                                    {{ number_format($effectivePrice * $quantity, 2, ',', ' ') }} zł
                                                </div>
                                            @elseif($service->is_area_based && $this->totalArea > 0)
                                                <div class="text-xs text-gray-500 pl-2">
                                                    {{ number_format($effectivePrice, 2, ',', ' ') }} zł/m² ×
                                                    {{ number_format($this->totalArea, 2, ',', ' ') }} m² =
                                                    {{ number_format($effectivePrice * $this->totalArea, 2, ',', ' ') }}
                                                    zł
                                                </div>
                                            @elseif($service->is_area_based)
                                                <div class="text-xs text-amber-600 pl-2">
                                                    {{ __('Waiting for dimensions') }}
                                                </div>
                                            @endif
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
