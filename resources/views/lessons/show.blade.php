@extends('layouts.app')

@section('title', $lesson->title . ' - GloboKids')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="lg:flex">
        <!-- Sidebar - Course Navigation -->
        <aside class="lg:w-80 bg-white border-r border-gray-100 lg:h-screen lg:sticky lg:top-0 overflow-y-auto">
            <div class="p-4 border-b border-gray-100">
                <a href="{{ route('courses.show', $course) }}" class="flex items-center text-purple-600 hover:text-purple-800 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Назад к курсу
                </a>
            </div>
            
            <!-- Modules list -->
            <nav class="p-4">
                @foreach($allModules as $m)
                <div class="mb-4">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-2">
                        {{ $m->title }}
                    </h3>
                    <ul class="space-y-1">
                        @foreach($m->publishedLessons as $l)
                        @php
                            $isActive = $l->id === $lesson->id;
                            $isCompleted = $l->progress->first()?->is_completed ?? false;
                        @endphp
                        <li>
                            <a href="{{ route('lessons.show', [$course, $l]) }}" 
                               class="flex items-center px-3 py-2 rounded-lg transition text-sm
                                   {{ $isActive ? 'bg-purple-100 text-purple-700 font-medium' : 'hover:bg-gray-50' }}">
                                <span class="w-5 h-5 rounded-full flex items-center justify-center mr-2 flex-shrink-0
                                    {{ $isCompleted ? 'bg-green-100 text-green-600' : 'bg-gray-100' }}">
                                    @if($isCompleted)
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    @else
                                    <span class="w-2 h-2 bg-gray-300 rounded-full"></span>
                                    @endif
                                </span>
                                <span class="truncate {{ $isActive ? '' : 'text-gray-700' }}">{{ $l->title }}</span>
                            </a>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-grow">
            <div class="max-w-4xl mx-auto px-4 py-8">
                <!-- Lesson Header -->
                <div class="mb-6">
                    <p class="text-sm text-purple-600 font-medium mb-2">{{ $module->title }}</p>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $lesson->title }}</h1>
                </div>

                <!-- Video -->
                @if($lesson->hasVideo())
                <div class="aspect-video bg-black rounded-xl overflow-hidden mb-8 shadow-lg">
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8 mb-8">
                    <div class="prose prose-purple max-w-none">
                        {!! $lesson->content !!}
                    </div>
                </div>
                @endif

                <!-- Assignment -->
                @if($assignment)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
                    <div class="p-6 bg-purple-50 border-b border-purple-100">
                        <h2 class="text-lg font-bold text-purple-900 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $assignment->title ?: $assignment->type_label }}
                        </h2>
                        @if($assignment->description)
                        <p class="text-purple-700 mt-2">{{ $assignment->description }}</p>
                        @endif
                    </div>
                    
                    <div class="p-6">
                        <form id="assignment-form" class="space-y-6">
                            @foreach($assignment->questions as $qi => $question)
                            <div class="border-b border-gray-100 pb-6 last:border-b-0 last:pb-0">
                                <p class="font-medium text-gray-900 mb-3">
                                    {{ $qi + 1 }}. {{ $question->text }}
                                </p>
                                
                                @if($question->type === 'single')
                                <div class="space-y-2">
                                    @foreach($question->answers as $answer)
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                        <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->id }}" class="text-purple-600 focus:ring-purple-500">
                                        <span class="ml-3">{{ $answer->text }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @elseif($question->type === 'multiple')
                                <div class="space-y-2">
                                    @foreach($question->answers as $answer)
                                    <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition">
                                        <input type="checkbox" name="question_{{ $question->id }}[]" value="{{ $answer->id }}" class="rounded text-purple-600 focus:ring-purple-500">
                                        <span class="ml-3">{{ $answer->text }}</span>
                                    </label>
                                    @endforeach
                                </div>
                                @else
                                {{-- Default: text input for 'text' type or unknown --}}
                                <textarea 
                                    name="question_{{ $question->id }}"
                                    rows="3"
                                    class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    placeholder="Введите ваш ответ..."
                                    required
                                ></textarea>
                                @endif
                            </div>
                            @endforeach
                            
                            <button type="submit" class="w-full gradient-bg text-white font-semibold py-3 px-6 rounded-xl hover:opacity-90 transition shadow-lg">
                                Отправить ответы
                            </button>
                        </form>
                    </div>
                </div>
                @endif

                <!-- Lesson Status -->
                @if($progress->is_completed)
                <div class="flex items-center bg-green-50 border border-green-200 rounded-xl p-4 mb-8">
                    <svg class="w-6 h-6 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <div>
                        <p class="font-medium text-green-800">Урок пройден ✅</p>
                        <p class="text-sm text-green-600">Отлично! Продолжайте обучение.</p>
                    </div>
                </div>
                @endif

                <!-- Navigation -->
                <div class="flex justify-between">
                    @if($previousLesson)
                    <a href="{{ route('lessons.show', [$course, $previousLesson]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Назад
                    </a>
                    @else
                    <div></div>
                    @endif

                    @if($nextLesson)
                    <a href="{{ route('lessons.show', [$course, $nextLesson]) }}" 
                       class="inline-flex items-center px-4 py-2 gradient-bg text-white rounded-lg hover:opacity-90 transition">
                        Далее
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                    @else
                    <a href="{{ route('courses.show', $course) }}" 
                       class="inline-flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition">
                        Завершить курс
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </a>
                    @endif
                </div>
            </div>
        </main>
    </div>
</div>

<style>
    .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
</style>

<script>
function markComplete() {
    const btn = event.target.closest('button');
    const originalText = btn.innerHTML;
    btn.innerHTML = '<svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
    btn.disabled = true;
    
    fetch('{{ route("lessons.complete", [$course, $lesson]) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network error');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось отметить урок'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка. Попробуйте ещё раз.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
}

document.getElementById('assignment-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = this.querySelector('button[type="submit"]');
    const originalText = btn.innerHTML;
    btn.innerHTML = 'Отправка...';
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
            // Show result
            let message = data.message;
            if (data.max_score) {
                message += `\n\nРезультат: ${data.score} из ${data.max_score}`;
            }
            alert(message);
            
            // Mark lesson complete and reload
            location.reload();
        } else {
            alert('Ошибка: ' + (data.error || 'Не удалось отправить ответы'));
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Произошла ошибка. Попробуйте ещё раз.');
        btn.innerHTML = originalText;
        btn.disabled = false;
    });
});
</script>
@endsection
