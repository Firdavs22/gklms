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
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: #4B5563;
            border-radius: 0.75rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        .sidebar-link:hover {
            background-color: #F3E8FF;
            color: #7C3AED;
        }
        .sidebar-link.active {
            background-color: #EDE9FE;
            color: #6D28D9;
            font-weight: 500;
        }
        .sidebar-link svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 fixed h-full overflow-y-auto hidden lg:block">
            <!-- Logo -->
            <div class="p-6 border-b border-gray-100">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                    @if($logoPath && Storage::disk('public')->exists($logoPath))
                        <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-10">
                    @else
                        <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($siteName, 0, 1) }}
                        </div>
                    @endif
                    <span class="font-bold text-lg text-gray-900">{{ $siteName }}</span>
                </a>
            </div>
            
            <!-- Navigation -->
            <nav class="p-4 space-y-1">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Главная
                </a>
                
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                    Мои курсы
                </a>
                
                <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Профиль
                </a>
                
                <a href="{{ route('profile.edit') }}#security" class="sidebar-link">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Настройки
                </a>
            </nav>
            
            <!-- User Info at Bottom -->
            <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center">
                        <span class="text-purple-600 font-medium">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="ml-3 flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name ?? 'Пользователь' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-red-500 transition" title="Выйти">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Mobile Header -->
        <div class="lg:hidden fixed top-0 left-0 right-0 bg-white border-b border-gray-200 z-50">
            <div class="flex items-center justify-between px-4 h-16">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    @if($logoPath && Storage::disk('public')->exists($logoPath))
                        <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-8">
                    @else
                        <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white font-bold">
                            {{ substr($siteName, 0, 1) }}
                        </div>
                    @endif
                    <span class="font-bold text-gray-900">{{ $siteName }}</span>
                </a>
                <button id="mobile-menu-btn" class="text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu Overlay -->
        <div id="mobile-menu" class="lg:hidden fixed inset-0 bg-black/50 z-50 hidden">
            <div class="w-64 bg-white h-full">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                    <span class="font-bold text-gray-900">Меню</span>
                    <button id="close-menu-btn" class="text-gray-500">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <nav class="p-4 space-y-1">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        Главная
                    </a>
                    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        Профиль
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="sidebar-link text-red-600 w-full">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Выйти
                        </button>
                    </form>
                </nav>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="flex-1 lg:ml-64">
            <div class="lg:hidden h-16"></div>
            
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="max-w-6xl mx-auto px-6 pt-6">
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('success') }}
                </div>
            </div>
            @endif
            
            @if(session('error'))
            <div class="max-w-6xl mx-auto px-6 pt-6">
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ session('error') }}
                </div>
            </div>
            @endif
            
            <!-- Page Content -->
            <div class="max-w-6xl mx-auto px-6 py-8">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="border-t border-gray-100 mt-auto">
                <div class="max-w-6xl mx-auto px-6 py-6">
                    <p class="text-center text-sm text-gray-400">
                        © {{ date('Y') }} {{ $siteName }}. Все права защищены.
                    </p>
                </div>
            </footer>
        </main>
    </div>

    <script>
        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeMenuBtn = document.getElementById('close-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', () => mobileMenu.classList.remove('hidden'));
        }
        if (closeMenuBtn) {
            closeMenuBtn.addEventListener('click', () => mobileMenu.classList.add('hidden'));
        }
        if (mobileMenu) {
            mobileMenu.addEventListener('click', (e) => {
                if (e.target === mobileMenu) mobileMenu.classList.add('hidden');
            });
        }
    </script>
    @stack('scripts')
</body>
</html>
