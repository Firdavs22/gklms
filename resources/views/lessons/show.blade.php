@extends('layouts.app')

@section('title', $lesson->title . ' - GloboKids')

@section('content')
<div class="min-h-screen bg-gray-50 flex flex-col lg:flex-row">
    <!-- Sidebar - Course Navigation -->
    <aside class="lg:w-80 bg-white border-r border-gray-100 lg:h-[calc(100vh-64px)] lg:sticky lg:top-16 overflow-y-auto z-10"
           x-data="{ activeModule: {{ $module->id }} }">
        <div class="p-4 border-b border-gray-100">
            <a href="{{ route('courses.show', $course) }}" class="flex items-center text-brand hover:opacity-80 transition font-bold text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                К программе курса
            </a>
        </div>
        
        <!-- Modules list Accordion -->
        <nav class="p-2 space-y-1">
            @foreach($allModules as $m)
            <div class="rounded-xl overflow-hidden" x-data="{ isOpen: activeModule === {{ $m->id }} }">
                <button @click="isOpen = !isOpen" 
                        class="w-full flex items-center justify-between p-3 text-left hover:bg-gray-50 transition rounded-xl"
                        :class="isOpen ? 'bg-gray-50/50' : ''">
                    <div class="flex items-center">
                        <div class="w-1.5 h-6 rounded-full mr-3" :class="activeModule === {{ $m->id }} ? 'bg-brand' : 'bg-gray-200'"></div>
                        <span class="text-xs font-black text-gray-900 uppercase tracking-wider truncate max-w-[180px]">
                            {{ $m->title }}
                        </span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform duration-300" 
                         :class="isOpen ? 'rotate-180 text-brand' : ''"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <ul class="mt-1 pb-2 space-y-0.5" x-show="isOpen" x-collapse x-cloak>
                    @foreach($m->publishedLessons as $l)
                    @php
                        $isActive = $l->id === $lesson->id;
                        $isComp = $l->progress->first()?->is_completed ?? false;
                    @endphp
                    <li>
                        <a href="{{ route('lessons.show', [$course, $l]) }}" 
                           class="flex items-center pl-8 pr-3 py-2.5 rounded-xl transition text-sm relative group
                               {{ $isActive ? 'bg-brand/5 text-brand font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                            @if($isActive)
                                <div class="absolute left-0 w-1 h-4 bg-brand rounded-r-full"></div>
                            @endif

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
    <main class="flex-grow bg-white">
        <div class="max-w-4xl mx-auto px-4 py-8 md:py-12">
            <!-- Lesson Header -->
            <div class="mb-10">
                <div class="flex items-center space-x-2 mb-3">
                    <span class="bg-brand/10 text-brand text-[10px] font-black px-2 py-0.5 rounded uppercase tracking-widest">{{ $module->title }}</span>
                </div>
                <h1 class="text-3xl md:text-4xl font-black text-gray-900 leading-tight">{{ $lesson->title }}</h1>
            </div>

            <!-- Video Player -->
            @if($lesson->hasVideo())
            <div class="aspect-video bg-black rounded-3xl overflow-hidden mb-12 shadow-2xl ring-1 ring-black/5">
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
            <div class="prose prose-brand prose-lg max-w-none mb-12 bg-gray-50/50 p-8 md:p-12 rounded-3xl border border-gray-100">
                {!! $lesson->content !!}
            </div>
            @endif

            <!-- Assignment Section -->
            @if($assignment)
            <div class="bg-white rounded-3xl shadow-xl border border-brand/10 overflow-hidden mb-12 ring-1 ring-brand/5">
                <div class="p-8 bg-brand/5 border-b border-brand/10">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-black text-brand-dark flex items-center">
                            <div class="w-10 h-10 rounded-xl bg-brand text-white flex items-center justify-center mr-4 shadow-lg shadow-brand/20">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            {{ $assignment->title ?: $assignment->type_label }}
                        </h2>
                        @if($assignment->is_required)
                            <span class="text-[10px] font-black bg-orange-100 text-orange-600 px-2 py-1 rounded-full uppercase tracking-tighter">Обязательно</span>
                        @endif
                    </div>
                    @if($assignment->description)
                    <p class="text-gray-600 mt-4 text-lg leading-relaxed">{{ $assignment->description }}</p>
                    @endif
                </div>
                
                <div class="p-8 md:p-10">
                    <form id="assignment-form" class="space-y-8">
                        @foreach($assignment->questions as $qi => $question)
                        <div class="group">
                            <h3 class="font-extrabold text-gray-900 mb-5 flex items-start">
                                <span class="text-brand mr-3">{{ $qi + 1 }}.</span>
                                <span>{{ $question->text }}</span>
                            </h3>
                            
                            <div class="space-y-3">
                                @if($question->type === 'single')
                                    @foreach($question->answers as $answer)
                                    <label class="flex items-center p-4 border-2 border-gray-100 rounded-2xl hover:border-brand/30 hover:bg-brand/5 cursor-pointer transition-all duration-300 group/label">
                                        <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}" class="w-5 h-5 text-brand focus:ring-brand border-gray-300">
                                        <span class="ml-4 font-bold text-gray-700 group-hover/label:text-brand transition-colors">{{ $answer->text }}</span>
                                    </label>
                                    @endforeach
                                @elseif($question->type === 'multiple')
                                    @foreach($question->answers as $answer)
                                    <label class="flex items-center p-4 border-2 border-gray-100 rounded-2xl hover:border-brand/30 hover:bg-brand/5 cursor-pointer transition-all duration-300 group/label">
                                        <input type="checkbox" name="question_{{ $question->id }}[]" value="{{ $answer->id }}" class="w-5 h-5 rounded text-brand focus:ring-brand border-gray-300">
                                        <span class="ml-4 font-bold text-gray-700 group-hover/label:text-brand transition-colors">{{ $answer->text }}</span>
                                    </label>
                                    @endforeach
                                @else
                                    <textarea 
                                        name="question_{{ $question->id }}"
                                        rows="4"
                                        class="w-full border-2 border-gray-100 rounded-2xl p-5 focus:ring- brand focus:border-brand focus:bg-white bg-gray-50/50 transition-all text-gray-700 font-medium placeholder-gray-400"
                                        placeholder="Напишите ваш подробный ответ здесь..."
                                        required
                                    ></textarea>
                                @endif
                            </div>
                        </div>
                        @endforeach
                        
                        <div class="pt-6">
                            <button type="submit" class="w-full bg-brand text-white font-black py-5 px-8 rounded-2xl hover:opacity-90 transform active:scale-[0.98] transition-all shadow-xl shadow-brand/20 text-lg uppercase tracking-wider">
                                Отправить задание
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

            <!-- Lesson Completion Status -->
            @if($progress->is_completed)
            <div class="flex items-center bg-green-50 border-2 border-green-100 rounded-3xl p-8 mb-12 shadow-sm">
                <div class="w-14 h-14 bg-green-100 text-green-600 rounded-2xl flex items-center justify-center mr-6 flex-shrink-0">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-xl font-black text-green-900 mb-1">Урок успешно пройден!</h4>
                    <p class="text-green-700 font-medium opacity-80">Вы молодец, двигайтесь дальше к новым вершинам.</p>
                </div>
            </div>
            @endif

            <!-- Navigation Buttons -->
            <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4 border-t border-gray-100 pt-10">
                @if($previousLesson)
                <a href="{{ route('lessons.show', [$course, $previousLesson]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-all">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Предыдущий урок
                </a>
                @endif

                @if($nextLesson)
                <a href="{{ route('lessons.show', [$course, $nextLesson]) }}" 
                   class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-brand text-white font-black rounded-2xl hover:opacity-90 transition-all shadow-lg shadow-brand/10">
                    Следующий урок
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
                @else
                <a href="{{ route('courses.show', $course) }}" 
                   class="flex-1 inline-flex items-center justify-center px-8 py-4 bg-green-500 text-white font-black rounded-2xl hover:bg-green-600 transition-all shadow-lg shadow-green-500/10">
                    Завершить модуль
                    <svg class="w-5 h-5 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
    btn.innerHTML = '<div class="flex items-center justify-center"><svg class="animate-spin h-5 w-5 mr-3" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Отправляем...</div>';
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
