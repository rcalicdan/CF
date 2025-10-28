@props(['title' => config('app.name', 'LaundryManager')])

<!DOCTYPE html>
<html lang="en">

<head>
    @include('includes.head')
    <style>
        a,
        button,
        [type="button"],
        [type="submit"],
        [type="reset"],
        select,
        [role="button"],
        .cursor-pointer,
        label[for],
        input[type="checkbox"],
        input[type="radio"] {
            cursor: pointer !important;
        }

        button:disabled,
        [type="button"]:disabled,
        [type="submit"]:disabled,
        [type="reset"]:disabled,
        select:disabled,
        [disabled] {
            cursor: not-allowed !important;
        }
    </style>
</head>

<body class="font-sans">

    <div x-data="{
        isMobileMenuOpen: false,
        isDesktopSidebarCollapsed: $persist(false).as('sidebarCollapsed'),
        dropdownOpen: false
    }" x-cloak>

        <x-partials.dashboard.sidebar />

        <div class="flex flex-col flex-1 transition-all duration-300 ease-in-out"
            :class="{ 'md:ml-64': !isDesktopSidebarCollapsed, 'md:ml-20': isDesktopSidebarCollapsed }">

            <x-partials.dashboard.header />

            <main class="flex-1 overflow-x-hidden overflow-y-auto"
                @click="if (isMobileMenuOpen) { isMobileMenuOpen = false }">
                <div class="container mx-auto px-6 py-8">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    @livewireScripts
    @stack('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/css/splide.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@latest/dist/js/splide.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</body>

</html>