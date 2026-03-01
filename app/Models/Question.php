<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    /**
     * Supported question types – defined here for reference.
     * The DB column is a string, not enum, to allow future types.
     */
    const TYPE_MCQ_SINGLE = 'mcq_single';
    const TYPE_MCQ_MULTIPLE = 'mcq_multiple';
    const TYPE_NUMBER_RANGE = 'number_range';
    const TYPE_TEXT_KEYWORDS = 'text_keywords';
    const TYPE_BOOLEAN = 'boolean';

    /**
     * Human-readable labels for admin panel selects.
     */
    public static function typeLabels(): array
    {
        return [
            self::TYPE_MCQ_SINGLE => 'Multiple Choice (Single Answer)',
            self::TYPE_MCQ_MULTIPLE => 'Multiple Choice (Multiple Answers)',
            self::TYPE_NUMBER_RANGE => 'Number Range',
            self::TYPE_TEXT_KEYWORDS => 'Text / Keywords',
            self::TYPE_BOOLEAN => 'Boolean (Yes / No)',
        ];
    }

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function numberRanges(): HasMany
    {
        return $this->hasMany(QuestionNumberRange::class);
    }

    public function keywords(): HasMany
    {
        return $this->hasMany(QuestionKeyword::class);
    }

    public function attemptAnswers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function usesOptions(): bool
    {
        return in_array($this->question_type, [
            self::TYPE_MCQ_SINGLE,
            self::TYPE_MCQ_MULTIPLE,
            self::TYPE_BOOLEAN,
        ]);
    }

    public function usesNumberRanges(): bool
    {
        return $this->question_type === self::TYPE_NUMBER_RANGE;
    }

    public function usesKeywords(): bool
    {
        return $this->question_type === self::TYPE_TEXT_KEYWORDS;
    }
}
