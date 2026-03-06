<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LLMUsageLog extends Model
{
    use HasFactory;

    protected $table = 'llm_usage_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'operation',
        'input_tokens',
        'output_tokens',
        'total_tokens',
        'cost',
        'provider',
        'created_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function log(string $operation, array $usage, ?string $provider = null): self
    {
        return self::create([
            'user_id' => auth()->id(),
            'operation' => $operation,
            'input_tokens' => $usage['input_tokens'] ?? 0,
            'output_tokens' => $usage['output_tokens'] ?? 0,
            'total_tokens' => $usage['total_tokens'] ?? 0,
            'cost' => $usage['cost'] ?? 0,
            'provider' => $provider ?? config('llm.default'),
            'created_at' => now(),
        ]);
    }
}
