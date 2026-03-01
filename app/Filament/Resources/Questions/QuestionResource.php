<?php

namespace App\Filament\Resources\Questions;

use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\Questions\Pages\EditQuestion;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Models\Question;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'All Questions';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('General Information')
                ->schema([
                    Select::make('quiz_id')
                        ->relationship('quiz', 'title')
                        ->required()
                        ->searchable(),

                    Select::make('question_type')
                        ->options(Question::typeLabels())
                        ->required()
                        ->live(),

                    Textarea::make('question_text')
                        ->required()
                        ->rows(2)
                        ->columnSpanFull(),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('order')
                                ->numeric()
                                ->default(0)
                                ->required(),

                            Toggle::make('is_required')
                                ->default(true),
                        ]),
                ]),

            Section::make('Answer Configuration')
                ->description('Configure options, ranges, or keywords depending on the type.')
                ->schema([
                    Repeater::make('options')
                        ->relationship('options')
                        ->hidden(fn(Get $get) => !in_array($get('question_type'), [
                            Question::TYPE_MCQ_SINGLE,
                            Question::TYPE_MCQ_MULTIPLE,
                            Question::TYPE_BOOLEAN
                        ]))
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

                    Repeater::make('numberRanges')
                        ->relationship('numberRanges')
                        ->hidden(fn(Get $get) => $get('question_type') !== Question::TYPE_NUMBER_RANGE)
                        ->schema([
                            TextInput::make('min_value')->numeric()->required()->columnSpan(4),
                            TextInput::make('max_value')->numeric()->required()->columnSpan(4),
                            TextInput::make('score')->numeric()->required()->columnSpan(4),
                        ])->columns(12),

                    Repeater::make('keywords')
                        ->relationship('keywords')
                        ->hidden(fn(Get $get) => $get('question_type') !== Question::TYPE_TEXT_KEYWORDS)
                        ->schema([
                            TextInput::make('keyword')->required()->columnSpan(8),
                            TextInput::make('score')->numeric()->required()->columnSpan(4),
                        ])->columns(12),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->searchable()
                    ->sortable()
                    ->label('Quiz'),

                TextColumn::make('question_text')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('question_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => Question::typeLabels()[$state] ?? $state),

                IconColumn::make('is_required')
                    ->boolean(),

                TextColumn::make('order')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('quiz')
                    ->relationship('quiz', 'title'),
                SelectFilter::make('question_type')
                    ->options(Question::typeLabels()),
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

    public static function getPages(): array
    {
        return [
            'index' => ListQuestions::route('/'),
            'create' => CreateQuestion::route('/create'),
            'edit' => EditQuestion::route('/{record}/edit'),
        ];
    }
}
