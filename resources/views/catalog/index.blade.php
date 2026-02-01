@extends('layouts.app')

@section('title', 'Каталог курсов')

@section('content')
<div class="-mx-6 -mt-8 mb-12">
    <!-- Premium Hero Section -->
    <div class="relative overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <svg class="hidden lg:block absolute right-0 inset-y-0 h-full w-48 text-white transform translate-x-1/2" fill="currentColor" viewBox="0 0 100 100" preserveAspectRatio="none" aria-hidden="true">
                    <polygon points="50,0 100,0 50,100 0,100" />
                </svg>

                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-bold text-gray-900 sm:text-5xl md:text-6xl">
                            <span class="block xl:inline">Растем вместе:</span>
                            <span class="block text-brand xl:inline">от первой улыбки до первого класса</span>
                        </h1>
                        <p class="mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                            Пошаговые онлайн-курсы для родителей, которые хотят понимать своего ребенка и наслаждаться материнством без лишней тревоги.
                        </p>
                        <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                            <div class="rounded-md shadow">
                                <a href="#courses" class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-brand hover:opacity-90 md:py-4 md:text-lg md:px-10 transition shadow-lg">
                                    Начать путь к спокойному родительству
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2">
            <div class="h-56 w-full sm:h-72 md:h-96 lg:w-full lg:h-full gradient-bg opacity-10 flex items-center justify-center">
                <span class="text-9xl">🤱</span>
            </div>
        </div>
    </div>
</div>

<div id="courses" class="max-w-7xl mx-auto">
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 space-y-4 md:space-y-0">
        <div>
            <h2 class="text-3xl font-bold text-gray-900">Наши программы</h2>
            <p class="text-gray-500 mt-1">Выберите подходящий курс для вашего этапа родительства</p>
        </div>
        
        <!-- Simple Filter (Visual only for now) -->
        <div class="flex items-center space-x-2 overflow-x-auto pb-2 md:pb-0">
            <button class="px-4 py-2 bg-brand text-white rounded-full text-sm font-medium whitespace-nowrap shadow-sm">Все</button>
            <button class="px-4 py-2 bg-white text-gray-600 border border-gray-200 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-50 transition">Для мам</button>
            <button class="px-4 py-2 bg-white text-gray-600 border border-gray-200 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-50 transition">Развитие</button>
            <button class="px-4 py-2 bg-white text-gray-600 border border-gray-200 rounded-full text-sm font-medium whitespace-nowrap hover:bg-gray-50 transition">Психология</button>
        </div>
    </div>

    @if($courses->isEmpty())
        <div class="bg-white rounded-3xl p-12 text-center border-2 border-dashed border-gray-200">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-4xl text-gray-300">📚</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Курсы скоро появятся</h3>
            <p class="text-gray-500 max-w-sm mx-auto">Мы работаем над созданием лучших обучающих программ для вас и ваших детей.</p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @foreach($courses as $course)
                <div class="group bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col h-full transform hover:-translate-y-1">
                    <!-- Image Container -->
                    <div class="relative aspect-[16/9] overflow-hidden bg-gray-100">
                        @if($course->cover_image)
                            <img src="{{ Storage::url($course->cover_image) }}" alt="{{ $course->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="w-full h-full gradient-bg opacity-20 group-hover:opacity-30 transition-opacity flex items-center justify-center">
                                <span class="text-6xl">📚</span>
                            </div>
                        @endif
                        
                        @if($course->isFree())
                            <div class="absolute top-4 right-4 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
                                Бесплатно
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="p-6 flex flex-col flex-1">
                        <div class="flex items-center space-x-2 mb-3">
                            <span class="bg-purple-100 text-brand text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Курс</span>
                            <span class="text-gray-400 text-xs">•</span>
                            <span class="text-gray-500 text-xs">{{ $course->modules_count }} модулей</span>
                        </div>
                        
                        <h3 class="font-bold text-xl text-gray-900 mb-2 group-hover:text-brand transition-colors">{{ $course->title }}</h3>
                        
                        @if($course->description)
                            <p class="text-gray-600 text-sm mb-6 line-clamp-3 leading-relaxed">{{ Str::limit($course->description, 120) }}</p>
                        @endif
                        
                        <div class="mt-auto pt-6 flex items-center justify-between border-t border-gray-50">
                            <div>
                                <p class="text-[10px] text-gray-400 uppercase tracking-widest font-bold mb-0.5">Стоимость</p>
                                <p class="text-xl font-bold text-gray-900">{{ $course->formatted_price }}</p>
                            </div>
                            
                            <a href="{{ route('catalog.show', $course) }}" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-gray-50 text-gray-400 group-hover:bg-brand group-hover:text-white transition-all duration-300 shadow-sm">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
