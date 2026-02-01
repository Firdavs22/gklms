<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';
    
    protected static ?string $title = 'Уроки';
    
    protected static ?string $modelLabel = 'Урок';
    
    protected static ?string $pluralModelLabel = 'Уроки';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Название урока')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('video_url')
                    ->label('Ссылка на видео')
                    ->url()
                    ->helperText('YouTube, Kinescope или Vimeo')
                    ->columnSpanFull(),
                
                Forms\Components\RichEditor::make('content')
                    ->label('Содержание урока')
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('sort_order')
                    ->label('Порядок')
                    ->numeric()
                    ->default(0),
                
                Forms\Components\Toggle::make('is_published')
                    ->label('Опубликован')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('title')
                    ->label('Название')
                    ->searchable(),
                
                Tables\Columns\IconColumn::make('video_url')
                    ->label('Видео')
                    ->boolean()
                    ->getStateUsing(fn ($record) => !empty($record->video_url)),
                
                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубликован')
                    ->boolean(),
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
}
