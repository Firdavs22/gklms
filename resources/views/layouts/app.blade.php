<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = \App\Models\SiteSetting::get('site_name', 'GloboKids Edu');
        $logoPath = \App\Models\SiteSetting::get('logo_path');
        $primaryColor = \App\Models\SiteSetting::get('primary_color', '#7c3aed');
        $secondaryColor = \App\Models\SiteSetting::get('secondary_color', '#a855f7');
    @endphp
    <title>@yield('title', 'Личный кабинет') - {{ $siteName }}</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '{{ $primaryColor }}10',
                            100: '{{ $primaryColor }}20',
                            200: '{{ $primaryColor }}40',
                            300: '{{ $primaryColor }}60',
                            400: '{{ $primaryColor }}80',
                            500: '{{ $primaryColor }}',
                            600: '{{ $primaryColor }}',
                            700: '{{ $primaryColor }}',
                            800: '{{ $primaryColor }}',
                            900: '{{ $primaryColor }}',
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-secondary: {{ $secondaryColor }};
        }
        .gradient-bg {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
        }
        .text-brand {
            color: {{ $primaryColor }};
        }
        .bg-brand {
            background-color: {{ $primaryColor }};
        }
        .border-brand {
            border-color: {{ $primaryColor }};
        }
        .card-hover {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: opacity 0.2s;
        }
        .btn-primary:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                        @if($logoPath)
                            <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-8">
                        @else
                            <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white font-bold">
                                {{ substr($siteName, 0, 1) }}
                            </div>
                        @endif
                        <span class="font-bold text-xl text-gray-900">{{ $siteName }}</span>
                    </a>
                </div>
                
                @auth
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600">{{ auth()->user()->name ?? auth()->user()->email ?? auth()->user()->phone }}</span>
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                            Выйти
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    </div>
    @endif
    
    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-100 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <p class="text-center text-sm text-gray-500">
                © {{ date('Y') }} {{ $siteName }}. Все права защищены.
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
