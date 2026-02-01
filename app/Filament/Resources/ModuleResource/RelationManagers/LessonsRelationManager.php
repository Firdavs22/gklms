<?php

namespace App\Filament\Resources\ModuleResource\RelationManagers;

use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';
    
    protected static ?string $title = 'Уроки';
    protected static ?string $modelLabel = 'урок';
    protected static ?string $pluralModelLabel = 'уроки';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('lesson_module.sort_order')
            ->defaultSort('lesson_module.sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\IconColumn::make('video_url')
                    ->label('Видео')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasVideo()),

                Tables\Columns\IconColumn::make('assignment')
                    ->label('Задание')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasAssignment()),

                Tables\Columns\TextColumn::make('modules_count')
                    ->label('В модулях')
                    ->getStateUsing(fn ($record) => $record->modules()->count())
                    ->badge(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубликован')
                    ->boolean(),
            ])
            ->headerActions([
                // Attach existing lesson
                Tables\Actions\AttachAction::make()
                    ->label('Добавить урок')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['title'])
                    ->recordTitle(fn (Lesson $record) => $record->title),
                
                // Create new lesson and attach
                Tables\Actions\CreateAction::make()
                    ->label('Создать новый урок')
                    ->form([
                        Forms\Components\TextInput::make('title')
                            ->label('Название урока')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('video_source')
                            ->label('Источник видео')
                            ->options([
                                'url' => 'Ссылка (YouTube, Kinescope, VK)',
                                'yandex_disk' => 'Яндекс.Диск',
                            ])
                            ->default('url')
                            ->live(),

                        Forms\Components\TextInput::make('video_url')
                            ->label('Ссылка на видео')
                            ->url()
                            ->hidden(fn (Forms\Get $get) => $get('video_source') !== 'url'),

                        Forms\Components\TextInput::make('video_path')
                            ->label('Путь на Яндекс.Диске')
                            ->hidden(fn (Forms\Get $get) => $get('video_source') !== 'yandex_disk'),

                        Forms\Components\RichEditor::make('content')
                            ->label('Содержание урока'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Опубликован')
                            ->default(true),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('edit_lesson')
                    ->label('Редактировать')
                    ->icon('heroicon-o-pencil')
                    ->url(fn ($record) => route('filament.admin.resources.lessons.edit', $record)),
                
                Tables\Actions\DetachAction::make()
                    ->label('Убрать из модуля'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DetachBulkAction::make()
                        ->label('Убрать из модуля'),
                ]),
            ]);
    }
}
