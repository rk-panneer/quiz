<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'email',
        'total_score',
        'started_at',
        'completed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'total_score' => 'integer',
        'email' => 'encrypted',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AttemptAnswer::class, 'attempt_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('completed_at');
    }

    /**
     * Anonymize PII fields on this attempt (IP and user-agent) to comply with retention policies.
     */
    public function anonymizePII(): void
    {
        $this->ip_address = null;
        $this->user_agent = null;
        $this->save();
    }
}
