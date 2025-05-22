<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&display=swap" rel="stylesheet">
    
    <title>{{ $title ?? 'Product Management System' }}</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

</head>
<body class=" font-sans bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-xl font-semibold">Product Management System</h1>
                <nav class="flex space-x-4">
                  
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
    
    
    <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2"></div>
    <script type="module" src="{{ asset('js/app.js') }}"></script>
</body>
</html>