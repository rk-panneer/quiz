<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'status',
        'max_attempts_per_user',
        'time_limit_minutes',
        'created_by',
    ];

    protected $casts = [
        'status' => 'string',
        'max_attempts_per_user' => 'integer',
        'time_limit_minutes' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'question_quiz')
            ->withPivot('order')
            ->withTimestamps()
            ->orderBy('question_quiz.order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Auto-generate a unique slug from the title.
     */
    public static function generateSlug(string $title): string
    {
        $slug = Str::slug($title);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Create a quiz with a slug, retrying on unique constraint violations to avoid TOCTOU races.
     *
     * @param array $attributes
     * @param int $maxAttempts
     * @return static
     */
    public static function createWithUniqueSlug(array $attributes, int $maxAttempts = 5)
    {
        $attempt = 0;

        do {
            $attempt++;

            DB::beginTransaction();
            try {
                $attributes['slug'] = static::generateSlug($attributes['title']);
                $quiz = static::create($attributes);
                DB::commit();

                return $quiz;
            } catch (QueryException $e) {
                DB::rollBack();

                $isUniqueViolation = str_contains($e->getMessage(), 'UNIQUE') || str_contains($e->getMessage(), 'unique');
                if ($isUniqueViolation && $attempt < $maxAttempts) {
                    continue;
                }

                throw $e;
            }
        } while ($attempt < $maxAttempts);

        throw new \RuntimeException('Unable to create unique slug after ' . $maxAttempts . ' attempts');
    }
}
