<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageRecipient extends Model
{
    use HasFactory;

    protected $table = 'message_recipients';

    public $timestamps = false; // We only use created_at which is handled by default, and read_at

    protected $fillable = [
        'message_id',
        'recipient_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    /**
     * The message this record belongs to.
     */
    public function message()
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * The user who is the recipient.
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
