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
        $headingFont = \App\Models\SiteSetting::get('heading_font', 'Nunito Sans');
        $bodyFont = \App\Models\SiteSetting::get('body_font', 'Nunito Sans');
        
        // Brand Palette from User
        $peachColor = '#F1C49E';
        $successGreen = '#83C696';
        
        $shouldCollapseSidebar = request()->routeIs(['courses.*', 'catalog.*', 'lessons.*']);
        
        // Prepare Google Fonts URL
        $fonts = array_unique([$headingFont, $bodyFont]);
        $fontParam = implode('&family=', array_map(fn($f) => str_replace(' ', '+', $f) . ':wght@300;400;500;600;700;800;900', $fonts));
        $fontsUrl = "https://fonts.googleapis.com/css2?family={$fontParam}&display=swap";
    @endphp
    <title>@yield('title', 'Личный кабинет') - {{ $siteName }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="{{ $fontsUrl }}" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js -->
    <script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <style>
        :root {
            --color-primary: {{ $primaryColor }};
            --color-secondary: {{ $secondaryColor }};
            --color-accent: {{ $peachColor }};
            --color-success: {{ $successGreen }};
            
            --font-heading: '{{ $headingFont }}', sans-serif;
            --font-body: '{{ $bodyFont }}', sans-serif;
        }

        body {
            font-family: var(--font-body);
            -webkit-font-smoothing: antialiased;
            color: #1f2937;
        }

        h1, h2, h3, h4, .font-heading {
            font-family: var(--font-heading);
            letter-spacing: -0.01em;
        }

        .text-brand { color: var(--color-primary); }
        .bg-brand { background-color: var(--color-primary); }
        .bg-secondary { background-color: var(--color-secondary); }
        .bg-accent { background-color: var(--color-accent); }
        .bg-success { background-color: var(--color-success); }
        
        /* Filament-style Sidebar with Brand colors */
        .sidebar-item {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            margin: 0.125rem 0.75rem;
            color: #4b5563;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            text-decoration: none;
            font-size: 0.9375rem;
            font-weight: 600;
        }
        .sidebar-item:hover {
            background-color: var(--color-secondary);
            color: var(--color-primary);
        }
        .sidebar-item.active {
            background-color: var(--color-primary);
            color: #ffffff;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .sidebar-item svg {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            flex-shrink: 0;
            transition: color 0.2s;
        }
        .sidebar-item.active svg {
            color: #ffffff;
        }
        
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen" x-data="{ sidebarOpen: {{ $shouldCollapseSidebar ? 'false' : 'true' }}, mobileSidebarOpen: false }">
    <div class="flex min-h-screen">
        <!-- Sidebar Overlay (Mobile) -->
        <div x-show="mobileSidebarOpen" 
             @click="mobileSidebarOpen = false"
             x-transition:enter="transition-opacity ease-linear duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-300"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm z-40 lg:hidden" x-cloak></div>

        <!-- Sidebar -->
        <aside :class="{ 'translate-x-0 w-72': sidebarOpen || mobileSidebarOpen, '-translate-x-full lg:translate-x-0 lg:w-0': !sidebarOpen && !mobileSidebarOpen }"
               class="bg-white border-r border-gray-200 overflow-hidden fixed h-full z-50 transition-all duration-300 lg:z-30 shadow-sm">
            
            <div class="w-72 flex flex-col h-full">
                <!-- Logo area -->
                <div class="h-16 flex items-center px-6 border-b border-gray-100 flex-shrink-0">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 overflow-hidden">
                        @if(($brandingType === 'logo' || $brandingType === 'both') && $logoPath && Storage::disk('public')->exists($logoPath))
                            <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-8 max-w-full">
                        @elseif($brandingType !== 'logo')
                            <div class="w-8 h-8 rounded-lg bg-brand flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                {{ substr($siteName, 0, 1) }}
                            </div>
                        @endif
                        
                        @if($brandingType === 'name' || $brandingType === 'both')
                            <span class="font-bold text-lg text-gray-900 truncate tracking-tight">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-4">
                    <div class="px-6 mb-2">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-loose">Обучение</p>
                    </div>
                    
                    <a href="{{ route('catalog.index') }}" class="sidebar-item {{ request()->routeIs('catalog.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Каталог</span>
                    </a>
                    
                    <div class="px-6 mt-6 mb-2 border-t border-gray-50 pt-6">
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest leading-loose">Настройки</p>
                    </div>

                    <a href="{{ route('profile.edit') }}" class="sidebar-item {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Профиль</span>
                    </a>
                </nav>
                
                <!-- Bottom User section -->
                <div class="p-4 border-t border-gray-100 flex-shrink-0 bg-gray-50">
                    <div class="flex items-center group">
                        <div class="w-9 h-9 rounded-full bg-white border border-gray-200 flex items-center justify-center text-xs font-bold text-gray-700 transition group-hover:bg-brand group-hover:text-white">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-900 truncate">{{ auth()->user()->name ?? 'Пользователь' }}</p>
                            <p class="text-[11px] text-gray-400 truncate">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition p-1.5" title="Выйти">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Main Content Area -->
        <main class="flex-1 transition-all duration-300 ease-in-out flex flex-col min-w-0"
              :class="{ 'lg:ml-72': sidebarOpen, 'lg:ml-0': !sidebarOpen }">
            
            <!-- Navbar -->
            <header class="h-16 bg-white/80 backdrop-blur-md border-b border-gray-100 sticky top-0 z-20 px-4 md:px-8 flex items-center justify-between">
                <div class="flex items-center">
                    <button @click="sidebarOpen = !sidebarOpen" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-lg transition mr-4 lg:flex hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path>
                            <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    
                    <button @click="mobileSidebarOpen = true" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:bg-gray-50 rounded-lg transition mr-4 lg:hidden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>

                    <h1 class="font-bold text-lg text-gray-900 truncate">@yield('title')</h1>
                </div>

                <div class="flex items-center space-x-3">
                    <a href="{{ route('catalog.index') }}" class="hidden sm:flex items-center px-4 py-2 bg-accent/10 border border-accent/20 rounded-lg text-sm font-bold text-orange-900 hover:bg-accent/20 transition">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 11-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                        Все курсы
                    </a>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="flex-1 w-full @if(!request()->routeIs('lessons.*')) max-w-7xl mx-auto @endif">
                @if(session('success'))
                <div class="p-6 pb-0">
                    <div class="bg-success/10 border border-success/20 text-green-950 px-4 py-3 rounded-xl flex items-center text-sm shadow-sm ring-1 ring-success/5">
                        <svg class="w-5 h-5 mr-3 text-success" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
                @endif
                
                @yield('content')
            </div>
            
            <footer class="py-10 border-t border-gray-100 mt-auto">
                <div class="max-w-7xl mx-auto px-6 text-center">
                    <p class="text-xs text-gray-400">
                        © {{ date('Y') }} {{ $siteName }}. Растем вместе 🤰
                    </p>
                </div>
            </footer>
        </main>
    </div>

    @stack('scripts')

    <!-- Toast Notification System -->
    <div x-data="{ show: false, message: '', type: 'success', timer: null }"
         @notify.window="message = $event.detail.message; type = $event.detail.type || 'success'; show = true; clearTimeout(timer); timer = setTimeout(() => show = false, 5000);"
         x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full">
        <div :class="{'bg-green-50 border-success/20 shadow-green-100': type === 'success', 'bg-red-50 border-red-100 shadow-red-100': type === 'error'}"
             class="flex items-center p-4 rounded-xl border shadow-xl">
             <div :class="{'text-success': type === 'success', 'text-red-500': type === 'error'}" class="flex-shrink-0 mr-3">
                 <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/></svg>
             </div>
             <p x-text="message" class="text-sm font-bold text-gray-900"></p>
        </div>
    </div>
</body>
</html>
