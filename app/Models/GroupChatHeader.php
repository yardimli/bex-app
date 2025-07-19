<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class GroupChatHeader extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'creator_id',
        'title',
        'llm_model',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(GroupChatMessage::class)->orderBy('created_at', 'asc');
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_chat_header_user');
    }

    protected static function booted()
    {
        static::deleting(function ($groupChatHeader) {
            DB::transaction(function () use ($groupChatHeader) {
                $groupChatHeader->messages()->delete();
            });
        });
    }
}
