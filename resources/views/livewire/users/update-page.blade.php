<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 my-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form wire:submit.prevent="update" x-data="{ showPasswordFields: false, isActive: @entangle('active') }" class="p-6 space-y-6">
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
                    <x-forms.field class="md:col-span-2" label="{{ __('Email Address') }}" name="email" required
                        help="{{ __('User will use this email to login') }}">
                        <x-forms.input type="email" name="email" wire:model="email" placeholder="{{ __('Enter email address') }}"
                            required />
                    </x-forms.field>
                    <x-forms.field class="md:col-span-2" label="{{ __('Profile Picture') }}" name="profile_path"
                        help="{{ __('Optional - Upload a new profile picture (max 2MB)') }}">
                        <x-forms.input type="file" name="profile_path" wire:model="profile_path" accept="image/*" />
                        <div class="mt-2">
                            @if ($profile_path)
                                <img src="{{ $profile_path->temporaryUrl() }}"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            @elseif($existing_profile_path)
                                <img src="{{ Storage::url($existing_profile_path) }}"
                                    class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                            @endif
                        </div>
                    </x-forms.field>
                </div>
            </div>

            <div class="space-y-6">
                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                    {{ __('Account Settings') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-forms.field class="md:col-span-2" label="{{ __('User Role') }}" name="role" required
                        help="{{ __('Select the appropriate user role') }}">
                        <x-forms.select name="role" wire:model="role" placeholder="{{ __('Select user role') }}"
                            :options="$roleOptions" required />
                    </x-forms.field>

                    {{-- Only show the active status toggle if the user is NOT editing their own profile --}}
                    @if ($user->id !== auth()->id())
                        <div class="md:col-span-2 flex items-center">
                            <span class="text-sm font-medium text-gray-900 mr-3" id="active-status-label">
                                {{ __('Active Status') }}
                            </span>
                            <button type="button"
                                class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 transition-all duration-200 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 hover:scale-105"
                                :class="{
                                    'bg-green-500 border-green-600 shadow-lg shadow-green-500/30 focus:ring-green-500': isActive,
                                    'bg-gray-300 border-gray-400 shadow-md focus:ring-gray-400': !isActive
                                }"
                                role="switch"
                                :aria-checked="isActive.toString()"
                                @click="isActive = !isActive"
                                aria-labelledby="active-status-label">
                                <span class="pointer-events-none relative inline-block h-6 w-6 transform rounded-full shadow-lg transition-all duration-200 ease-out border-2"
                                    :class="{
                                        'translate-x-6 bg-white border-green-100': isActive,
                                        'translate-x-0 bg-gray-100 border-gray-300': !isActive
                                    }"
                                    style="top: -1px;">
                                </span>
                            </button>
                            <span class="ml-3 text-sm transition-all duration-200" 
                                :class="isActive ? 'text-green-600 font-medium' : 'text-gray-500'">
                                <span x-show="isActive">{{ __('Active') }}</span>
                                <span x-show="!isActive">{{ __('Inactive') }}</span>
                            </span>
                        </div>
                    @endif

                    <div class="md:col-span-2 flex items-center">
                        <span class="text-sm font-medium text-gray-900 mr-3" id="update-password-label">
                            {{ __('Update Password') }}
                        </span>

                        <button type="button"
                            class="relative inline-flex h-8 w-14 flex-shrink-0 cursor-pointer rounded-full border-2 transition-all duration-700 ease-out focus:outline-none focus:ring-2 focus:ring-offset-2 hover:scale-105"
                            :class="{
                                'bg-yellow-400 border-yellow-500 shadow-lg shadow-yellow-400/50 focus:ring-yellow-400': showPasswordFields,
                                'bg-gray-300 border-gray-400 shadow-md focus:ring-gray-400': !showPasswordFields
                            }"
                            role="switch" :aria-checked="showPasswordFields.toString()"
                            @click="showPasswordFields = !showPasswordFields" aria-labelledby="update-password-label">

                            <!-- Light bulb toggle circle -->
                            <span
                                class="pointer-events-none relative inline-block h-6 w-6 transform rounded-full shadow-xl transition-all duration-700 ease-out border-2"
                                :class="{
                                    'translate-x-6 bg-yellow-200 border-yellow-300 shadow-yellow-300/50': showPasswordFields,
                                    'translate-x-0 bg-gray-100 border-gray-300 shadow-gray-300/50': !showPasswordFields
                                }"
                                style="top: -1px;">

                                <span class="absolute inset-0 rounded-full transition-all duration-700"
                                    :class="showPasswordFields ? 'bg-yellow-300 opacity-60 blur-md animate-pulse' :
                                        'bg-transparent'">
                                </span>

                                <span class="absolute inset-1 rounded-full transition-all duration-700"
                                    :class="showPasswordFields ?
                                        'bg-gradient-to-br from-yellow-100 to-yellow-200 shadow-inner' :
                                        'bg-gradient-to-br from-gray-50 to-gray-100 shadow-inner'">
                                </span>

                                <span class="absolute inset-2 rounded-full transition-all duration-700"
                                    :class="showPasswordFields ? 'bg-yellow-400 opacity-80 animate-pulse' :
                                        'bg-gray-400 opacity-20'">
                                </span>
                            </span>

                            <span
                                class="absolute inset-0 flex items-center justify-center text-sm transition-all duration-500"
                                :class="{
                                    'text-yellow-800 opacity-100': showPasswordFields,
                                    'text-gray-600 opacity-70': !showPasswordFields
                                }">
                                <span x-show="showPasswordFields" x-transition class="animate-pulse">💡</span>
                                <span x-show="!showPasswordFields" x-transition>🔌</span>
                            </span>

                            <!-- Outer glow when "on" -->
                            <span class="absolute inset-0 rounded-full transition-all duration-700 pointer-events-none"
                                :class="showPasswordFields ? 'shadow-lg shadow-yellow-400/40 ring-4 ring-yellow-200/30' : ''">
                            </span>
                        </button>
                    </div>

                    <div x-show="showPasswordFields" x-transition
                        class="md:col-span-2 space-y-6 border-t border-gray-200 pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <x-forms.field label="{{ __('New Password') }}" name="password"
                                help="{{ __('Leave blank to keep the current password.') }}">
                                <x-forms.input type="password" name="password" wire:model="password"
                                    placeholder="{{ __('Enter new password') }}" />
                            </x-forms.field>
                            <x-forms.field label="{{ __('Confirm New Password') }}" name="password_confirmation">
                                <x-forms.input type="password" name="password_confirmation" wire:model="password_confirmation"
                                    placeholder="{{ __('Confirm new password') }}" />
                            </x-forms.field>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <x-utils.link-button href="{{ route('users.index') }}" buttonText="{{ __('Cancel') }}" />
                <x-utils.submit-button wire-target="update" buttonText="{{ __('Update User') }}" bgColor="bg-indigo-600"
                    hoverColor="hover:bg-indigo-700" focusRing="focus:ring-indigo-500" />
            </div>
        </form>
    </div>
</div>