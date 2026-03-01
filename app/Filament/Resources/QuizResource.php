<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizResource\Pages;
use App\Filament\Resources\QuizResource\RelationManagers;
use App\Models\Quiz;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Str;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Quizzes';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $form): Schema
    {
        return $form->schema([
            Section::make('Quiz Details')
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state))),

                    TextInput::make('slug')
                        ->required()
                        ->unique(Quiz::class, 'slug', ignoreRecord: true)
                        ->maxLength(255)
                        ->readOnly()
                        ->helperText('Automatically generated from title.'),

                    Textarea::make('description')
                        ->nullable()
                        ->rows(3)
                        ->columnSpanFull(),

                    Select::make('status')
                        ->options([
                            'draft' => 'Draft',
                            'published' => 'Published',
                            'archived' => 'Archived',
                        ])
                        ->default('draft')
                        ->required(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'gray' => 'draft',
                        'success' => 'published',
                        'warning' => 'archived',
                    ]),

                TextColumn::make('questions_count')
                    ->counts('questions')
                    ->label('Questions'),

                TextColumn::make('attempts_count')
                    ->counts('attempts')
                    ->label('Attempts'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ]),
            ])
            ->actions([
                EditAction::make(),
                Action::make('view_public')
                    ->label('View')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn(Quiz $record) => route('quiz.show', $record->slug))
                    ->openUrlInNewTab()
                    ->visible(fn(Quiz $record) => $record->status === 'published'),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelationManagers(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizzes::route('/'),
            'create' => Pages\CreateQuiz::route('/create'),
            'edit' => Pages\EditQuiz::route('/{record}/edit'),
        ];
    }

    /**
     * Auto-fill created_by with the currently authenticated admin.
     */
    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
