<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="save" class="p-6 space-y-6">
            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Personal Information') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('First Name') }}" name="first_name" required>
                        <x-forms.input name="first_name" wire:model="first_name" placeholder="{{ __('Enter first name') }}" required
                            :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Last Name') }}" name="last_name" required>
                        <x-forms.input name="last_name" wire:model="last_name" placeholder="{{ __('Enter last name') }}" required
                            :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('Email Address') }}" name="email" required
                        help="{{ __('User will use this email to login') }}">
                        <x-forms.input type="email" name="email" wire:model="email" placeholder="{{ __('Enter email address') }}"
                            required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('Profile Picture') }}" name="profile_path"
                        help="{{ __('Optional - Upload a profile picture (max 2MB)') }}">
                        <x-forms.input type="file" name="profile_path" wire:model="profile_path" accept="image/*" />
                        @if ($profile_path)
                            <div class="mt-2">
                                <img src="{{ $profile_path->temporaryUrl() }}"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            </div>
                        @endif
                    </x-forms.field>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Account Settings') }}</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field label="{{ __('Password') }}" name="password" required>
                        <x-forms.input type="password" name="password" wire:model="password"
                            placeholder="{{ __('Enter password') }}" required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field label="{{ __('Confirm Password') }}" name="password_confirmation" required>
                        <x-forms.input type="password" name="password_confirmation" wire:model="password_confirmation"
                            placeholder="{{ __('Confirm password') }}" required :icon="'<path stroke-linecap=\'round\' stroke-linejoin=\'round\' stroke-width=\'2\' d=\'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z\'></path>'" />
                    </x-forms.field>

                    <x-forms.field class="md:col-span-2" label="{{ __('User Role') }}" name="role" required
                        help="{{ __('Select the appropriate user role') }}">
                        <x-forms.select name="role" wire:model="role" placeholder="{{ __('Select user role') }}"
                            :options="$roleOptions" required />
                    </x-forms.field>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('users.index') }}" buttonText="{{ __('Cancel') }}" spacing="" />
                <x-utils.submit-button wire-target="save" buttonText="{{ __('Create User') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>
