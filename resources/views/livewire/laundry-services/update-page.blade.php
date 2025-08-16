<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="update" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Service Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('Service Name') }}" name="name" required>
                        <x-forms.input name="name" wire:model="name" placeholder="{{ __('Enter service name') }}" required />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Base Price') }}" name="base_price" required>
                        <x-forms.input type="number" step="0.01" min="0" name="base_price"
                            wire:model="base_price" placeholder="{{ __('Enter base price') }}" required />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('Pricing Type') }}" name="is_area_based" required
                        help="{{ __('Select whether this service is charged per area (square feet) or per item') }}">
                        <div class="flex items-center space-x-6">
                            <label class="flex items-center">
                                <input type="radio" wire:model="is_area_based" value="0"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">{{ __('Per Item') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" wire:model="is_area_based" value="1"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300">
                                <span class="ml-2 text-sm text-gray-700">{{ __('Area Based (per sq ft)') }}</span>
                            </label>
                        </div>
                    </x-forms.field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('services.index') }}" buttonText="{{ __('Cancel') }}" />
                <x-utils.submit-button wire-target="update" buttonText="{{ __('Update Service') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
