<?php

namespace App\Filament\Resources\Questions;

use App\Filament\Resources\Questions\Pages\CreateQuestion;
use App\Filament\Resources\Questions\Pages\EditQuestion;
use App\Filament\Resources\Questions\Pages\ListQuestions;
use App\Models\Question;
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
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\CreateAction;
use Filament\Actions\AttachAction;
use Filament\Actions\EditAction as TableEditAction;
use Filament\Actions\DeleteAction as TableDeleteAction;
use Filament\Actions\BulkActionGroup as TableBulkActionGroup;
use Filament\Actions\DeleteBulkAction as TableDeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkAction as TableBulkAction;
use Filament\Actions\Action as TableAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Collection;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';
    protected static ?string $navigationLabel = 'All Questions';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $form): Schema
    {
        return $form->schema(\App\Filament\Resources\Questions\Schemas\QuestionForm::schema());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quizzes_count')
                    ->counts('quizzes')
                    ->label('Used In')
                    ->suffix(' Quizzes')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                TextColumn::make('question_text')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('question_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => Question::typeLabels()[$state] ?? $state)
                    ->colors([
                        'primary' => fn($state) => in_array($state, [Question::TYPE_MCQ_SINGLE, Question::TYPE_MCQ_MULTIPLE]),
                        'success' => Question::TYPE_BOOLEAN,
                        'warning' => Question::TYPE_IMAGE_ANSWER,
                        'info' => Question::TYPE_TEXT_KEYWORDS,
                        'danger' => Question::TYPE_NUMBER_RANGE,
                    ]),


                TextColumn::make('media_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->colors([
                        'gray' => Question::MEDIA_TYPE_NONE,
                        'info' => Question::MEDIA_TYPE_IMAGE,
                        'success' => Question::MEDIA_TYPE_VIDEO,
                        'warning' => Question::MEDIA_TYPE_AUDIO,
                    ]),

                IconColumn::make('is_required')
                    ->boolean()
                    ->label('Req'),

                TextColumn::make('order')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('quizzes')
                    ->relationship('quizzes', 'title'),
                SelectFilter::make('question_type')
                    ->options(Question::typeLabels()),
            ])
            ->actions([
                TableEditAction::make(),
                TableDeleteAction::make(),
            ])
            ->bulkActions([
                TableBulkActionGroup::make([
                    TableBulkAction::make('addToQuiz')
                        ->label('Import to Quiz')
                        ->icon('heroicon-o-plus-circle')
                        ->form([
                            Select::make('quiz_id')
                                ->label('Target Quiz')
                                ->options(\App\Models\Quiz::pluck('title', 'id'))
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $quizId = $data['quiz_id'];
                            $quiz = \App\Models\Quiz::find($quizId);

                            if (!$quiz) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Quiz not found')
                                    ->body("The target quiz could not be found.")
                                    ->send();
                                return;
                            }

                            $attachedCount = 0;
                            foreach ($records as $question) {
                                // Check if already attached to prevent unnecessary operations
                                if (!$question->quizzes->contains($quizId)) {
                                    $question->quizzes()->attach($quizId);
                                    $attachedCount++;
                                }
                            }

                            \Filament\Notifications\Notification::make()
                                ->success()
                                ->title('Import successful')
                                ->body("{$attachedCount} questions added to the quiz '{$quiz->title}'.")
                                ->send();
                        }),
                    TableDeleteBulkAction::make(),
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
