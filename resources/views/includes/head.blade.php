<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="token" content="{{ auth()->user()->bearerToken() }}">
    <title>{{ $title }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style"
        onload="this.rel='stylesheet'">
    <link rel="preload" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css"
        as="style" onload="this.rel='stylesheet'">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style"
        onload="this.rel='stylesheet'">
    <link rel="stylesheet" href="/css/styles.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
    <script src="/js/route-data-service.js"></script>
    <script src="/js/route-optimizer.js"></script>
    <script src="/js/map-manager.js"></script>
    <script src="/js/route-optimizer-service.js"></script>
    <script src="/js/alpine-component.js"></script>
    @stack('styles');
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
