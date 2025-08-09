<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LlmUsageLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'team_id',
        'llm_id',
        'prompt_tokens',
        'completion_tokens',
        'prompt_cost',
        'completion_cost',
    ];

    protected $casts = [
        'prompt_cost' => 'float',
        'completion_cost' => 'float',
        'total_cost' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function llm(): BelongsTo
    {
        return $this->belongsTo(Llm::class, 'llm_id');
    }
}
