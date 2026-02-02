@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Breadcrumb -->
    <nav class="mb-8">
        <a href="{{ route('catalog.index') }}" class="inline-flex items-center text-sm font-medium text-brand hover:opacity-80 transition">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Назад к каталогу
        </a>
    </nav>

    <div class="lg:flex lg:space-x-12">
        <!-- Main Content -->
        <div class="lg:w-2/3">
            <!-- Course Header -->
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                <div class="relative aspect-video bg-gray-100">
                    @if($course->cover_image)
                        <img src="{{ Storage::url($course->cover_image) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full gradient-bg opacity-20 flex items-center justify-center">
                            <span class="text-9xl">🤱</span>
                        </div>
                    @endif
                </div>
                
                <div class="p-8 md:p-10">
                    <div class="flex items-center space-x-3 mb-4">
                        <span class="bg-purple-100 text-brand text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Основная программа</span>
                        <span class="text-gray-400 text-xs">•</span>
                        <span class="text-gray-500 text-xs">{{ $totalLessons }} видео-уроков</span>
                    </div>
                    
                    <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6 leading-tight">{{ $course->title }}</h1>
                    
                    @if($course->description)
                        <div class="prose prose-purple max-w-none text-gray-600 leading-relaxed">
                            {!! nl2br(e($course->description)) !!}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Course Program -->
            <div class="space-y-6 mb-12">
                <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <span class="w-8 h-8 rounded-full bg-brand/10 text-brand flex items-center justify-center text-sm mr-3">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </span>
                    Программа курса
                </h3>

                @foreach($modules as $module)
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" 
                         x-data="{ isOpen: {{ $loop->first ? 'true' : 'false' }} }"
                         :class="isOpen ? 'ring-1 ring-brand/10 shadow-md' : ''">
                        
                        <button @click="isOpen = !isOpen" class="w-full p-5 border-b border-gray-50 flex justify-between items-center bg-gray-50/50 hover:bg-gray-50 transition-colors text-left focus:outline-none">
                            <div class="flex items-center">
                                <span class="flex-shrink-0 w-8 h-8 rounded-full border border-gray-200 text-gray-400 flex items-center justify-center font-bold text-xs mr-4 transition-colors"
                                      :class="isOpen ? 'bg-brand text-white border-brand' : ''">
                                    {{ $loop->iteration }}
                                </span>
                                <div>
                                    <h4 class="font-bold text-gray-900">{{ $module->title }}</h4>
                                    @if($module->description)
                                        <p class="text-xs text-gray-500 mt-0.5">{{ Str::limit($module->description, 60) }}</p>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-center">
                                <span class="hidden sm:inline-block bg-white border border-gray-100 px-3 py-1 rounded-full text-xs text-gray-500 font-medium mr-4">
                                    {{ $module->publishedLessons->count() }} уроков
                                </span>
                                <svg class="w-5 h-5 text-gray-400 transition-transform duration-300"
                                     :class="isOpen ? 'rotate-180 text-brand' : ''"
                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </button>

                        <div x-show="isOpen" x-collapse x-cloak>
                            <div class="divide-y divide-gray-50">
                                @foreach($module->publishedLessons as $lesson)
                                    <div class="p-5 flex items-center justify-between group hover:bg-gray-50/80 transition-colors">
                                        <div class="flex items-center space-x-4">
                                            <div class="w-10 h-10 rounded-xl bg-gray-50 text-gray-400 flex items-center justify-center group-hover:bg-white group-hover:text-brand transition-all">
                                                @if($lesson->video_path)
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                    </svg>
                                                @else
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                    </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $lesson->title }}</p>
                                            </div>
                                        </div>
                                        @if($lesson->is_published)
                                            <div class="text-brand opacity-0 group-hover:opacity-100 transition-opacity">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Sidebar / Purchase Card -->
        <div class="lg:w-1/3 mt-8 lg:mt-0">
            <div class="sticky top-24 bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden p-8">
                <div class="mb-8">
                    <p class="text-[10px] text-gray-400 font-bold uppercase tracking-[0.2em] mb-2">Стоимость обучения</p>
                    <div class="flex items-baseline space-x-2">
                        <span class="text-4xl font-bold text-gray-900">{{ $course->formatted_price }}</span>
                        @if(!$course->isFree())
                            <span class="text-gray-400 text-sm line-through">5 900 ₽</span>
                        @endif
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center space-x-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Доступ навсегда</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-gray-600">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        <span>Сертификат об окончании</span>
                    </div>
                    <div class="flex items-center space-x-3 text-sm text-gray-600 py-2">
                        <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span>Проверка домашних заданий</span>
                    </div>
                </div>

                <div class="mt-8">
                    @if($course->isFree())
                        @auth
                            <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full bg-brand text-white font-bold py-4 px-6 rounded-2xl hover:opacity-95 transition shadow-lg shadow-brand/20">
                                    Записаться бесплатно
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}?redirect={{ url()->current() }}" class="block w-full text-center bg-brand text-white font-bold py-4 px-6 rounded-2xl hover:opacity-95 transition shadow-lg shadow-brand/20">
                                Войти и записаться
                            </a>
                        @endauth
                    @else
                        <a 
                            href="https://globokids.ru" 
                            target="_blank"
                            class="block w-full text-center bg-gray-900 text-white font-bold py-4 px-6 rounded-2xl hover:opacity-90 transition shadow-lg"
                        >
                            Купить курс
                        </a>
                        <p class="text-center text-gray-400 text-[10px] mt-4 flex items-center justify-center">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                            </svg>
                            Безопасная оплата через Tinkoff
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
