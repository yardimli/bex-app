<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class File extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'original_filename',
        'path',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function sharedWithTeams(): BelongsToMany
    {
        // Use your table name 'file_teams' and include the extra pivot columns
        return $this->belongsToMany(Team::class, 'file_teams')
            ->withPivot('shared_by', 'shared_at')
            ->withTimestamps();
    }
}
