<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback</title>
    @include('contents.reviews.assets.css')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-main font-sans leading-normal tracking-normal">

    <div class="min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8">
        @livewire('client-feedback.submit-feedback')
    </div>

    @livewireScripts
</body>

</html>