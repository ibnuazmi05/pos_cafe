<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'POS Cafe') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-stone-900 antialiased selection:bg-amber-100 selection:text-amber-900">
    <div class="relative min-h-screen flex flex-col justify-center items-center px-4 py-12 bg-cover bg-center bg-no-repeat"
        style="background-image: url('{{ asset('images/cafe-bg.jpg') }}');">

        {{-- Solid dark backdrop without gradients --}}
        <div class="absolute inset-0 bg-stone-950/80"></div>

        <div class="relative z-10 w-full sm:max-w-md">
            <div class="bg-white shadow-2xl rounded-2xl border border-stone-200/90 p-8 sm:p-10">
                {{ $slot }}
            </div>
            <p class="mt-6 text-center text-xs text-stone-400">
                &copy; {{ date('Y') }} POS Cafe.
            </p>
        </div>
    </div>
</body>

</html>
