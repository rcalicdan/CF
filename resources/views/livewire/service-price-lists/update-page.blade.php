<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="update" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Update Service Price') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <x-forms.field label="{{ __('Price List') }}" name="price_list_id" required
                        help="{{ __('Select the price list for this service') }}">
                        <x-forms.select name="price_list_id" wire:model="price_list_id" placeholder="{{ __('Select price list') }}"
                            :options="$priceListOptions" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Service') }}" name="service_id" required
                        help="{{ __('Select the service to set price for') }}">
                        <x-forms.select name="service_id" wire:model="service_id" placeholder="{{ __('Select service') }}"
                            :options="$serviceOptions" />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('Price') }}" name="price" required
                        help="{{ __('Enter the price for this service in the selected price list') }}">
                        <x-forms.input type="number" step="0.01" min="0" name="price" wire:model="price"
                            placeholder="{{ __('0.00') }}" required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1\'></path>'" />
                    </x-forms.field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('service-price-lists.index') }}" buttonText="{{ __('Cancel') }}" />
                <x-utils.submit-button wire-target="update" buttonText="{{ __('Update Service Price') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
