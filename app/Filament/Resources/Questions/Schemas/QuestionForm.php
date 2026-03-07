<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\Question;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function schema(): array
    {
        return [
            Tabs::make('Question Setup')
                ->tabs([
                    Tab::make('Content')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Select::make('question_type')
                                ->options(Question::typeLabels())
                                ->required()
                                ->live(),

                            Select::make('quizzes')
                                ->relationship('quizzes', 'title')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->label('Quizzes')
                                ->helperText('Associate this question with one or more quizzes.'),

                            Textarea::make('question_text')
                                ->required()
                                ->rows(3)
                                ->columnSpanFull()
                                ->live(onBlur: true)
                                ->maxLength(1000)
                                ->rules(fn($record) => [
                                    new \App\Rules\UniqueNormalizedQuestion($record?->id),
                                    new \App\Rules\SemanticUniqueQuestion($record?->id),
                                    'regex:/^(?!.*<script.*?>).*$/is'
                                ])
                                ->afterStateUpdated(function ($state, Set $set, \App\Services\QuestionService $service, $record) {
                                    if (!$state)
                                        return;
                                    $result = $service->detectDuplicates($state, $record?->id);
                                    if ($result) {
                                        \Filament\Notifications\Notification::make()
                                            ->warning()
                                            ->title('Potential Duplicate Found')
                                            ->body("Similar content exists: \"{$result['question']->question_text}\" (Match Type: {$result['type']})")
                                            ->persistent()
                                            ->send();
                                    }
                                }),
                        ]),

                    Tab::make('Media Attachment')
                        ->icon('heroicon-o-camera')
                        ->schema([
                            Section::make('Visual/Audio Context')
                                ->schema([
                                    Select::make('media_type')
                                        ->options([
                                            Question::MEDIA_TYPE_NONE => 'Text Only',
                                            Question::MEDIA_TYPE_IMAGE => 'Image (Upload Local)',
                                            Question::MEDIA_TYPE_VIDEO => 'Video (External URL)',
                                            Question::MEDIA_TYPE_AUDIO => 'Audio (External URL)',
                                        ])
                                        ->default(Question::MEDIA_TYPE_NONE)
                                        ->live(),

                                    FileUpload::make('media_path')
                                        ->image()
                                        ->label('Upload Image')
                                        ->disk('public')
                                        ->directory('questions')
                                        ->visible(fn(Get $get) => $get('media_type') === Question::MEDIA_TYPE_IMAGE)
                                        ->required(fn(Get $get) => $get('media_type') === Question::MEDIA_TYPE_IMAGE),

                                    TextInput::make('media_url')
                                        ->url()
                                        ->label('Embedded Resource URL')
                                        ->placeholder('https://www.youtube.com/watch?v=...')
                                        ->visible(fn(Get $get) => in_array($get('media_type'), [Question::MEDIA_TYPE_VIDEO, Question::MEDIA_TYPE_AUDIO]))
                                        ->required(fn(Get $get) => in_array($get('media_type'), [Question::MEDIA_TYPE_VIDEO, Question::MEDIA_TYPE_AUDIO])),
                                ])
                        ]),

                    Tab::make('Logic & Scoring')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('order')
                                        ->numeric()
                                        ->default(0)
                                        ->required(),

                                    Toggle::make('is_required')
                                        ->default(true)
                                        ->label('Mandatory field?'),
                                ]),

                            Section::make('Answer Key')
                                ->schema([
                                    Repeater::make('options')
                                        ->relationship('options')
                                        ->hidden(fn(Get $get) => !in_array($get('question_type'), [
                                            Question::TYPE_MCQ_SINGLE,
                                            Question::TYPE_MCQ_MULTIPLE,
                                            Question::TYPE_BOOLEAN,
                                            Question::TYPE_IMAGE_ANSWER,
                                        ]))
                                        ->schema([
                                            Grid::make(12)
                                                ->schema([
                                                    TextInput::make('option_text')
                                                        ->required(fn(Get $get) => in_array($get('../../question_type'), [Question::TYPE_MCQ_SINGLE, Question::TYPE_MCQ_MULTIPLE, Question::TYPE_BOOLEAN]))
                                                        ->columnSpan(3),
                                                    FileUpload::make('image_path')
                                                        ->image()
                                                        ->disk('public')
                                                        ->directory('options')
                                                        ->columnSpan(3)
                                                        ->label('Select Image'),
                                                    Toggle::make('is_correct')->label('Correct?')->columnSpan(2)->live()
                                                        ->afterStateUpdated(function ($state, Get $get, Set $set, $component) {
                                                            if ($state && in_array($get('../../question_type'), [Question::TYPE_MCQ_SINGLE, Question::TYPE_IMAGE_ANSWER, Question::TYPE_BOOLEAN])) {
                                                                $path = $component->getStatePath();
                                                                $currentRowKey = (string) str($path)->beforeLast('.')->afterLast('.');
                                                                $options = $get('../../options') ?? [];
                                                                foreach ($options as $key => $option) {
                                                                    if ($key !== $currentRowKey)
                                                                        $options[$key]['is_correct'] = false;
                                                                }
                                                                $set('../../options', $options);
                                                            }
                                                        }),
                                                    TextInput::make('score')->numeric()->default(0)->columnSpan(2),
                                                    TextInput::make('order')->numeric()->default(0)->columnSpan(2),
                                                ]),
                                        ])->maxItems(10)->addActionLabel('Add Option'),

                                    Repeater::make('numberRanges')
                                        ->relationship('numberRanges')
                                        ->hidden(fn(Get $get) => $get('question_type') !== Question::TYPE_NUMBER_RANGE)
                                        ->schema([
                                            TextInput::make('min_value')->numeric()->required()->columnSpan(4),
                                            TextInput::make('max_value')->numeric()->required()->columnSpan(4),
                                            TextInput::make('score')->numeric()->required()->default(0)->columnSpan(4),
                                        ])->columns(12)->addActionLabel('Add Range'),

                                    Repeater::make('keywords')
                                        ->relationship('keywords')
                                        ->hidden(fn(Get $get) => $get('question_type') !== Question::TYPE_TEXT_KEYWORDS)
                                        ->schema([
                                            TextInput::make('keyword')->required()->columnSpan(8),
                                            TextInput::make('score')->numeric()->required()->default(1)->columnSpan(4),
                                        ])->columns(12)->addActionLabel('Add Keyword'),

                                    Placeholder::make('media_response_note')
                                        ->label('Media Choice Mode')
                                        ->content('For Image responses, users will select from the illustrated options above. Ensure at least one option is marked as correct.')
                                        ->visible(fn(Get $get) => $get('question_type') === Question::TYPE_IMAGE_ANSWER),
                                ])
                        ]),
                ])->columnSpanFull()
        ];
    }
}
