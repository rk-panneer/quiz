<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use App\Models\Question;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $recordTitleAttribute = 'question_text';

    public function form(Schema $form): Schema
    {
        return $form->schema([
            Grid::make(1)
                ->schema([
                    Textarea::make('question_text')
                        ->required()
                        ->rows(2)
                        ->columnSpanFull(),

                    Select::make('question_type')
                        ->options(Question::typeLabels())
                        ->required()
                        ->live(),

                    TextInput::make('order')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Toggle::make('is_required')
                        ->default(true),
                ]),

            // 1. MCQ and BOOLEAN Options
            Section::make('Answer Options')
                ->description('Configure choices for MCQ or Boolean questions.')
                ->hidden(fn(Get $get) => !in_array($get('question_type'), [
                    Question::TYPE_MCQ_SINGLE,
                    Question::TYPE_MCQ_MULTIPLE,
                    Question::TYPE_BOOLEAN
                ]))
                ->schema([
                    Repeater::make('options')
                        ->relationship('options')
                        ->schema([
                            Grid::make(12)
                                ->schema([
                                    TextInput::make('option_text')
                                        ->required()
                                        ->columnSpan(5),
                                    Toggle::make('is_correct')
                                        ->label('Correct?')
                                        ->columnSpan(3)
                                        ->live()
                                        ->afterStateUpdated(function ($state, Get $get, Set $set, $component) {
                                            if ($state && $get('../../question_type') === Question::TYPE_MCQ_SINGLE) {
                                                $path = $component->getStatePath();
                                                $currentRowKey = (string) str($path)->beforeLast('.')->afterLast('.');
                                                $options = $get('../../options') ?? [];

                                                foreach ($options as $key => $option) {
                                                    if ($key !== $currentRowKey) {
                                                        $options[$key]['is_correct'] = false;
                                                    }
                                                }

                                                $set('../../options', $options);
                                            }
                                        }),
                                    TextInput::make('score')
                                        ->numeric()
                                        ->default(0)
                                        ->columnSpan(2),
                                    TextInput::make('order')
                                        ->numeric()
                                        ->default(0)
                                        ->columnSpan(2),
                                ]),
                        ])
                        ->defaultItems(fn(Get $get) => $get('question_type') === Question::TYPE_BOOLEAN ? 2 : 1)
                        ->addActionLabel('Add Option')
                        ->rules([
                            function (Get $get) {
                                return function (string $attribute, $value, \Closure $fail) use ($get) {
                                    if ($get('question_type') === Question::TYPE_MCQ_SINGLE) {
                                        $correctCount = collect($value)->where('is_correct', true)->count();
                                        if ($correctCount > 1) {
                                            $fail('Single choice questions can only have one correct answer.');
                                        }
                                    }
                                };
                            },
                        ]),
                ]),

            // 2. Number Ranges
            Section::make('Number Ranges')
                ->description('Define scoring bands for numeric inputs.')
                ->hidden(fn(Get $get) => $get('question_type') !== Question::TYPE_NUMBER_RANGE)
                ->schema([
                    Repeater::make('numberRanges')
                        ->relationship('numberRanges')
                        ->schema([
                            TextInput::make('min_value')->numeric()->required()->rules(['lte:max_value'])->columnSpan(4),
                            TextInput::make('max_value')->numeric()->required()->rules(['gte:min_value'])->columnSpan(4),
                            TextInput::make('score')->numeric()->required()->default(0)->columnSpan(4),
                        ])
                        ->columns(12)
                        ->addActionLabel('Add Range'),
                ]),

            // 3. Keywords
            Section::make('Keywords')
                ->description('Add keywords to match in user text response.')
                ->hidden(fn(Get $get) => $get('question_type') !== Question::TYPE_TEXT_KEYWORDS)
                ->schema([
                    Repeater::make('keywords')
                        ->relationship('keywords')
                        ->schema([
                            TextInput::make('keyword')->required()->columnSpan(8),
                            TextInput::make('score')->numeric()->required()->default(1)->columnSpan(4),
                        ])
                        ->columns(12)
                        ->addActionLabel('Add Keyword'),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
                TextColumn::make('question_text')->limit(50),
                TextColumn::make('question_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => Question::typeLabels()[$state] ?? $state),
                IconColumn::make('is_required')->boolean(),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
