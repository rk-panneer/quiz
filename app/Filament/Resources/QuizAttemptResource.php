<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuizAttemptResource\Pages;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\QuestionOption;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use League\Csv\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

class QuizAttemptResource extends Resource
{
    protected static ?string $model = QuizAttempt::class;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Quiz Attempts';
    protected static ?int $navigationSort = 2;

    public static function infolist(Schema $infolist): Schema
    {
        return $infolist
            ->schema([
                Section::make('Attempt Information')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('quiz_title')
                                    ->label('Quiz Title')
                                    ->content(fn($record) => $record->quiz->title),
                                Placeholder::make('email')
                                    ->label('User Email')
                                    ->content(fn($record) => $record->email),
                                Placeholder::make('total_score')
                                    ->label('Total Score')
                                    ->content(fn($record) => ($record->total_score ?? 0) . ' points'),
                            ]),
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('started_at')
                                    ->label('Started')
                                    ->content(fn($record) => $record->started_at?->toDateTimeString() ?? 'In Progress'),
                                Placeholder::make('completed_at')
                                    ->label('Completed')
                                    ->content(fn($record) => $record->completed_at?->toDateTimeString() ?? 'In Progress'),
                                Placeholder::make('ip_address')
                                    ->label('IP Address')
                                    ->content(fn($record) => $record->ip_address),
                            ]),
                    ]),

                Section::make('User Answers')
                    ->description('Overview of latest attempt data.')
                    ->schema(function ($record) {
                        $items = [];
                        $allIds = [];
                        foreach ($record->answers as $answer) {
                            if (in_array($answer->question->question_type, [Question::TYPE_MCQ_SINGLE, Question::TYPE_MCQ_MULTIPLE, Question::TYPE_BOOLEAN])) {
                                $val = $answer->answer;
                                $ids = is_array($val) ? $val : [$val];
                                foreach ($ids as $id) {
                                    if (is_numeric($id)) {
                                        $allIds[] = $id;
                                    }
                                }
                            }
                        }

                        $optionLabels = [];
                        if (!empty($allIds)) {
                            $optionLabels = QuestionOption::whereIn('id', array_values(array_unique($allIds)))->pluck('option_text', 'id')->toArray();
                        }

                        foreach ($record->answers as $index => $answer) {
                            $items[] = Section::make("Question " . ($index + 1))
                                ->compact()
                                ->schema([
                                    Placeholder::make("q_{$index}")
                                        ->label('Question')
                                        ->content($answer->question->question_text),
                                    Grid::make(2)
                                        ->schema([
                                            Placeholder::make("a_{$index}")
                                                ->label('Answer Given')
                                                ->content(function () use ($answer, $optionLabels) {
                                                    $val = $answer->answer;
                                                    if ($val === null || $val === '' || (is_array($val) && empty($val))) {
                                                        return 'N/A';
                                                    }

                                                    if (in_array($answer->question->question_type, [Question::TYPE_MCQ_SINGLE, Question::TYPE_MCQ_MULTIPLE, Question::TYPE_BOOLEAN])) {
                                                        $ids = is_array($val) ? $val : [$val];
                                                        $ids = array_filter($ids, fn($id) => is_numeric($id));

                                                        if (empty($ids))
                                                            return is_array($val) ? implode(', ', $val) : $val;

                                                        $labels = [];
                                                        foreach ($ids as $id) {
                                                            if (isset($optionLabels[$id])) {
                                                                $labels[] = $optionLabels[$id];
                                                            }
                                                        }

                                                        return !empty($labels) ? implode(', ', $labels) : (is_array($val) ? implode(', ', $val) : $val);
                                                    }

                                                    return is_array($val) ? implode(', ', $val) : $val;
                                                }),
                                            Placeholder::make("s_{$index}")
                                                ->label('Score Earned')
                                                ->content(fn() => ($answer->score_awarded ?? 0) . ' points'),
                                        ])
                                ]);
                        }
                        return $items;
                    })
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('quiz.title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_score')
                    ->sortable()
                    ->label('Score'),
                TextColumn::make('started_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('completed_at')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('In Progress'),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('quiz')
                    ->relationship('quiz', 'title'),
                Filter::make('completed')
                    ->query(fn($query) => $query->whereNotNull('completed_at')),
            ])
            ->actions([
                ViewAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkAction::make('export_csv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn($records) => static::exportCsv($records->load('quiz'))),
                DeleteBulkAction::make(),
            ])
            ->headerActions([
                Action::make('export_all')
                    ->label('Export All')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn() => static::exportCsv(QuizAttempt::query())),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuizAttempts::route('/'),
        ];
    }

    /**
     * CSV Export Logic using league/csv
     */
    protected static function exportCsv($records): StreamedResponse
    {
        Gate::authorize('export', QuizAttempt::class);

        $filename = 'quiz_attempts_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $user = auth()->user();
        Log::info('quiz_attempts_export', [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'timestamp' => now()->toDateTimeString(),
            'scope' => is_string($records) ? $records : (is_iterable($records) ? 'iterable' : 'unknown'),
            'filename' => $filename,
        ]);

        return response()->streamDownload(function () use ($records) {
            $csv = Writer::createFromFileObject(new \SplTempFileObject());

            $includeIp = config('quiz.include_ip_in_exports', false) && Gate::allows('export_ip', QuizAttempt::class);

            $headers = ['Quiz', 'Email', 'Score', 'Started At', 'Completed At'];
            if ($includeIp) {
                $headers[] = 'IP Address';
            }
            $csv->insertOne($headers);

            $writeRow = function ($record) use ($csv, $includeIp) {
                $started = $record->started_at?->toDateTimeString() ?? 'N/A';
                $completed = $record->completed_at?->toDateTimeString() ?? 'N/A';

                $quizTitle = $record->quiz->title ?? ($record->quiz_title ?? 'N/A');

                $row = [
                    $quizTitle,
                    $record->email,
                    $record->total_score ?? 'N/A',
                    $started,
                    $completed,
                ];

                if ($includeIp) {
                    $ip = $record->ip_address ?? null;
                    $masked = null;
                    if ($ip) {
                        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                            $parts = explode('.', $ip);
                            if (count($parts) === 4) {
                                $parts[3] = 'xxx';
                                $masked = implode('.', $parts);
                            } else {
                                $masked = substr($ip, 0, 8) . '...';
                            }
                        } else {
                            $masked = substr($ip, 0, 8) . '...';
                        }
                    }
                    $row[] = $masked;
                }

                $csv->insertOne($row);
            };

            if ($records instanceof EloquentBuilder || (is_object($records) && method_exists($records, 'chunkById'))) {
                $records->with('quiz')->chunkById(500, function ($chunk) use ($writeRow) {
                    foreach ($chunk as $r) {
                        $writeRow($r);
                    }
                });

            } elseif (is_iterable($records)) {
                foreach ($records as $r) {
                    if (isset($r->quiz_id) && !$r->relationLoaded('quiz')) {
                        $r->setRelation('quiz', $r->quiz ?? null);
                    }
                    $writeRow($r);
                }
            }

            echo $csv->toString();
        }, $filename);
    }
}
