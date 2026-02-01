@extends('layouts.app')

@section('title', $lesson->title . ' - GloboKids')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col lg:flex-row">
    <!-- Sidebar - Course Navigation -->
    <aside class="lg:w-72 bg-white border-r border-gray-100 lg:h-[calc(100vh-64px)] lg:sticky lg:top-16 overflow-y-auto z-10"
           x-data="{ activeModule: {{ $module->id }} }">
        <div class="p-3 border-b border-gray-100">
            <a href="{{ route('courses.show', $course) }}" class="flex items-center text-brand hover:opacity-80 transition font-extrabold text-[11px] uppercase tracking-wider">
                <svg class="w-3 h-3 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                К программе курса
            </a>
        </div>
        
        <!-- Modules list Accordion -->
        <nav class="p-2 space-y-0.5">
            @foreach($allModules as $m)
            <div class="rounded-xl overflow-hidden" x-data="{ isOpen: activeModule === {{ $m->id }} }">
                <button @click="isOpen = !isOpen" 
                        class="w-full flex items-center justify-between p-2.5 text-left hover:bg-gray-50 transition rounded-xl"
                        :class="isOpen ? 'bg-gray-50/50' : ''">
                    <div class="flex items-center">
                        <div class="w-1 h-5 rounded-full mr-3" :class="activeModule === {{ $m->id }} ? 'bg-brand' : 'bg-gray-200'"></div>
                        <span class="text-[11px] font-black text-gray-900 uppercase tracking-tight truncate max-w-[160px]">
                            {{ $m->title }}
                        </span>
                    </div>
                    <svg class="w-3 h-3 text-gray-400 transition-transform duration-300" 
                         :class="isOpen ? 'rotate-180 text-brand' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul class="pb-2" x-show="isOpen" x-collapse x-cloak>
                    @foreach($m->publishedLessons as $l)
                    @php
                        $isActive = $l->id === $lesson->id;
                        $isComp = $l->progress->first()?->is_completed ?? false;
                    @endphp
                    <li>
                        <a href="{{ route('lessons.show', [$course, $l]) }}" 
                           class="flex items-center pl-7 pr-2 py-1.5 rounded-xl transition text-[13px] relative group
                               {{ $isActive ? 'bg-brand/5 text-brand font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            @if($isActive)
                                <div class="absolute left-0 w-1 h-3 bg-brand rounded-r-full"></div>
                            @endif

                            <span class="w-4 h-4 rounded-lg flex items-center justify-center mr-2.5 flex-shrink-0 transition-colors
                                {{ $isComp ? 'bg-green-100 text-green-600' : ($isActive ? 'bg-brand text-white' : 'bg-gray-100 text-gray-400 group-hover:bg-gray-200') }}">
                                @if($isComp)
                                <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                @else
                                <span class="text-[9px]">{{ $loop->iteration }}</span>
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
    <main class="flex-grow bg-white">
        <div class="max-w-4xl mx-auto px-4 py-6 md:py-8">
            <!-- Lesson Header -->
            <div class="mb-6">
                <div class="flex items-center space-x-2 mb-2">
                    <span class="bg-brand/5 text-brand text-[9px] font-black px-1.5 py-0.5 rounded uppercase tracking-widest">{{ $module->title }}</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-gray-900 leading-tight">{{ $lesson->title }}</h1>
            </div>

            <!-- Video Player -->
            @if($lesson->hasVideo())
            <div class="aspect-video bg-black rounded-2xl overflow-hidden mb-8 shadow-xl ring-1 ring-black/5">
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
            <div class="prose prose-brand prose-sm max-w-none mb-8 bg-gray-50/30 p-6 md:p-8 rounded-2xl border border-gray-100">
                {!! $lesson->content !!}
            </div>
            @endif

            <!-- Assignment Section -->
            @if($assignment)
            <div class="bg-white rounded-2xl shadow-lg border border-brand/10 overflow-hidden mb-8 ring-1 ring-brand/5">
                @if($userSubmission)
                    <!-- COMPLETED STATE -->
                    <div class="p-8 text-center bg-green-50/50">
                        <div class="w-16 h-16 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <h2 class="text-xl font-black text-green-900 mb-2">Задание пройдено! ✅</h2>
                        <p class="text-green-700 font-medium">Ваш ответ успешно принят и сохранен в системе.</p>
                        
                        @if($assignment->type === 'quiz' && $userSubmission->score !== null)
                            <div class="mt-4 inline-block bg-white px-4 py-2 rounded-xl text-sm font-bold border border-green-100">
                                Результат: <span class="text-green-600">{{ $userSubmission->score }} / {{ $userSubmission->max_score }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <!-- FORM STATE -->
                    <div class="p-5 bg-brand/5 border-b border-brand/10 flex items-center justify-between">
                        <h2 class="text-sm font-black text-brand-dark flex items-center">
                            <div class="w-8 h-8 rounded-lg bg-brand text-white flex items-center justify-center mr-3 shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            {{ $assignment->title ?: $assignment->type_label }}
                        </h2>
                        @if($assignment->is_required)
                            <span class="text-[8px] font-black bg-orange-100 text-orange-600 px-2 py-0.5 rounded-full uppercase tracking-tighter">Обязательно</span>
                        @endif
                    </div>
                    
                    <div class="p-6 md:p-8">
                        @if($assignment->description)
                            <p class="text-gray-600 mb-6 text-sm leading-relaxed">{{ $assignment->description }}</p>
                        @endif

                        <form id="assignment-form" class="space-y-6">
                            @foreach($assignment->questions as $qi => $question)
                            <div class="group">
                                <h3 class="font-extrabold text-gray-900 mb-4 text-sm flex items-start">
                                    <span class="text-brand mr-2">{{ $qi + 1 }}.</span>
                                    <span>{{ $question->text }}</span>
                                </h3>
                                
                                <div class="space-y-2">
                                    @if($question->type === 'single')
                                        @foreach($question->answers as $answer)
                                        <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:border-brand/30 hover:bg-brand/5 cursor-pointer transition-all duration-300 group/label">
                                            <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}" class="w-4 h-4 text-brand focus:ring-brand border-gray-300">
                                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover/label:text-brand transition-colors">{{ $answer->text }}</span>
                                        </label>
                                        @endforeach
                                    @elseif($question->type === 'multiple')
                                        @foreach($question->answers as $answer)
                                        <label class="flex items-center p-3 border border-gray-100 rounded-xl hover:border-brand/30 hover:bg-brand/5 cursor-pointer transition-all duration-300 group/label">
                                            <input type="checkbox" name="question_{{ $question->id }}[]" value="{{ $answer->id }}" class="w-4 h-4 rounded text-brand focus:ring-brand border-gray-300">
                                            <span class="ml-3 text-sm font-bold text-gray-700 group-hover/label:text-brand transition-colors">{{ $answer->text }}</span>
                                        </label>
                                        @endforeach
                                    @else
                                        <textarea 
                                            name="question_{{ $question->id }}"
                                            rows="3"
                                            class="w-full border border-gray-200 rounded-xl p-4 focus:ring-brand focus:border-brand bg-gray-50/30 transition-all text-sm font-medium placeholder-gray-400"
                                            placeholder="Введите ваш ответ..."
                                            required
                                        ></textarea>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                            
                            <div class="pt-4">
                                <button type="submit" class="w-full bg-brand text-white font-black py-4 px-6 rounded-xl hover:opacity-90 transform active:scale-[0.98] transition-all shadow-lg shadow-brand/10 text-sm uppercase tracking-wider">
                                    Отправить задание
                                </button>
                            </div>
                        </form>
                    </div>
                @endif
            </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3 border-t border-gray-100 pt-6">
                @if($previousLesson)
                <a href="{{ route('lessons.show', [$course, $previousLesson]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-all text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад
                </a>
                @endif

                @if($nextLesson)
                <a href="{{ route('lessons.show', [$course, $nextLesson]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-brand text-white font-black rounded-xl hover:opacity-90 transition-all shadow-md shadow-brand/10 text-sm">
                    Следующий урок
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @else
                <a href="{{ route('courses.show', $course) }}" 
                   class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-500 text-white font-black rounded-xl hover:bg-green-600 transition-all shadow-md shadow-green-500/10 text-sm">
                    Завершить курс
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
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
    btn.innerHTML = '<div class="flex items-center justify-center"><svg class="animate-spin h-4 w-4 mr-2" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Отправляем...</div>';
    btn.disabled = true;
    
    // Collect answers
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
    
    // Submit to server
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
                detail: { message: data.error || 'Не удалось отправить ответы', type: 'error' } 
            }));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        window.dispatchEvent(new CustomEvent('notify', { 
            detail: { message: 'Произошла ошибка соединения', type: 'error' } 
        }));
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endpush

<style>
    .prose-brand {
        --tw-prose-links: var(--color-primary);
        --tw-prose-bullets: var(--color-primary);
        --tw-prose-counters: var(--color-primary);
    }
    [x-cloak] { display: none !important; }
</style>
@endsection
