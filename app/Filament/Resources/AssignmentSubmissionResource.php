<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AssignmentSubmissionResource\Pages;
use App\Models\AssignmentSubmission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;

class AssignmentSubmissionResource extends Resource
{
    protected static ?string $model = AssignmentSubmission::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    
    protected static ?string $navigationLabel = 'Ответы студентов';
    
    protected static ?string $modelLabel = 'Ответ';
    
    protected static ?string $pluralModelLabel = 'Ответы студентов';
    
    protected static ?string $navigationGroup = 'Контент';
    
    protected static ?int $navigationSort = 4;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Read-only view
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Студент')
                    ->description(fn ($record) => $record->user?->email ?? $record->user?->phone ?? '—')
                    ->searchable(['name', 'email', 'phone']),

                Tables\Columns\TextColumn::make('assignment.lesson.title')
                    ->label('Урок')
                    ->default('—')
                    ->limit(30)
                    ->searchable(),

                Tables\Columns\TextColumn::make('assignment.type')
                    ->label('Тип')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => match ($state) {
                        'text' => '📝 Текст',
                        'poll' => '📊 Опрос',
                        'quiz' => '✅ Тест',
                        default => $state ?? '-',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'text' => 'gray',
                        'poll' => 'warning',
                        'quiz' => 'success',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('score')
                    ->label('Результат')
                    ->formatStateUsing(function ($state, $record) {
                        if ($record->max_score === null) {
                            return '—';
                        }
                        return "{$state}/{$record->max_score}";
                    }),

                Tables\Columns\IconColumn::make('is_passed')
                    ->label('Зачтено')
                    ->boolean(),

                Tables\Columns\TextColumn::make('submitted_at')
                    ->label('Дата отправки')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('assignment_id')
                    ->label('Задание')
                    ->relationship('assignment', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('is_passed')
                    ->label('Только зачтённые')
                    ->query(fn ($query) => $query->where('is_passed', true)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Информация')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Студент'),
                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Email'),
                        Infolists\Components\TextEntry::make('assignment.lesson.title')
                            ->label('Урок'),
                        Infolists\Components\TextEntry::make('submitted_at')
                            ->label('Дата отправки')
                            ->dateTime('d.m.Y H:i'),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Результат')
                    ->schema([
                        Infolists\Components\TextEntry::make('score')
                            ->label('Баллы')
                            ->formatStateUsing(fn ($state, $record) => 
                                $record->max_score ? "{$state} из {$record->max_score}" : '—'
                            ),
                        Infolists\Components\IconEntry::make('is_passed')
                            ->label('Зачтено')
                            ->boolean(),
                    ])
                    ->columns(2)
                    ->visible(fn ($record) => $record->max_score !== null),

                Infolists\Components\Section::make('Ответы')
                    ->schema([
                        Infolists\Components\RepeatableEntry::make('formatted_answers')
                            ->label('')
                            ->schema([
                                Infolists\Components\TextEntry::make('question')
                                    ->label('Вопрос')
                                    ->weight('bold'),
                                Infolists\Components\TextEntry::make('user_answer')
                                    ->label('Ответ студента')
                                    ->formatStateUsing(function ($state) {
                                        if (is_array($state)) {
                                            return implode(', ', $state);
                                        }
                                        return $state ?: '(пусто)';
                                    }),
                                Infolists\Components\IconEntry::make('is_correct')
                                    ->label('Верно')
                                    ->boolean()
                                    ->visible(fn ($state) => $state !== null),
                            ])
                            ->columns(3),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAssignmentSubmissions::route('/'),
            'view' => Pages\ViewAssignmentSubmission::route('/{record}'),
        ];
    }
}
