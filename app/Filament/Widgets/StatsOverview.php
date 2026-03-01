<?php

namespace App\Filament\Widgets;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Quizzes', Quiz::count())
                ->description('All quizzes')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('success'),
            Stat::make('Total Questions', Question::count())
                ->description('Across all quizzes')
                ->descriptionIcon('heroicon-m-question-mark-circle'),
            Stat::make('Total Attempts', QuizAttempt::count())
                ->description('User participations')
                ->descriptionIcon('heroicon-m-user-group')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('primary'),
        ];
    }
}
