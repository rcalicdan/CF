<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="update" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Personal Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('First Name') }}" name="first_name" required>
                        <x-forms.input name="first_name" wire:model="first_name" placeholder="{{ __('Enter first name') }}"
                            required />
                    </x-forms.field>
                    <x-forms.field label="{{ __('Last Name') }}" name="last_name" required>
                        <x-forms.input name="last_name" wire:model="last_name" placeholder="{{ __('Enter last name') }}" required />
                    </x-forms.field>
                    <x-forms.field label="{{ __('Phone Number') }}" name="phone_number" required>
                        <x-forms.input name="phone_number" wire:model="phone_number" placeholder="{{ __('Enter phone number') }}"
                            required />
                    </x-forms.field>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Address Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('Street Name') }}" name="street_name" required>
                        <x-forms.input name="street_name" wire:model="street_name" placeholder="{{ __('Enter street name') }}"
                            required />
                    </x-forms.field>
                                        <x-forms.field label="{{ __('Street Number') }}" name="street_number" required>
                        <x-forms.input name="street_number" wire:model="street_number" placeholder="{{ __('Enter street number') }}"
                            required />
                    </x-forms.field>
                    <x-forms.field label="{{ __('City') }}" name="city" required>
                        <x-forms.input name="city" wire:model="city" placeholder="{{ __('Enter city') }}" required />
                    </x-forms.field>
                    <x-forms.field label="{{ __('Postal Code') }}" name="postal_code" required>
                        <x-forms.input name="postal_code" wire:model="postal_code" placeholder="{{ __('Enter postal code') }}"
                            required />
                    </x-forms.field>
                    <x-forms.field class="md:col-span-2" label="{{ __('Notes') }}" name="notes"
                        help="{{ __('Optional - Additional notes about the client') }}">
                        <x-forms.textarea name="notes" wire:model="notes" placeholder="{{ __('Enter any additional notes...') }}"
                            rows="3" />
                    </x-forms.field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('clients.index') }}" buttonText="{{ __('Cancel') }}" />
                <x-utils.submit-button wire-target="update" buttonText="{{ __('Update Client') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
