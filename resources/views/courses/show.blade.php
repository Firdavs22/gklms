@extends('layouts.app')

@section('title', $course->title . ' - GloboKids')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ openModule: null }">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-brand hover:opacity-80 transition flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            ← Мои курсы
        </a>
    </nav>

    <!-- Course Header -->
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="md:flex">
            @if($course->cover_image)
            <div class="md:w-1/3">
                <div class="aspect-video md:aspect-square h-full bg-gray-100">
                    <img src="{{ Storage::url($course->cover_image) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                </div>
            </div>
            @else
            <div class="md:w-1/3">
                <div class="aspect-video md:aspect-square h-full gradient-bg opacity-20 flex items-center justify-center">
                    <span class="text-7xl">🤱</span>
                </div>
            </div>
            @endif
            
            <div class="p-6 md:p-10 flex-grow">
                <div class="flex items-center space-x-2 mb-4">
                    <span class="bg-purple-100 text-brand text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">Мое обучение</span>
                </div>
                
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-6 leading-tight">{{ $course->title }}</h1>
                
                <!-- Progress Bar -->
                <div class="mb-6 bg-gray-50 p-6 rounded-2xl border border-gray-100">
                    <div class="flex justify-between text-sm font-bold text-gray-500 mb-3 uppercase tracking-wider">
                        <span>Прогресс курса</span>
                        <span class="text-brand">{{ $completedLessons }} / {{ $totalLessons }} уроков</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="bg-brand rounded-full h-3 transition-all duration-700 shadow-sm" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
                
                @if($progressPercent === 100)
                <div class="inline-flex items-center px-6 py-2 bg-green-100 text-green-700 rounded-2xl text-sm font-bold shadow-sm">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Курс завершён!
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modules and Lessons Accordion -->
    <div class="space-y-4">
        <div class="flex items-center justify-between mb-2">
            <h2 class="text-xl font-bold text-gray-900">Программа обучения</h2>
            <p class="text-sm text-gray-500">{{ count($modules) }} модулей</p>
        </div>

        @foreach($modules as $moduleIndex => $module)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md" 
             x-data="{ isOpen: false }" 
             :class="isOpen ? 'border-brand/30 shadow-md ring-1 ring-brand/5' : ''">
            
            <!-- Module Header Toggle -->
            <button class="w-full p-5 flex items-center justify-between text-left focus:outline-none" 
                    @click="isOpen = !isOpen">
                <div class="flex items-center">
                    <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-gray-50 text-gray-400 group-hover:bg-brand/10 group-hover:text-brand flex items-center justify-center font-bold mr-4 transition-colors"
                          :class="isOpen ? 'bg-brand/10 text-brand' : ''">
                        {{ $moduleIndex + 1 }}
                    </span>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900 tracking-tight">{{ $module->title }}</h2>
                        <div class="flex items-center text-xs text-gray-400 mt-1">
                            <span>{{ $module->publishedLessons->count() }} уроков</span>
                        </div>
                    </div>
                </div>
                
                <div class="w-8 h-8 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 transition-transform duration-300"
                     :class="isOpen ? 'rotate-180 bg-brand/10 text-brand' : ''">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
            </button>
            
            <!-- Module Description (if any) and Lessons list -->
            <div x-show="isOpen" 
                 x-collapse
                 x-cloak
                 class="border-t border-gray-50">
                
                @if($module->description)
                <div class="px-6 py-4 bg-gray-50/30">
                    <p class="text-gray-600 text-sm leading-relaxed">{{ $module->description }}</p>
                </div>
                @endif

                <div class="divide-y divide-gray-50">
                    @foreach($module->publishedLessons as $lessonIndex => $lesson)
                    @php
                        $isCompleted = $lesson->progress->first()?->is_completed ?? false;
                    @endphp
                    <a href="{{ route('lessons.show', [$course, $lesson]) }}" 
                       class="flex items-center p-5 hover:bg-gray-50 transition group">
                        <div class="flex-shrink-0 w-10 h-10 rounded-xl flex items-center justify-center mr-4 transition-all
                            {{ $isCompleted ? 'bg-green-100 text-green-600 shadow-sm' : 'bg-gray-50 text-gray-400 group-hover:bg-white group-hover:text-brand' }}">
                            @if($isCompleted)
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            @else
                                @if($lesson->hasVideo())
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                @endif
                            @endif
                        </div>
                        
                        <div class="flex-grow">
                            <span class="text-gray-900 font-bold group-hover:text-brand transition-colors">
                                {{ $lesson->title }}
                            </span>
                            <div class="flex items-center text-xs text-gray-400 mt-1">
                                @if($lesson->hasVideo())
                                <span class="flex items-center mr-3 bg-purple-50 text-brand px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Видео</span>
                                @endif
                                @if($lesson->hasAssignment())
                                <span class="flex items-center bg-orange-50 text-orange-600 px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider">Задание</span>
                                @endif
                            </div>
                        </div>
                        
                        <div class="text-gray-300 group-hover:text-brand group-hover:translate-x-1 transition-all">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<script src="https://unpkg.com/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
@endsection
