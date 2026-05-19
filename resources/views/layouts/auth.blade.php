<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login - Acufara AI Clinic')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        /* Custom colors for Sage Green and Beige */
        :root {
            --color-sage: #9cb4a1;
            --color-beige: #f5f5dc;
        }
        body {
            background-color: var(--color-beige);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 font-sans antialiased text-gray-900">
    
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <h2 class="mt-6 text-center text-3xl font-extrabold" style="color: var(--color-sage);">
            Acufara Clinic & Spa
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Klinik Akupunktur & Kecantikan dengan Asisten AI
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-4 shadow sm:rounded-lg sm:px-10 border-t-4" style="border-color: var(--color-sage);">
            @yield('content')
        </div>
    </div>

</body>
</html>
