<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class GroupChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_chat_header_id',
        'user_id', // The user who sent the message. Null for assistant.
        'role',
        'content',
        'prompt_tokens',
        'completion_tokens',
    ];

    protected $with = ['user']; // Eager load the user by default

    public function groupChatHeader(): BelongsTo
    {
        return $this->belongsTo(GroupChatHeader::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function files(): BelongsToMany
    {
        // Assuming a similar pivot table 'group_chat_message_file'
        return $this->belongsToMany(File::class, 'group_chat_message_file');
    }
}
