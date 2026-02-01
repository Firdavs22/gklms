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
        $headingFont = \App\Models\SiteSetting::get('heading_font', 'Inter');
        $bodyFont = \App\Models\SiteSetting::get('body_font', 'Inter');
        
        $peachColor = '#F1C49E';
        $greenColor = '#83C696';
        
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
            --color-brand: #4A91CD;
            --color-brand-light: #D0E3F4;
            --color-peach: #F1C49E;
            --color-green: #83C696;
            
            --font-heading: '{{ $headingFont }}', sans-serif;
            --font-body: '{{ $bodyFont }}', sans-serif;
        }

        body {
            font-family: var(--font-body);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6, .font-heading {
            font-family: var(--font-heading);
            letter-spacing: -0.02em;
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
            padding: 0.6rem 0.875rem;
            color: #4B5563;
            border-radius: 0.625rem;
            transition: all 0.2s;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .sidebar-link:hover {
            background-color: var(--color-brand-light);
            color: var(--color-brand);
        }
        .sidebar-link.active {
            background-color: var(--color-brand-light);
            color: var(--color-brand);
            font-weight: 700;
        }
        .sidebar-link svg {
            width: 1.15rem;
            height: 1.15rem;
            margin-right: 0.65rem;
            flex-shrink: 0;
        }
        [x-cloak] { display: none !important; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #e2e8f0;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #cbd5e1;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen selection:bg-brand selection:text-white" x-data="{ sidebarOpen: {{ $shouldCollapseSidebar ? 'false' : 'true' }}, mobileSidebarOpen: false }">
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
             class="fixed inset-0 bg-gray-900/40 backdrop-blur-[2px] z-40 lg:hidden" x-cloak></div>

        <!-- Sidebar -->
        <aside :class="{ 'translate-x-0 w-60': sidebarOpen || mobileSidebarOpen, '-translate-x-full lg:translate-x-0 lg:w-0': !sidebarOpen && !mobileSidebarOpen }"
               class="bg-white border-r border-gray-100 fixed h-full overflow-hidden z-50 transition-all duration-300 ease-in-out lg:z-30 shadow-sm">
            
            <div class="w-60 flex flex-col h-full">
                <!-- Logo -->
                <div class="p-5 border-b border-gray-50 flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-2.5 overflow-hidden">
                        @if(($brandingType === 'logo' || $brandingType === 'both') && $logoPath && Storage::disk('public')->exists($logoPath))
                            <img src="{{ Storage::disk('public')->url($logoPath) }}" alt="{{ $siteName }}" class="h-8">
                        @elseif($brandingType !== 'logo')
                            <div class="w-8 h-8 rounded-lg gradient-bg flex items-center justify-center text-white font-black text-sm flex-shrink-0">
                                {{ substr($siteName, 0, 1) }}
                            </div>
                        @endif
                        
                        @if($brandingType === 'name' || $brandingType === 'both')
                            <span class="font-extrabold text-base text-gray-900 truncate tracking-tighter">{{ $siteName }}</span>
                        @endif
                    </a>
                </div>
                
                <!-- Navigation -->
                <nav class="p-3.5 space-y-1 overflow-y-auto flex-1">
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span>Главная</span>
                    </a>
                    
                    <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('courses.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Мои курсы</span>
                    </a>

                    <a href="{{ route('catalog.index') }}" class="sidebar-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        <span>Каталог</span>
                    </a>
                    
                    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span>Профиль</span>
                    </a>
                </nav>
                
                <!-- User Info at Bottom -->
                <div class="p-3.5 border-t border-gray-50 bg-white">
                    <div class="flex items-center">
                        <div class="w-9 h-9 rounded-full bg-brand/10 border border-brand/20 flex items-center justify-center flex-shrink-0">
                            <span class="text-brand font-black text-xs">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                        <div class="ml-2.5 flex-1 min-w-0">
                            <p class="text-xs font-bold text-gray-900 truncate">{{ auth()->user()->name ?? 'Пользователь' }}</p>
                            <p class="text-[9px] font-medium text-gray-400 truncate uppercase tracking-tighter">{{ auth()->user()->email ?? auth()->user()->phone }}</p>
                        </div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-gray-300 hover:text-red-500 transition p-1" title="Выйти">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>
        
        <!-- Header -->
        <div class="fixed top-0 left-0 right-0 h-14 bg-white/70 backdrop-blur-lg border-b border-gray-100/50 z-40 transition-all duration-300 ease-in-out"
             :class="{ 'lg:left-60': sidebarOpen, 'lg:left-0': !sidebarOpen }">
            <div class="flex items-center h-full px-4 lg:px-6">
                <!-- Toggle Button -->
                <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex w-9 h-9 items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition mr-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h16"></path>
                        <path x-show="!sidebarOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Mobile Menu Button -->
                <button @click="mobileSidebarOpen = true" class="lg:hidden w-9 h-9 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-50 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>

                <!-- Page context indicator -->
                <div class="ml-3 font-black text-gray-900 tracking-tighter hidden sm:block text-sm uppercase">
                    @yield('title')
                </div>

                <div class="ml-auto flex items-center space-x-4">
                    <a href="{{ route('catalog.index') }}" class="text-[10px] font-black uppercase tracking-[0.15em] text-brand hover:opacity-70 transition border-b-2 border-brand/20 pb-0.5">В каталог</a>
                </div>
            </div>
        </div>
        
        <!-- Main Content Wrapper -->
        <main class="flex-1 transition-all duration-300 ease-in-out flex flex-col"
              :class="{ 'lg:ml-60': sidebarOpen, 'lg:ml-0': !sidebarOpen }">
            
            <div class="h-14"></div> <!-- Spacer for header -->
            
            <!-- Flash Messages -->
            @if(session('success'))
            <div class="max-w-6xl w-full mx-auto px-4 lg:px-6 pt-4">
                <div class="bg-green-50 border border-green-100/50 text-green-700 px-4 py-2.5 rounded-xl flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs font-bold leading-tight">{{ session('success') }}</span>
                </div>
            </div>
            @endif
            
            @if(session('error'))
            <div class="max-w-6xl w-full mx-auto px-4 lg:px-6 pt-4">
                <div class="bg-red-50 border border-red-100/50 text-red-700 px-4 py-2.5 rounded-xl flex items-center shadow-sm">
                    <svg class="w-4 h-4 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-xs font-bold leading-tight">{{ session('error') }}</span>
                </div>
            </div>
            @endif
            
            <!-- Page Content -->
            <div class="flex-1">
                @yield('content')
            </div>
            
            <!-- Footer -->
            <footer class="mt-auto">
                <div class="max-w-6xl mx-auto px-6 py-6">
                    <p class="text-center text-[9px] font-black uppercase tracking-[0.2em] text-gray-300">
                        © {{ date('Y') }} {{ $siteName }}. Растем вместе 🤰
                    </p>
                </div>
            </footer>
        </main>
    </div>

    @stack('scripts')

    <!-- Toast Notification System -->
    <div x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            timer: null
         }"
         @notify.window="
            message = $event.detail.message;
            type = $event.detail.type || 'success';
            show = true;
            clearTimeout(timer);
            timer = setTimeout(() => show = false, 5000);
         "
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform translate-y-4 scale-95"
         x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
         x-transition:leave-end="opacity-0 transform translate-y-4 scale-95"
         class="fixed bottom-6 right-6 z-[9999] max-w-sm w-full"
         x-cloak>
        <div :class="{
                'bg-white border-green-100 shadow-2xl shadow-green-100/30': type === 'success',
                'bg-white border-red-100 shadow-2xl shadow-red-100/30': type === 'error',
                'bg-white border-blue-100 shadow-2xl shadow-blue-100/30': type === 'info'
             }"
             class="flex items-center p-3.5 rounded-2xl border">
            <div :class="{
                    'bg-green-100 text-green-600': type === 'success',
                    'bg-red-100 text-red-600': type === 'error',
                    'bg-blue-100 text-blue-600': type === 'info'
                 }"
                 class="w-9 h-9 rounded-xl flex items-center justify-center mr-3.5 flex-shrink-0">
                <template x-if="type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </template>
                <template x-if="type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </template>
                <template x-if="type === 'info'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </template>
            </div>
            <div class="flex-grow">
                <p x-text="message" class="text-xs font-black text-gray-900 leading-tight"></p>
            </div>
            <button @click="show = false" class="ml-3 text-gray-300 hover:text-gray-500 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
</body>
</html>
