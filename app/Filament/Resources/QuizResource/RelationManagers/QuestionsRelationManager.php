<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use App\Models\Question;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\AttachAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $recordTitleAttribute = 'question_text';

    public function form(Schema $form): Schema
    {
        return $form->schema(\App\Filament\Resources\Questions\Schemas\QuestionForm::schema());
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order')->sortable(),
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

                ImageColumn::make('media_path')
                    ->label('Img')
                    ->disk('public')
                    ->circular()
                    ->visibility(fn($record) => $record->media_type === Question::MEDIA_TYPE_IMAGE),

                IconColumn::make('is_required')
                    ->boolean()
                    ->label('Req'),

                TextColumn::make('media_type')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->colors([
                        'gray' => Question::MEDIA_TYPE_NONE,
                        'info' => Question::MEDIA_TYPE_IMAGE,
                        'success' => Question::MEDIA_TYPE_VIDEO,
                        'warning' => Question::MEDIA_TYPE_AUDIO,
                    ]),
            ])
            ->reorderable('order')
            ->defaultSort('order')
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()
                    ->form(fn(AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('order')->numeric()->default(0),
                    ]),
            ])
            ->actions([
                EditAction::make(),
                DetachAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
