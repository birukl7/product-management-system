<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ $title ?? 'Product Management System' }}</title>
    
    <!-- Styles -->
    @vite('resources/css/app.css')
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-xl font-semibold">Product Management System</h1>
                <nav class="flex space-x-4">
                    <a href="#" class="text-gray-600 hover:text-gray-900">Dashboard</a>
                    <a href="#" class="text-gray-900 font-medium">Products</a>
                    <a href="#" class="text-gray-600 hover:text-gray-900">Categories</a>
                </nav>
            </div>
        </header>
        
        <main class="flex-1">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
        
        <footer class="bg-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <p class="text-sm text-gray-500 text-center">© {{ date('Y') }} Product Management System</p>
            </div>
        </footer>
    </div>
    
    <!-- Toast Notification Container -->
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>
    
    <!-- Scripts -->
    @vite('resources/js/app.js')
    {{ $scripts ?? '' }}
</body>
</html>