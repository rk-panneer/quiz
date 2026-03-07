<?php

namespace App\Filament\Resources\QuizResource\RelationManagers;

use App\Filament\Resources\QuizAttemptResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;

class AttemptsRelationManager extends RelationManager
{
    protected static string $relationship = 'attempts';

    protected static ?string $recordTitleAttribute = 'email';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->columns([
                TextColumn::make('email')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where('email_hash', hash('sha256', $search));
                    }),
                TextColumn::make('total_score')
                    ->label('Score')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Completed')
                    ->placeholder('In Progress'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->actions([
                Action::make('view_results')
                    ->label('View Details')
                    ->icon('heroicon-o-presentation-chart-bar')
                    ->color('info')
                    ->url(fn($record) => QuizAttemptResource::getUrl('view', ['record' => $record])),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('completed_at', 'desc');
    }
}
