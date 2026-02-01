@extends('layouts.app')

@section('title', $lesson->title . ' - GloboKids')

@section('content')
<div class="min-h-[calc(100vh-64px)] bg-gray-50 flex flex-col lg:flex-row">
    <!-- Sidebar - Course Navigation -->
    <aside class="lg:w-80 bg-white border-r border-gray-100 lg:h-[calc(100vh-64px)] lg:sticky lg:top-16 overflow-y-auto z-10"
           x-data="{ activeModule: {{ $module->id }} }">
        <div class="p-4 border-b border-gray-50">
            <a href="{{ route('courses.show', $course) }}" class="flex items-center text-brand hover:opacity-80 transition font-bold text-xs uppercase tracking-wider">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                К программе курса
            </a>
        </div>
        
        <!-- Modules list Accordion -->
        <nav class="p-3 space-y-1">
            @foreach($allModules as $m)
            <div class="rounded-xl overflow-hidden" x-data="{ isOpen: activeModule === {{ $m->id }} }">
                <button @click="isOpen = !isOpen" 
                        class="w-full flex items-center justify-between p-3 text-left hover:bg-gray-50 transition rounded-xl"
                        :class="isOpen ? 'bg-gray-50/50' : ''">
                    <div class="flex items-center flex-1 min-w-0">
                        <div class="w-1 h-5 rounded-full mr-3 shrink-0" :class="activeModule === {{ $m->id }} ? 'bg-brand' : 'bg-gray-200'"></div>
                        <span class="text-[11px] font-bold text-gray-900 uppercase tracking-tight truncate">
                            {{ $m->title }}
                        </span>
                    </div>
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-300 shrink-0 ml-2" 
                         :class="isOpen ? 'rotate-180 text-brand' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul class="pb-2 space-y-0.5" x-show="isOpen" x-collapse x-cloak>
                    @foreach($m->publishedLessons as $l)
                    @php
                        $isActive = $l->id === $lesson->id;
                        $isComp = $l->progress->first()?->is_completed ?? false;
                    @endphp
                    <li>
                        <a href="{{ route('lessons.show', [$course, $l]) }}" 
                           class="flex items-center mx-2 px-4 py-2 rounded-xl transition text-[13px] relative group
                               {{ $isActive ? 'bg-brand/5 text-brand font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            
                            <span class="w-5 h-5 rounded-lg flex items-center justify-center mr-3 flex-shrink-0 transition-colors
                                {{ $isComp ? 'bg-green-100 text-green-600' : ($isActive ? 'bg-brand text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200') }}">
                                @if($isComp)
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                @else
                                <span class="text-[10px]">{{ $loop->iteration }}</span>
                                @endif
                            </span>
                            <span class="truncate">{{ $l->title }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endforeach
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow bg-white min-w-0">
        <div class="max-w-4xl mx-auto px-6 py-10 md:py-12">
            <!-- Lesson Header -->
            <div class="mb-8">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">{{ $module->title }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 tracking-tight leading-tight">{{ $lesson->title }}</h1>
            </div>

            <!-- Video Player -->
            @if($lesson->hasVideo())
            <div class="aspect-video bg-black rounded-3xl overflow-hidden mb-10 shadow-2xl shadow-gray-200 ring-1 ring-black/5">
                <iframe 
                    src="{{ $lesson->embed_video_url }}"
                    class="w-full h-full"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                ></iframe>
            </div>
            @endif

            <!-- Lesson Content -->
            @if($lesson->content)
            <div class="prose prose-gray prose-lg max-w-none mb-12 bg-gray-50/50 p-8 md:p-10 rounded-3xl border border-gray-100 font-medium leading-relaxed text-gray-700">
                {!! $lesson->content !!}
            </div>
            @endif

            <!-- Assignment Section -->
            @if($assignment)
            <div class="bg-white rounded-3xl border border-gray-100 overflow-hidden mb-12 shadow-sm">
                @if($userSubmission)
                    <!-- COMPLETED STATE -->
                    <div class="p-10 text-center bg-gray-50/30">
                        <div class="inline-flex items-center px-4 py-1.5 bg-green-100 text-green-700 rounded-full text-[11px] font-bold uppercase tracking-widest mb-3">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Задание пройдено
                        </div>
                        @if($assignment->type === 'quiz' && $userSubmission->score !== null)
                            <p class="text-sm font-bold text-gray-500 mt-2">Ваш результат: <span class="text-green-600">{{ $userSubmission->score }} / {{ $userSubmission->max_score }}</span></p>
                        @else
                            <p class="text-sm font-medium text-gray-400 mt-2">Ваш ответ успешно сохранен в системе.</p>
                        @endif
                    </div>
                @else
                    <!-- FORM STATE -->
                    <div class="p-6 bg-gray-50 border-b border-gray-100 flex items-center justify-between">
                        <h2 class="text-base font-bold text-gray-900 flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-brand/10 text-brand flex items-center justify-center mr-4">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            {{ $assignment->title ?: $assignment->type_label }}
                        </h2>
                        @if($assignment->is_required)
                            <span class="text-[10px] font-bold bg-orange-100 text-orange-600 px-3 py-1 rounded-full uppercase tracking-wider">Обязательно</span>
                        @endif
                    </div>
                    
                    <div class="p-8 md:p-10">
                        @if($assignment->description)
                            <p class="text-gray-500 mb-8 text-base leading-relaxed">{{ $assignment->description }}</p>
                        @endif

                        <form id="assignment-form" class="space-y-8">
                            @foreach($assignment->questions as $qi => $question)
                            <div class="group">
                                <h3 class="font-bold text-gray-900 mb-5 text-base flex items-start">
                                    <span class="text-brand mr-3 shrink-0">{{ $qi + 1 }}.</span>
                                    <span>{{ $question->text }}</span>
                                </h3>
                                
                                <div class="space-y-3">
                                    @if($question->type === 'single')
                                        @foreach($question->answers as $answer)
                                        <label class="flex items-center p-4 border border-gray-100 rounded-2xl hover:border-brand/30 hover:bg-brand/5 cursor-pointer transition-all">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}" class="w-5 h-5 text-brand focus:ring-brand border-gray-300">
                                            <span class="ml-4 text-sm font-medium text-gray-700 transition-colors">{{ $answer->text }}</span>
                                        </label>
                                        @endforeach
                                    @elseif($question->type === 'multiple')
                                        @foreach($question->answers as $answer)
                                        <label class="flex items-center p-4 border border-gray-100 rounded-2xl hover:border-brand/30 hover:bg-brand/5 cursor-pointer transition-all">
                                            <input type="checkbox" name="question_{{ $question->id }}[]" value="{{ $answer->id }}" class="w-5 h-5 rounded text-brand focus:ring-brand border-gray-300">
                                            <span class="ml-4 text-sm font-medium text-gray-700 transition-colors">{{ $answer->text }}</span>
                                        </label>
                                        @endforeach
                                    @else
                                        <textarea 
                                            name="question_{{ $question->id }}"
                                            rows="4"
                                            class="w-full border border-gray-200 rounded-2xl p-5 focus:ring-brand focus:border-brand bg-gray-50/30 transition-all text-sm font-medium placeholder-gray-400"
                                            placeholder="Введите ваш ответ..."
                                            required
                                        ></textarea>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            
                            <div class="pt-6">
                                <button type="submit" class="w-full bg-brand text-white font-bold py-4 px-8 rounded-2xl hover:opacity-90 transform active:scale-[0.99] transition-all shadow-xl shadow-brand/20 text-base">
                                    Отправить задание
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
            @endif

            <!-- MANUAL COMPLETION BUTTON (If no assignment or as a quick action) -->
            @if(!$assignment && !($progress->is_completed ?? false))
            <div class="mb-12">
                <button id="mark-complete-btn" 
                        class="w-full flex items-center justify-center space-x-3 bg-brand/10 text-brand border-2 border-dashed border-brand/30 rounded-3xl py-8 px-6 hover:bg-brand/20 transition-all group">
                    <div class="w-12 h-12 rounded-2xl bg-brand text-white flex items-center justify-center shadow-lg shadow-brand/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-lg leading-none mb-1">Я изучил этот урок</p>
                        <p class="text-sm font-medium opacity-70">Отметить как пройденный</p>
                    </div>
                </button>
            </div>
            @elseif(!$assignment && ($progress->is_completed ?? false))
            <div class="mb-12 bg-green-50 border border-green-100 rounded-3xl p-8 flex items-center justify-center space-x-4">
                <div class="w-10 h-10 rounded-full bg-green-500 text-white flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <p class="font-bold text-green-800 text-lg">Вы успешно изучили этот урок!</p>
            </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 border-t border-gray-100 pt-10 mb-10">
                @if($previousLesson)
                <a href="{{ route('lessons.show', [$course, $previousLesson]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-white border border-gray-100 text-gray-600 font-bold rounded-2xl hover:bg-gray-50 transition-all text-sm shadow-sm">
                    <svg class="w-4 h-4 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Предыдущий урок
                </a>
                @endif

                @if($nextLesson)
                <a href="{{ route('lessons.show', [$course, $nextLesson]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-brand text-white font-bold rounded-2xl hover:opacity-90 transition-all shadow-xl shadow-brand/20 text-sm">
                    Следующий урок
                    <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @else
                <a href="{{ route('courses.show', $course) }}" 
                   class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-green-500 text-white font-bold rounded-2xl hover:bg-green-600 transition-all shadow-xl shadow-green-500/20 text-sm">
                    Завершить курс
                    <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </a>
                @endif
            </div>
        </div>
    </main>
</div>

@push('scripts')
<script>
document.getElementById('assignment-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<div class="flex items-center justify-center"><svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Отправляем...</div>';
    btn.disabled = true;
    
    const formData = new FormData(this);
    const answers = {};
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('question_')) {
            const qId = key.replace('question_', '').replace('[]', '');
            if (key.endsWith('[]')) {
                if (!answers[qId]) answers[qId] = [];
                answers[qId].push(value);
            } else {
                answers[qId] = value;
            }
        }
    }
    
    fetch('{{ route("assignments.submit", [$course, $lesson]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ answers: answers })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: data.message, type: 'success' } 
            }));
            setTimeout(() => location.reload(), 1500);
        } else {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: data.error || 'Ошибка при отправке', type: 'error' } 
            }));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.dispatchEvent(new CustomEvent('notify', { 
            detail: { message: 'Ошибка соединения', type: 'error' } 
        }));
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});

// MANUAL COMPLETION
document.getElementById('mark-complete-btn')?.addEventListener('click', function() {
    const btn = this;
    const originalContent = btn.innerHTML;
    btn.disabled = true;
    btn.classList.add('opacity-50');

    fetch('{{ route("lessons.complete", [$course, $lesson]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.dispatchEvent(new CustomEvent('notify', { 
                detail: { message: 'Урок пройден!', type: 'success' } 
            }));
            setTimeout(() => location.reload(), 1000);
        } else {
            btn.disabled = false;
            btn.classList.remove('opacity-50');
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.classList.remove('opacity-50');
    });
});

// VIDEO POSITION TRACKING
let videoStarted = false;
let lastSavedPosition = 0;

window.addEventListener('message', function(event) {
    // Basic support for YouTube and other players that post messages
    // This is a placeholder for more advanced tracking
    if (!videoStarted) {
        videoStarted = true;
        console.log('Video interaction detected');
    }
});

// Periodically check if we need to mark progress (fallback or simple timer)
@if($lesson->hasVideo() && !($progress->is_completed ?? false))
    // If it's a long lesson without assignment, we could auto-complete after some time
    // For now, let's just use manual completion or assignment success
@endif
</script>
@endpush

<style>
    .prose-gray {
        --tw-prose-links: var(--color-primary);
        --tw-prose-bullets: #d1d5db;
        --tw-prose-counters: #9ca3af;
    }
    [x-cloak] { display: none !important; }
</style>
@endsection
