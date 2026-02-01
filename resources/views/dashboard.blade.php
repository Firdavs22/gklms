@extends('layouts.app')

@section('title', 'Мои курсы')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Привет, {{ $user->name }}! 👋</h1>
    <p class="text-gray-600 mt-1">Здесь твои курсы для обучения</p>
</div>

@if($enrollments->isEmpty())
    <!-- Empty State -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Пока нет курсов</h3>
        <p class="text-gray-600 mb-6">
            Приобретите курс на нашем сайте и он появится здесь
        </p>
        <a 
            href="https://globokids.ru" 
            target="_blank"
            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-lg hover:from-purple-700 hover:to-pink-700 transition shadow-lg"
        >
            Перейти в каталог курсов
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
            </svg>
        </a>
    </div>
@else
    <!-- Courses Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($enrollments as $enrollment)
        <a 
            href="{{ route('courses.show', $enrollment->course) }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden card-hover block"
        >
            <!-- Course Cover -->
            @if($enrollment->course->cover_image)
            <div class="aspect-video bg-gray-100">
                <img 
                    src="{{ Storage::url($enrollment->course->cover_image) }}" 
                    alt="{{ $enrollment->course->title }}"
                    class="w-full h-full object-cover"
                >
            </div>
            @else
            <div class="aspect-video gradient-bg flex items-center justify-center">
                <span class="text-5xl">📚</span>
            </div>
            @endif
            
            <!-- Course Info -->
            <div class="p-5">
                <h3 class="font-semibold text-lg text-gray-900 mb-2">
                    {{ $enrollment->course->title }}
                </h3>
                
                @if($enrollment->course->description)
                <p class="text-gray-600 text-sm line-clamp-2 mb-4">
                    {{ Str::limit($enrollment->course->description, 100) }}
                </p>
                @endif
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">
                        {{ $enrollment->course->publishedLessons()->count() }} уроков
                    </span>
                    <span class="inline-flex items-center text-purple-600 text-sm font-medium">
                        Начать
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
@endif
@endsection
