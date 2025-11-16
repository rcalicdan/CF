<x-layouts.auth title="Admin Login" brand-title="Admin Panel">
    <x-forms.auth.login title="Admin Accesss" subtitle="Enter your admin credentials" :show-sign-up="false">
        <x-inputs.auth.email/>
        <x-inputs.auth.password/>
        <x-inputs.auth.remember-me/>
    </x-forms.auth.login>
</x-layouts.auth>