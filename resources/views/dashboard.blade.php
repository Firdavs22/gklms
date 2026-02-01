@extends('layouts.app')

@section('title', 'Мои курсы')

@section('content')
{{-- Welcome Section with Brand Gradient --}}
<div class="relative overflow-hidden rounded-3xl bg-brand p-10 mb-8 text-white shadow-lg overflow-hidden">
    <div class="relative z-10">
        <h1 class="text-3xl font-extrabold mb-2 tracking-tight">Привет, {{ $user->name }}! 👋</h1>
        <p class="text-white/80 text-lg">Рады видеть вас. Время открыть новые горизонты!</p>
    </div>
    {{-- Brand decorative elements --}}
    <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/4"></div>
    <div class="absolute bottom-0 left-1/2 w-32 h-32 bg-white/10 rounded-full translate-y-1/2"></div>
    <div class="absolute inset-0 gradient-bg opacity-40"></div>
</div>

{{-- Quick Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm transition-transform hover:-translate-y-1">
        <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-brand/10 flex items-center justify-center mr-4">
                <svg class="w-7 h-7 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] uppercase font-extrabold tracking-widest text-gray-400 mb-0.5">Курсов</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $enrollments->count() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm transition-transform hover:-translate-y-1">
        <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-green-50 flex items-center justify-center mr-4">
                <svg class="w-7 h-7 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] uppercase font-extrabold tracking-widest text-gray-400 mb-0.5">Пройдено</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $completedLessons ?? 0 }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 border border-gray-100 shadow-sm transition-transform hover:-translate-y-1">
        <div class="flex items-center">
            <div class="w-14 h-14 rounded-2xl bg-orange-50 flex items-center justify-center mr-4">
                <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
            </div>
            <div>
                <p class="text-[10px] uppercase font-extrabold tracking-widest text-gray-400 mb-0.5">Всего уроков</p>
                <p class="text-2xl font-black text-gray-900 leading-none">{{ $totalLessons ?? 0 }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Section Title --}}
<div class="flex items-center justify-between mb-8">
    <h2 class="text-2xl font-black text-gray-900 tracking-tight">Мои программы</h2>
</div>

@if($enrollments->isEmpty())
    {{-- Empty State --}}
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 p-16 text-center">
        <div class="w-24 h-24 bg-brand/10 rounded-full flex items-center justify-center mx-auto mb-6">
            <span class="text-5xl">📚</span>
        </div>
        <h3 class="text-xl font-extrabold text-gray-900 mb-3">У вас пока нет активных курсов</h3>
        <p class="text-gray-500 mb-10 max-w-md mx-auto leading-relaxed">
            Выберите подходящую программу в нашем каталоге и начните путь к спокойному родительству уже сегодня.
        </p>
        <a 
            href="{{ route('catalog.index') }}" 
            class="inline-flex items-center px-10 py-5 bg-brand text-white font-extrabold rounded-2xl hover:opacity-90 transition-all duration-300 shadow-xl shadow-brand/20 active:scale-95"
        >
            Перейти в каталог
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path>
            </svg>
        </a>
    </div>
@else
    {{-- Courses Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @foreach($enrollments as $enrollment)
        @php
            $course = $enrollment->course;
            // Calculate progress for this course
            $totalInCourse = 0;
            $completedInCourse = 0;
            foreach ($course->publishedModules as $module) {
                foreach ($module->publishedLessons as $lesson) {
                    $totalInCourse++;
                    if ($user->lessonProgress()->where('lesson_id', $lesson->id)->where('is_completed', true)->exists()) {
                        $completedInCourse++;
                    }
                }
            }
            $progress = $totalInCourse > 0 ? round(($completedInCourse / $totalInCourse) * 100) : 0;
        @endphp
        <a 
            href="{{ route('courses.show', $course) }}"
            class="group bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 flex flex-col h-full transform hover:-translate-y-1"
        >
            {{-- Course Cover --}}
            <div class="relative aspect-[16/9] bg-gray-100 overflow-hidden">
                @if($course->cover_image)
                    <img 
                        src="{{ Storage::url($course->cover_image) }}" 
                        alt="{{ $course->title }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                    >
                @else
                    <div class="w-full h-full gradient-bg opacity-20 flex items-center justify-center group-hover:opacity-30 transition-opacity">
                        <span class="text-6xl group-hover:scale-110 transition-transform duration-500">🤱</span>
                    </div>
                @endif
                
                <div class="absolute top-4 right-4 animate-pulse">
                    <span class="bg-white/90 backdrop-blur-sm text-brand text-[8px] font-black px-2 py-1 rounded-full uppercase tracking-tighter">Активен</span>
                </div>
            </div>
            
            {{-- Course Info --}}
            <div class="p-6 flex flex-col flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="bg-gray-100 text-gray-500 text-[9px] font-black px-2 py-0.5 rounded uppercase tracking-wider">Программа</span>
                </div>

                <h3 class="font-extrabold text-xl text-gray-900 mb-4 group-hover:text-brand transition-colors leading-tight">
                    {{ $course->title }}
                </h3>
                
                {{-- Progress Bar --}}
                <div class="mt-auto pt-4">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-gray-400">Прогресс</span>
                        <span class="text-sm font-black text-brand">{{ $progress }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2 shadow-inner">
                        <div 
                            class="bg-brand h-2 rounded-full transition-all duration-1000 shadow-sm"
                            style="width: {{ $progress }}%"
                        ></div>
                    </div>
                    
                    <div class="mt-5 flex items-center justify-between">
                        <span class="text-[11px] font-bold text-gray-400 flex items-center">
                             {{ $completedInCourse }}/{{ $totalInCourse }} уроков
                        </span>
                        
                        <div class="w-10 h-10 rounded-2xl bg-gray-50 text-gray-400 group-hover:bg-brand group-hover:text-white transition-all duration-300 flex items-center justify-center transform group-hover:rotate-12">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($progress == 100)
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7-7 7"></path>
                                @endif
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
@endif
@endsection
