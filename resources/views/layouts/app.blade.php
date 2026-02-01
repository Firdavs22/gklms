<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = \App\Models\SiteSetting::get('site_name', 'GloboKids Edu');
        $logoPath = \App\Models\SiteSetting::get('logo_path');
        $brandingType = \App\Models\SiteSetting::get('branding_display_type', 'name');
        $primaryColor = \App\Models\SiteSetting::get('primary_color', '#4A91CD');
        $secondaryColor = \App\Models\SiteSetting::get('secondary_color', '#D0E3F4');
        $peachColor = '#F1C49E';
        $greenColor = '#83C696';
    @endphp
    <title>@yield('title', 'Личный кабинет') - {{ $siteName }}</title>
    
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-secondary: {{ $secondaryColor }};
            --color-brand: #4A91CD;
            --color-brand-light: #D0E3F4;
            --color-peach: #F1C49E;
            --color-green: #83C696;
        }
        .gradient-bg {
            background: linear-gradient(135deg, var(--color-brand) 0%, var(--color-green) 100%);
        }
        .text-brand {
            color: var(--color-brand);
        }
        .bg-brand {
            background-color: var(--color-brand);
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
            background-color: var(--color-brand-light);
            color: var(--color-brand);
        }
        .sidebar-link.active {
            background-color: var(--color-brand-light);
            color: var(--color-brand);
            font-weight: 600;
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
                    @if(($brandingType === 'logo' || $brandingType === 'both') && $logoPath && Storage::disk('public')->exists($logoPath))
                        <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-10">
                    @elseif($brandingType !== 'logo')
                        <div class="w-10 h-10 rounded-xl gradient-bg flex items-center justify-center text-white font-bold text-lg">
                            {{ substr($siteName, 0, 1) }}
                        </div>
                    @endif
                    
                    @if($brandingType === 'name' || $brandingType === 'both')
                        <span class="font-bold text-lg text-gray-900">{{ $siteName }}</span>
                    @endif
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
            <div class="flex items-center justify-center px-4 h-16">
                <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                    @if(($brandingType === 'logo' || $brandingType === 'both') && $logoPath && Storage::disk('public')->exists($logoPath))
                        <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-8">
                    @elseif($brandingType !== 'logo')
                        <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white font-bold">
                            {{ substr($siteName, 0, 1) }}
                        </div>
                    @endif

                    @if($brandingType === 'name' || $brandingType === 'both')
                        <span class="font-bold text-gray-900">{{ $siteName }}</span>
                    @endif
                </a>
            </div>
        </div>
        
        <!-- Main Content -->
        <main class="flex-1 lg:ml-64 pb-20 lg:pb-0">
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
            
            <!-- Footer (Desktop only) -->
            <footer class="border-t border-gray-100 mt-auto hidden lg:block">
                <div class="max-w-6xl mx-auto px-6 py-6">
                    <p class="text-center text-sm text-gray-400">
                        © {{ date('Y') }} {{ $siteName }}. Все права защищены.
                    </p>
                </div>
            </footer>
        </main>

        <!-- Bottom Navigation Bar (Mobile only) -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50 flex items-center justify-around pb-safe">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-3 px-5 {{ request()->routeIs('dashboard') ? 'text-brand' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <span class="text-[10px] mt-1">Главная</span>
            </a>
            
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center py-3 px-5 {{ request()->routeIs('courses.*') ? 'text-brand' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="text-[10px] mt-1">Курсы</span>
            </a>
            
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center py-3 px-5 {{ request()->routeIs('profile.*') ? 'text-brand' : 'text-gray-400' }}">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                <span class="text-[10px] mt-1">Профиль</span>
            </a>
            
            <form action="{{ route('logout') }}" method="POST" id="logout-form-mobile">
                @csrf
                <button type="submit" class="flex flex-col items-center py-3 px-5 text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    <span class="text-[10px] mt-1">Выход</span>
                </button>
            </form>
        </nav>
    </div>

    <script>
        // Any general scripts can go here
    </script>
    @stack('scripts')
</body>
</html>
