<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title }} - GloboKids</title>
    <meta name="description" content="{{ Str::limit($course->description, 160) }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            light: '#D0E3F4',
                            peach: '#F1C49E',
                            green: '#83C696',
                            blue: '#4A91CD',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #4A91CD 0%, #83C696 100%); }
        .gradient-text { background: linear-gradient(135deg, #4A91CD 0%, #83C696 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    </style>
</head>
<body class="bg-brand-light min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <a href="{{ route('catalog.index') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="GloboKids" class="h-10">
                </a>
                
                <a href="{{ route('login') }}" class="bg-brand-blue hover:opacity-90 text-white px-4 py-2 rounded-lg font-medium transition">
                    Войти
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Breadcrumb -->
        <nav class="mb-6">
            <a href="{{ route('catalog.index') }}" class="text-brand-blue hover:opacity-80">← Все курсы</a>
        </nav>

        <div class="lg:flex lg:space-x-12">
            <!-- Main Content -->
            <div class="lg:w-2/3">
                <!-- Course Header -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    @if($course->cover_image)
                    <div class="aspect-video bg-gray-100">
                        <img src="{{ Storage::url($course->cover_image) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                    </div>
                    @endif
                    
                    <div class="p-8">
                        <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $course->title }}</h1>
                        
                        @if($course->description)
                        <p class="text-gray-600 text-lg leading-relaxed">{{ $course->description }}</p>
                        @endif
                    </div>
                </div>

                <!-- Course Program -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-900">Программа курса</h2>
                        <p class="text-gray-500 mt-1">{{ $modules->count() }} модулей • {{ $totalLessons }} уроков</p>
                    </div>
                    
                    @foreach($modules as $index => $module)
                    <div class="border-b border-gray-100 last:border-b-0">
                        <div class="p-6">
                            <div class="flex items-start">
                                <span class="flex-shrink-0 w-8 h-8 bg-brand-blue text-white rounded-full flex items-center justify-center font-bold text-sm mr-4">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-grow">
                                    <h3 class="font-semibold text-lg text-gray-900">{{ $module->title }}</h3>
                                    
                                    @if($module->description)
                                    <p class="text-gray-500 text-sm mt-1">{{ $module->description }}</p>
                                    @endif
                                    
                                    <ul class="mt-4 space-y-2">
                                        @foreach($module->publishedLessons as $lesson)
                                        <li class="flex items-center text-gray-600 text-sm">
                                            <svg class="w-4 h-4 text-brand-green mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            {{ $lesson->title }}
                                            
                                            @if($lesson->hasAssignment())
                                            <span class="ml-2 text-xs bg-brand-peach text-gray-700 px-2 py-0.5 rounded-full">Задание</span>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Sidebar - Purchase Card -->
            <aside class="lg:w-1/3 mt-8 lg:mt-0">
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-6 sticky top-6">
                    <div class="text-center mb-6">
                        @if($course->isFree())
                            <span class="text-4xl font-bold text-brand-green">Бесплатно</span>
                            <p class="text-gray-500 mt-1">открытый доступ</p>
                        @else
                            <span class="text-4xl font-bold text-brand-blue">{{ $course->formatted_price }}</span>
                            <p class="text-gray-500 mt-1">единоразовая оплата</p>
                        @endif
                    </div>
                    
                    <ul class="space-y-3 mb-6">
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-brand-green mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ $modules->count() }} модулей
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-brand-green mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ $totalLessons }} видео-уроков
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-brand-green mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Доступ навсегда
                        </li>
                        <li class="flex items-center text-gray-700">
                            <svg class="w-5 h-5 text-brand-green mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Интерактивные задания
                        </li>
                    </ul>
                    
                    @if($course->isFree())
                        @auth
                            <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                @csrf
                                <button type="submit" class="block w-full bg-brand-green text-white text-center font-semibold py-4 px-6 rounded-xl hover:opacity-90 transition shadow-lg">
                                    Записаться бесплатно
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="block w-full bg-brand-green text-white text-center font-semibold py-4 px-6 rounded-xl hover:opacity-90 transition shadow-lg">
                                Войти и записаться
                            </a>
                        @endauth
                    @else
                        <a 
                            href="https://globokids.ru" 
                            target="_blank"
                            class="block w-full bg-brand-blue text-white text-center font-semibold py-4 px-6 rounded-xl hover:opacity-90 transition shadow-lg"
                        >
                            Купить курс
                        </a>
                        <p class="text-center text-gray-400 text-xs mt-4">
                            Безопасная оплата через YoKassa
                        </p>
                    @endif
                </div>
            </aside>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <p class="text-center text-sm">© {{ date('Y') }} GloboKids. Все права защищены.</p>
        </div>
    </footer>
</body>
</html>
