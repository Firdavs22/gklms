<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        
        <div class="mt-6">
            <x-filament::button type="submit">
                Сохранить настройки
            </x-filament::button>
        </div>
    </form>

    <div class="mt-8 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
        <h3 class="text-lg font-medium mb-4">Предпросмотр</h3>
        @php
            $logoPath = $this->data['logo_path'] ?? null;
            if (is_array($logoPath)) {
                $logoPath = $logoPath[0] ?? null;
            }
        @endphp
        <div class="flex items-center gap-4 p-4 bg-white dark:bg-gray-900 rounded-lg border">
            @if($logoPath)
                <img 
                    src="{{ Storage::disk('public')->url($logoPath) }}" 
                    alt="Logo" 
                    class="h-10"
                >
            @else
                <div 
                    class="h-10 w-10 rounded-lg flex items-center justify-center text-white font-bold"
                    style="background: {{ $this->data['primary_color'] ?? '#7c3aed' }}"
                >
                    {{ substr($this->data['site_name'] ?? 'G', 0, 1) }}
                </div>
            @endif
            <span class="font-semibold text-lg">{{ $this->data['site_name'] ?? 'GloboKids Edu' }}</span>
        </div>
        
        <div class="mt-4 flex gap-4">
            <div class="flex items-center gap-2">
                <div 
                    class="w-8 h-8 rounded"
                    style="background: {{ $this->data['primary_color'] ?? '#7c3aed' }}"
                ></div>
                <span class="text-sm">Основной</span>
            </div>
            <div class="flex items-center gap-2">
                <div 
                    class="w-8 h-8 rounded"
                    style="background: {{ $this->data['secondary_color'] ?? '#a855f7' }}"
                ></div>
                <span class="text-sm">Дополнительный</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
