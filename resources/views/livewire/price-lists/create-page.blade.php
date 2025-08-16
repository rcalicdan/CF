<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Price List Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('Name') }}" name="name" required>
                        <x-forms.input name="name" wire:model="name" placeholder="{{ __('Enter price list name') }}"
                            required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z\'></path>'" />
                    </x-forms.field>
                    <x-forms.field label="{{ __('Postal Code') }}" name="location_postal_code" required>
                        <x-forms.input name="location_postal_code" wire:model="location_postal_code"
                            placeholder="{{ __('Enter postal code') }}" required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z\'></path><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 11a3 3 0 11-6 0 3 3 0 016 0z\'></path>'" />
                    </x-forms.field>
                </div>
            </div>
            <!-- Service Prices Section -->
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                        {{ __('Service Prices') }}</h3>
                    @if (count($servicePrices) > 0)
                        <button type="button" wire:click="togglePreview"
                            class="text-sm bg-gray-100 hover:bg-gray-200 px-3 py-1 rounded-md transition-colors">
                            {{ $showPreview ? __('Hide Preview') : __('Show Service Preview') }}
                        </button>
                    @endif
                </div>
                <!-- Direct Service Addition Form -->
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <h4 class="text-sm font-medium text-blue-900 mb-3">{{ __('Add Service (Direct Selection)') }}</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="relative">
                            <x-forms.field label="{{ __('Service') }}" name="directServiceSearch">
                                <x-forms.input name="directServiceSearch"
                                    wire:model.live.debounce.300ms="directServiceSearch"
                                    placeholder="{{ __('Search for a service...') }}" autocomplete="off"
                                    wire:click="$set('showDirectServiceDropdown', true)" :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z\'></path>'" />
                            </x-forms.field>
                            @if ($showDirectServiceDropdown && count($directAvailableServices) > 0)
                                <div
                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                                    @foreach ($directAvailableServices as $service)
                                        <div wire:click="selectDirectService({{ $service->id }})"
                                            class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0">
                                            <div class="font-medium text-gray-900">{{ $service->name }}</div>
                                            <div class="text-sm text-gray-500">{{ __('Base Price') }}:
                                                {{ number_format($service->base_price, 2) }} PLN</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div>
                            <x-forms.field label="{{ __('Price') }}" name="price">
                                <x-forms.input type="number" step="0.01" min="0" name="price"
                                    wire:model="price" placeholder="0.00"/>
                            </x-forms.field>
                        </div>
                        <div>
                            <button type="button" wire:click="addServiceFromDropdown"
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                                @if (!$service_id || !$price) disabled @endif>
                                {{ __('Add Service') }}
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Preview Table -->
                @if ($showPreview && count($servicePrices) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900">{{ __('Price List Preview') }}</h4>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Service Name') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Base Price') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('List Price') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Difference') }}
                                        </th>
                                        <th
                                            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ __('Actions') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($servicePrices as $index => $servicePrice)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $servicePrice['name'] }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    {{ number_format($servicePrice['base_price'], 2) }} PLN</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-semibold text-gray-900">
                                                    {{ number_format($servicePrice['price'], 2) }} PLN</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $difference = $servicePrice['price'] - $servicePrice['base_price'];
                                                    $percentage =
                                                        $servicePrice['base_price'] > 0
                                                            ? ($difference / $servicePrice['base_price']) * 100
                                                            : 0;
                                                @endphp
                                                <div
                                                    class="text-sm {{ $difference >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $difference >= 0 ? '+' : '' }}{{ number_format($difference, 2) }}
                                                    PLN
                                                    ({{ $percentage >= 0 ? '+' : '' }}{{ number_format($percentage, 1) }}%)
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <button type="button"
                                                    wire:click="removeServicePrice({{ $index }})"
                                                    class="text-red-600 hover:text-red-900">
                                                    {{ __('Remove') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
                <!-- Selected Services List (Compact View) -->
                @if (!$showPreview && count($servicePrices) > 0)
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                            <h4 class="text-sm font-medium text-gray-900">{{ __('Selected Services') }}</h4>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @foreach ($servicePrices as $index => $servicePrice)
                                <div class="px-4 py-3 flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900">{{ $servicePrice['name'] }}</div>
                                        <div class="text-sm text-gray-500">{{ __('Base Price') }}:
                                            {{ number_format($servicePrice['base_price'], 2) }} PLN</div>
                                    </div>
                                    <div class="flex items-center space-x-4">
                                        <div class="text-lg font-semibold text-gray-900">
                                            {{ number_format($servicePrice['price'], 2) }} PLN</div>
                                        <button type="button" wire:click="removeServicePrice({{ $index }})"
                                            class="text-red-600 hover:text-red-800 focus:outline-none">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if (count($servicePrices) > 0)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="text-blue-800 font-medium">{{ __('Total Services: :count', ['count' => count($servicePrices)]) }}</span>
                            <span class="text-blue-800 font-medium">
                                {{ __('Total Value: :amount PLN', ['amount' => number_format(collect($servicePrices)->sum('price'), 2)]) }}
                            </span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('price-lists.index') }}" buttonText="{{ __('Cancel') }}"
                    spacing="" />
                <x-utils.submit-button wire-target="save" buttonText="{{ __('Create Price List') }}"
                    bgColor="bg-indigo-600" hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
@script
    <script>
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('[wire\\:click="$set(\'showDirectServiceDropdown\', true)"]')
                .closest('.relative');
            if (dropdown && !dropdown.contains(event.target)) {
                @this.set('showDirectServiceDropdown', false);
            }
        });
    </script>
@endscript
