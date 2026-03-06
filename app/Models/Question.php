<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    const TYPE_IMAGE_ANSWER = 'image_answer';

    const MEDIA_TYPE_NONE = 'none';
    const MEDIA_TYPE_IMAGE = 'image';
    const MEDIA_TYPE_VIDEO = 'video';
    const MEDIA_TYPE_AUDIO = 'audio';


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
            self::TYPE_BOOLEAN => 'Boolean (Yes/No)',
            self::TYPE_IMAGE_ANSWER => 'Image Response',
        ];
    }

    protected $fillable = [
        'question_text',
        'normalized_question_text',
        'question_type',
        'media_type',
        'media_url',
        'media_path',
        'order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function quizzes(): BelongsToMany
    {
        return $this->belongsToMany(Quiz::class, 'question_quiz')
            ->withPivot('order')
            ->withTimestamps();
    }

    public function embedding(): HasOne
    {
        return $this->hasOne(QuestionEmbedding::class);
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
            self::TYPE_IMAGE_ANSWER,
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

    protected static function booted()
    {
        static::saving(function ($question) {
            if ($question->isDirty('question_text')) {
                $question->normalized_question_text = self::normalizeText($question->question_text);
            }
        });

        static::saved(function ($question) {
            if ($question->wasChanged('question_text') || $question->wasRecentlyCreated) {
                try {
                    app(\App\Services\QuestionService::class)->syncEmbedding($question);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning("Embedding sync failed for question #{$question->id}: " . $e->getMessage());
                }
            }
        });
    }

    /**
     * Get the formatted embed URL for video media.
     * Automatically transforms standard YouTube links to embed links.
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->media_url) {
            return null;
        }

        if ($this->media_type === self::MEDIA_TYPE_VIDEO) {
            $pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/i';
            if (preg_match($pattern, $this->media_url, $match)) {
                return "https://www.youtube.com/embed/" . $match[1];
            }
        }

        return $this->media_url;
    }

    public static function normalizeText(string $text): string
    {
        $text = strtolower($text);
        $text = trim($text);
        $text = preg_replace('/[[:punct:]]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }
}
