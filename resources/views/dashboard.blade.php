@extends('layouts.app')

@section('title', 'Мои курсы')

@section('content')
{{-- Welcome Section with Gradient --}}
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-purple-600 via-violet-600 to-pink-600 p-8 mb-8 text-white">
    <div class="relative z-10">
        <h1 class="text-3xl font-bold mb-2">Привет, {{ $user->name }}! 👋</h1>
        <p class="text-purple-100 text-lg">Добро пожаловать в твой личный кабинет</p>
    </div>
    {{-- Decorative circles --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4"></div>
    <div class="absolute bottom-0 left-1/2 w-32 h-32 bg-white/10 rounded-full translate-y-1/2"></div>
</div>

{{-- Quick Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $enrollments->count() }}</p>
                <p class="text-gray-500 text-sm">Курсов</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $completedLessons ?? 0 }}</p>
                <p class="text-gray-500 text-sm">Уроков пройдено</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl p-6 border border-gray-100 shadow-sm">
        <div class="flex items-center">
            <div class="w-12 h-12 rounded-xl bg-pink-100 flex items-center justify-center mr-4">
                <svg class="w-6 h-6 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-gray-900">{{ $totalLessons ?? 0 }}</p>
                <p class="text-gray-500 text-sm">Уроков всего</p>
            </div>
        </div>
    </div>
</div>

{{-- Section Title --}}
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-bold text-gray-900">Мои курсы</h2>
</div>

@if($enrollments->isEmpty())
    {{-- Empty State --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
        <div class="w-24 h-24 bg-gradient-to-r from-purple-100 to-pink-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-12 h-12 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 mb-3">У вас пока нет курсов</h3>
        <p class="text-gray-500 mb-8 max-w-md mx-auto">
            После приобретения курса на нашем сайте, он автоматически появится здесь
        </p>
        <a 
            href="https://globokids.ru" 
            target="_blank"
            class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-xl hover:from-purple-700 hover:to-pink-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
        >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            Выбрать курс
        </a>
    </div>
@else
    {{-- Courses Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($enrollments as $enrollment)
        @php
            $course = $enrollment->course;
            $totalLessonsInCourse = $course->publishedLessons()->count();
            $completedInCourse = $user->lessonProgress()
                ->whereHas('lesson', function($q) use ($course) {
                    $q->whereHas('modules', function($q2) use ($course) {
                        $q2->where('course_id', $course->id);
                    });
                })
                ->where('is_completed', true)
                ->count();
            $progress = $totalLessonsInCourse > 0 ? round(($completedInCourse / $totalLessonsInCourse) * 100) : 0;
        @endphp
        <a 
            href="{{ route('courses.show', $course) }}"
            class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden group hover:shadow-lg transition-all duration-300 hover:-translate-y-1 block"
        >
            {{-- Course Cover --}}
            @if($course->cover_image)
            <div class="aspect-video bg-gray-100 overflow-hidden">
                <img 
                    src="{{ Storage::url($course->cover_image) }}" 
                    alt="{{ $course->title }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                >
            </div>
            @else
            <div class="aspect-video bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center">
                <span class="text-6xl">📚</span>
            </div>
            @endif
            
            {{-- Course Info --}}
            <div class="p-5">
                <h3 class="font-bold text-lg text-gray-900 mb-2 group-hover:text-purple-600 transition-colors">
                    {{ $course->title }}
                </h3>
                
                @if($course->description)
                <p class="text-gray-500 text-sm line-clamp-2 mb-4">
                    {{ Str::limit($course->description, 80) }}
                </p>
                @endif
                
                {{-- Progress Bar --}}
                <div class="mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm text-gray-500">Прогресс</span>
                        <span class="text-sm font-medium text-purple-600">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div 
                            class="bg-gradient-to-r from-purple-600 to-pink-600 h-2 rounded-full transition-all duration-500"
                            style="width: {{ $progress }}%"
                        ></div>
                    </div>
                </div>
                
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        {{ $completedInCourse }}/{{ $totalLessonsInCourse }} уроков
                    </span>
                    <span class="inline-flex items-center text-purple-600 text-sm font-semibold group-hover:translate-x-1 transition-transform">
                        @if($progress == 0)
                            Начать
                        @elseif($progress == 100)
                            Пройден ✓
                        @else
                            Продолжить
                        @endif
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
