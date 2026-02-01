<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentResource\Pages;
use App\Filament\Resources\AssignmentResource\RelationManagers;
use App\Models\Assignment;
use App\Models\Lesson;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AssignmentResource extends Resource
{
    protected static ?string $model = Assignment::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationLabel = 'Задания';
    
    protected static ?string $modelLabel = 'Задание';
    
    protected static ?string $pluralModelLabel = 'Задания';
    
    protected static ?string $navigationGroup = 'Контент';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Задание')
                    ->schema([
                        Forms\Components\Select::make('lesson_id')
                            ->label('Урок')
                            ->options(function () {
                                return Lesson::with('modules.course')
                                    ->get()
                                    ->mapWithKeys(function ($lesson) {
                                        $module = $lesson->modules->first();
                                        $courseName = $module?->course?->title ?? 'Без курса';
                                        $moduleName = $module?->title ?? 'Без модуля';
                                        return [
                                            $lesson->id => "{$courseName} → {$moduleName} → {$lesson->title}"
                                        ];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->default(request()->get('lesson_id')),

                        Forms\Components\Select::make('type')
                            ->label('Тип задания')
                            ->options([
                                'text' => '📝 Открытый вопрос (текстовый ответ)',
                                'poll' => '📊 Опрос (без правильного ответа)',
                                'quiz' => '✅ Тест (с правильными ответами)',
                            ])
                            ->required()
                            ->live(),

                        Forms\Components\TextInput::make('title')
                            ->label('Заголовок (необязательно)')
                            ->maxLength(255)
                            ->placeholder('Проверьте свои знания'),

                        Forms\Components\Textarea::make('description')
                            ->label('Описание')
                            ->rows(2)
                            ->placeholder('Инструкции для студента...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Настройки')
                    ->schema([
                        Forms\Components\Toggle::make('show_correct_answers')
                            ->label('Показывать правильные ответы')
                            ->helperText('Студент увидит правильные ответы после прохождения')
                            ->default(true)
                            ->hidden(fn (Forms\Get $get) => $get('type') === 'text'),

                        Forms\Components\Toggle::make('is_required')
                            ->label('Обязательно для продолжения')
                            ->helperText('Студент должен пройти задание чтобы продолжить')
                            ->default(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('lesson.title')
                    ->label('Урок')
                    ->searchable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'text' => '📝 Открытый вопрос',
                        'poll' => '📊 Опрос',
                        'quiz' => '✅ Тест',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'text',
                        'warning' => 'poll',
                        'success' => 'quiz',
                    ]),

                Tables\Columns\TextColumn::make('title')
                    ->label('Заголовок')
                    ->placeholder('—')
                    ->limit(30),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('Вопросов')
                    ->counts('questions'),

                Tables\Columns\IconColumn::make('is_required')
                    ->label('Обязательно')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options([
                        'text' => 'Открытый вопрос',
                        'poll' => 'Опрос',
                        'quiz' => 'Тест',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignments::route('/'),
            'create' => Pages\CreateAssignment::route('/create'),
            'edit' => Pages\EditAssignment::route('/{record}/edit'),
        ];
    }
}
