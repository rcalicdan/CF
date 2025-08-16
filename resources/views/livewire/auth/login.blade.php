<section>
    <x-flash-session />
    <x-forms.auth.login wire:submit.prevent="login" :title="__('Admin Access')" :subtitle="__('Enter your admin credentials')" :show-sign-up="false"
        :showForgotPassword="false">
        <x-inputs.auth.email wire:model="email" />
        <x-inputs.auth.password wire:model="password" />
    </x-forms.auth.login>
</section>
