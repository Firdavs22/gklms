<?php

namespace App\Filament\Resources\AssignmentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';
    
    protected static ?string $title = 'Вопросы';
    protected static ?string $modelLabel = 'вопрос';
    protected static ?string $pluralModelLabel = 'вопросы';

    public function form(Form $form): Form
    {
        $assignmentType = $this->getOwnerRecord()->type;

        return $form
            ->schema([
                Forms\Components\Textarea::make('text')
                    ->label('Текст вопроса')
                    ->required()
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Select::make('type')
                    ->label('Тип ответа')
                    ->options($this->getQuestionTypeOptions($assignmentType))
                    ->required()
                    ->default($this->getDefaultQuestionType($assignmentType))
                    ->live(),

                Forms\Components\Repeater::make('answers')
                    ->label('Варианты ответов')
                    ->relationship()
                    ->schema([
                        Forms\Components\TextInput::make('text')
                            ->label('Ответ')
                            ->required(),
                        
                        Forms\Components\Toggle::make('is_correct')
                            ->label('Правильный')
                            ->visible(fn () => $assignmentType === 'quiz'),
                    ])
                    ->columns(2)
                    ->reorderable('sort_order')
                    ->addActionLabel('Добавить вариант')
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['single', 'multiple']))
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#'),

                Tables\Columns\TextColumn::make('text')
                    ->label('Вопрос')
                    ->limit(50)
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'text' => 'Текст',
                        'single' => 'Один ответ',
                        'multiple' => 'Несколько',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('answers_count')
                    ->label('Вариантов')
                    ->counts('answers'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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

    private function getQuestionTypeOptions(string $assignmentType): array
    {
        if ($assignmentType === 'text') {
            return ['text' => 'Текстовый ответ'];
        }

        return [
            'single' => 'Один правильный ответ',
            'multiple' => 'Несколько правильных ответов',
        ];
    }

    private function getDefaultQuestionType(string $assignmentType): string
    {
        return $assignmentType === 'text' ? 'text' : 'single';
    }
}
