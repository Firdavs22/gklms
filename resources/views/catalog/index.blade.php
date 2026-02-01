<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Онлайн-курсы для детей - GloboKids</title>
    <meta name="description" content="Развивающие онлайн-курсы для детей от GloboKids. Интерактивное обучение с видео-уроками и увлекательными заданиями.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            light: '#D0E3F4',  // Light blue backgrounds
                            peach: '#F1C49E',  // Peach
                            green: '#83C696',  // Green
                            blue: '#4A91CD',   // Blue buttons
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #4A91CD 0%, #83C696 100%); }
        .gradient-text { background: linear-gradient(135deg, #4A91CD 0%, #83C696 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(0,0,0,0.15); }
    </style>
</head>
<body class="bg-brand-light min-h-screen">
    <!-- Hero Section -->
    <header class="gradient-bg text-white">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <a href="{{ route('catalog.index') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="GloboKids" class="h-10 bg-white rounded-lg p-1">
                </a>
                
                <a href="{{ route('login') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg font-medium transition">
                    Войти
                </a>
            </div>
        </nav>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-6">
                Развивающие курсы<br>для детей онлайн
            </h1>
            <p class="text-xl text-white/80 max-w-2xl mx-auto">
                Интерактивные видео-уроки, увлекательные задания и персональный прогресс. Обучайтесь в своём темпе!
            </p>
        </div>
    </header>

    <!-- Courses Section -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h2 class="text-2xl font-bold text-gray-900 mb-8">Наши курсы</h2>
        
        @if($courses->isEmpty())
        <div class="text-center py-16">
            <p class="text-gray-500 text-lg">Курсы скоро появятся!</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($courses as $course)
            <a href="{{ route('catalog.show', $course) }}" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover block">
                @if($course->cover_image)
                <div class="aspect-video bg-gray-100">
                    <img src="{{ Storage::url($course->cover_image) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                </div>
                @else
                <div class="aspect-video gradient-bg flex items-center justify-center">
                    <span class="text-6xl">📚</span>
                </div>
                @endif
                
                <div class="p-6">
                    <h3 class="font-bold text-xl text-gray-900 mb-2">{{ $course->title }}</h3>
                    
                    @if($course->description)
                    <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ Str::limit($course->description, 100) }}</p>
                    @endif
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">{{ $course->modules_count }} модулей</span>
                        <span class="text-xl font-bold text-brand-blue">{{ $course->formatted_price }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="flex items-center mb-4 md:mb-0">
                    <img src="{{ asset('images/logo.png') }}" alt="GloboKids" class="h-8 bg-white rounded p-1">
                </div>
                
                <p class="text-sm">© {{ date('Y') }} GloboKids. Все права защищены.</p>
            </div>
        </div>
    </footer>
</body>
</html>
