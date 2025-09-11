<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gmgnet Vacation System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased font-sans flex flex-col min-h-screen">

    <header class="absolute top-0 left-0 w-full z-20">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center bg-transparent">
            <h1 class="text-3xl font-extrabold text-white">Gmgnet Vacation System</h1>
            <div class="space-x-4">
                <a href="{{ route('login') }}" class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg shadow hover:bg-blue-700 transition">Login</a>
            </div>
        </div>
    </header>

    <main class="flex-1 relative bg-cover bg-center h-screen" 
          style="background-image: url('{{ asset('images/hero-bg.webp') }}');">
        <div class="absolute inset-0 bg-black bg-opacity-50 flex flex-col justify-center items-center text-center px-6">
            <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-6">Welcome to Gmgnet Vacation System</h1>
            <p class="text-lg md:text-xl text-white mb-8 max-w-2xl">Simplifying vacation management for employees and administrators. Track requests, approve efficiently, and stay organized—all in one place.</p>
        </div>
    </main>

    <footer class="bg-gray-800 text-white py-6 text-center">
        &copy; {{ date('Y') }} {{ config('app.name', 'Gmgnet') }}. All rights reserved.
    </footer>

</body>
</html>
