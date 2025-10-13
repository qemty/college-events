<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
            <div class="max-w-7xl mx-auto p-6 lg:p-8">
                <div class="flex justify-center">
                    <svg viewBox="0 0 100 100" class="w-20 h-20 fill-current text-gray-800">
                        <path d="M50 0C22.4 0 0 22.4 0 50s22.4 50 50 50 50-22.4 50-50S77.6 0 50 0zM25.6 71.2V28.8h8.8v35l26.4-35h7.2v42.4h-8.8V36.8l-26.4 35z"/>
                    </svg>
                </div>

                <h1 class="text-4xl font-bold text-center text-gray-800 dark:text-gray-200">Добро пожаловать</h1>

                <div class="mt-8 flex justify-center">
                    <a href="{{ route('login') }}" class="text-sm text-gray-600 dark:text-gray-400 underline">Авторизация</a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="ml-4 text-sm text-gray-600 dark:text-gray-400 underline">Регистрация</a>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>