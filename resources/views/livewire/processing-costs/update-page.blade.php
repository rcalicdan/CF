<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="update" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Processing Cost Information') }}
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field class="md:col-span-2" label="{{ __('Cost Name') }}" name="name" required>
                        <x-forms.input name="name" wire:model="name" placeholder="{{ __('Enter cost name') }}" required />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Cost Type') }}" name="type" required>
                        <x-forms.select name="type" wire:model="type" placeholder="{{ __('Select cost type') }}"
                            :options="$typeOptions" required />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Amount') }}" name="amount" required>
                        <x-forms.input type="number" step="0.01" min="0" name="amount" wire:model="amount" 
                            placeholder="{{ __('Enter amount') }}" required />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('Cost Date') }}" name="cost_date" required>
                        <x-forms.input type="date" name="cost_date" wire:model="cost_date" required />
                    </x-forms.field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('processing-costs.index') }}" buttonText="{{ __('Cancel') }}" />
                <x-utils.submit-button wire-target="update" buttonText="{{ __('Update Processing Cost') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>