<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library System</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 text-white flex flex-col">
            <div class="p-6 text-2xl font-bold border-b border-slate-700">
                LibrarySystem
            </div>
            <nav class="flex-1 p-4 space-y-2">
                <a href="{{ route('books.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-slate-700 hover:text-white {{ request()->routeIs('books.*') ? 'bg-indigo-500' : '' }}">
                    📚 Books
                </a>
                <a href="{{ route('students.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-slate-700 hover:text-white {{ request()->routeIs('students.*') ? 'bg-indigo-500' : '' }}">
                    🎓 Students
                </a>
                <a href="{{ route('fines.index') }}" class="block py-2.5 px-4 rounded transition duration-200 hover:bg-slate-700 hover:text-white {{ request()->routeIs('fines.*') ? 'bg-indigo-500' : '' }}">
                    💰 Fines
                </a>
            </nav>
            <div class="p-4 border-t border-slate-700 text-sm text-slate-400">
                &copy; {{ date('Y') }} Library
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <h1 class="text-3xl font-bold text-gray-900">
                        @yield('header')
                    </h1>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 p-6">
                @if(session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
