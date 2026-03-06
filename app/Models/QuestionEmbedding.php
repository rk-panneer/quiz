<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuestionEmbedding extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }
}
