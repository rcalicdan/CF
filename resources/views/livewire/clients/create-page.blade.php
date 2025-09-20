<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Personal Information') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('First Name') }}" name="first_name" required>
                        <x-forms.input name="first_name" wire:model="first_name" placeholder="{{ __('Enter first name') }}" required
                            :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Last Name') }}" name="last_name" required>
                        <x-forms.input name="last_name" wire:model="last_name" placeholder="{{ __('Enter last name') }}" required
                            :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Phone Number') }}" name="phone_number" required>
                        <x-forms.input name="phone_number" wire:model="phone_number" placeholder="{{ __('Enter phone number') }}"
                            required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z\'></path>'" />
                    </x-forms.field>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Address Information') }}
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('Street Number') }}" name="street_number" required>
                        <x-forms.input name="street_number" wire:model="street_number" placeholder="{{ __('Enter street number') }}"
                            required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z\'></path><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 11a3 3 0 11-6 0 3 3 0 016 0z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Street Name') }}" name="street_name" required>
                        <x-forms.input name="street_name" wire:model="street_name" placeholder="{{ __('Enter street name') }}"
                            required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z\'></path><path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M15 11a3 3 0 11-6 0 3 3 0 016 0z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('City') }}" name="city" required>
                        <x-forms.input name="city" wire:model="city" placeholder="{{ __('Enter city') }}" required
                            :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Postal Code') }}" name="postal_code" required>
                        <x-forms.input name="postal_code" wire:model="postal_code" placeholder="{{ __('Enter postal code') }}"
                            required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('Notes') }}" name="notes"
                        help="{{ __('Optional - Additional notes about the client') }}">
                        <x-forms.textarea name="notes" wire:model="notes" placeholder="{{ __('Enter any additional notes...') }}"
                            rows="3" />
                    </x-forms.field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('clients.index') }}" buttonText="{{ __('Cancel') }}" spacing="" />
                <x-utils.submit-button wire-target="save" buttonText="{{ __('Create Client') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
