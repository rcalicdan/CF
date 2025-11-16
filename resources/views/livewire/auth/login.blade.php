
<section>
    <x-flash-session />
    <div class="text-center mb-10">
        <!-- Ikonka Androida SVG -->
        <svg class="mx-auto mb-4 text-primary" style="height:3em;width:3em;" viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
            <path d="M380.9 144.3l41.8-72.4c2.7-4.7 1.1-10.7-3.6-13.4-4.7-2.7-10.7-1.1-13.4 3.6l-42.2 73.1c-32.2-13.7-68.2-21.2-106.5-21.2s-74.3 7.5-106.5 21.2l-42.2-73.1c-2.7-4.7-8.7-6.3-13.4-3.6-4.7 2.7-6.3 8.7-3.6 13.4l41.8 72.4C70.7 176.2 32 234.2 32 301.3c0 90.5 86.1 164 192 164s192-73.5 192-164c0-67.1-38.7-125.1-97.1-157zM128 352c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32zm256 0c-17.7 0-32-14.3-32-32s14.3-32 32-32 32 14.3 32 32-14.3 32-32 32z"/>
        </svg>
        <h1 class="text-3xl sm:text-4xl font-bold text-primary-dark">{{ __('Aladynos') }}</h1>
        <p class="mt-2 text-gray-500">{{ __('Enter your admin credentials') }}</p>
    </div>
    <x-forms.auth.login wire:submit.prevent="login" :show-sign-up="false" :showForgotPassword="false" :title="''" :subtitle="''">
        <x-inputs.auth.email wire:model="email" />
        <x-inputs.auth.password wire:model="password" />
    </x-forms.auth.login>
</section>
