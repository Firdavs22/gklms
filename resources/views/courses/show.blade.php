@extends('layouts.app')

@section('title', $course->title . ' - GloboKids')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <a href="{{ route('dashboard') }}" class="text-purple-600 hover:text-purple-800">← Мои курсы</a>
    </nav>

    <!-- Course Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="md:flex">
            @if($course->cover_image)
            <div class="md:w-1/3">
                <div class="aspect-video md:aspect-square h-full bg-gray-100">
                    <img src="{{ Storage::url($course->cover_image) }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                </div>
            </div>
            @endif
            
            <div class="p-6 md:p-8 flex-grow">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">{{ $course->title }}</h1>
                
                @if($course->description)
                <p class="text-gray-600 mb-6">{{ $course->description }}</p>
                @endif

                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="flex justify-between text-sm text-gray-600 mb-2">
                        <span>Прогресс курса</span>
                        <span>{{ $completedLessons }} / {{ $totalLessons }} уроков</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3">
                        <div class="gradient-bg rounded-full h-3 transition-all duration-500" style="width: {{ $progressPercent }}%"></div>
                    </div>
                </div>
                
                @if($progressPercent === 100)
                <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                    🎉 Курс завершён!
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modules and Lessons -->
    <div class="space-y-6">
        @foreach($modules as $moduleIndex => $module)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 bg-gray-50 border-b border-gray-100">
                <div class="flex items-center">
                    <span class="flex-shrink-0 w-10 h-10 gradient-bg text-white rounded-full flex items-center justify-center font-bold mr-4">
                        {{ $moduleIndex + 1 }}
                    </span>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900">{{ $module->title }}</h2>
                        @if($module->description)
                        <p class="text-gray-500 text-sm">{{ $module->description }}</p>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="divide-y divide-gray-100">
                @foreach($module->publishedLessons as $lessonIndex => $lesson)
                @php
                    $isCompleted = $lesson->progress->first()?->is_completed ?? false;
                @endphp
                <a href="{{ route('lessons.show', [$course, $lesson]) }}" 
                   class="flex items-center p-4 hover:bg-gray-50 transition group">
                    <span class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center mr-4
                        {{ $isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-500' }}">
                        @if($isCompleted)
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        @else
                        {{ $lessonIndex + 1 }}
                        @endif
                    </span>
                    
                    <div class="flex-grow">
                        <span class="text-gray-900 font-medium group-hover:text-purple-600 transition">
                            {{ $lesson->title }}
                        </span>
                        <div class="flex items-center text-sm text-gray-500 mt-1">
                            @if($lesson->hasVideo())
                            <span class="flex items-center mr-3">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Видео
                            </span>
                            @endif
                            @if($lesson->hasAssignment())
                            <span class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                Задание
                            </span>
                            @endif
                        </div>
                    </div>
                    
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
</style>
@endsection
