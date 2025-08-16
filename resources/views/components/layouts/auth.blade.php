@props([
    'title' => 'Aladynos',
    'brandTitle' => 'System zarządzania pralnią',
    'brandSubtitle' => 'Usprawnij swoje operacje, śledź zamówienia i zwiększaj przychody, wszystko w jednym miejscu.',
    'showBrandPanel' => true,
])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>

    {{ $head ?? '' }}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles()
</head>

<body class="font-sans antialiased text-gray-800">
    <div class="min-h-screen">
        <div class="grid {{ $showBrandPanel ? 'lg:grid-cols-2' : 'grid-cols-1' }} min-h-screen">
            @if ($showBrandPanel)
                <!-- Left Panel: Illustration and Branding -->
                <div class="hidden lg:flex flex-col items-center justify-center bg-primary-light p-12 text-center">
                    <div class="w-full max-w-md">
                        {{ $brandLogo ?? '' }}
                        @if (empty($brandLogo))
                            <svg class="w-48 mx-auto text-primary" viewBox="0 0 100 100" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    d="M85.4166 41.6667C85.4166 41.6667 83.3333 25 68.75 25C54.1666 25 54.1666 37.5 50 37.5C45.8333 37.5 45.8333 25 31.25 25C16.6666 25 14.5833 41.6667 14.5833 41.6667"
                                    stroke="currentColor" stroke-width="5" stroke-miterlimit="10" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M14.5833 41.6667H85.4166" stroke="currentColor" stroke-width="5"
                                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                <path
                                    d="M18.75 41.6667V75C18.75 77.3012 20.6988 79.1667 23.0001 79.1667H76.9999C79.3011 79.1667 81.25 77.3012 81.25 75V41.6667"
                                    stroke="currentColor" stroke-width="5" stroke-miterlimit="10" stroke-linecap="round"
                                    stroke-linejoin="round" />
                                <path d="M33.3333 54.1667H66.6667" stroke="currentColor" stroke-width="5"
                                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M33.3333 66.6667H54.1667" stroke="currentColor" stroke-width="5"
                                    stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        @endif
                        <h2 class="mt-8 text-4xl font-bold text-primary-dark">{{ $brandTitle }}</h2>
                        <p class="mt-4 text-lg text-gray-600">{{ $brandSubtitle }}</p>
                    </div>
                </div>
            @endif

            <!-- Right Panel: Main Content -->
            <div class="flex flex-col justify-center items-center p-6 sm:p-12">
                <div class="w-full max-w-sm">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>

</html>
