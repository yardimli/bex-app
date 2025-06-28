<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'team_id',
        'subject',
        'body',
    ];

    protected $appends = ['read_count', 'total_recipients'];
    /**
     * The user who sent the message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * The team this message belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The recipients of the message.
     */
    public function recipients()
    {
        return $this->hasMany(MessageRecipient::class);
    }

    public function getReadCountAttribute()
    {
        if (!$this->relationLoaded('recipients')) {
            $this->load('recipients');
        }
        return $this->recipients->whereNotNull('read_at')->count();
    }

    public function getTotalRecipientsAttribute()
    {
        if (!$this->relationLoaded('recipients')) {
            $this->load('recipients');
        }
        return $this->recipients->count();
    }
}
