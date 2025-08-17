<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'owner_id',
        'avatar',
    ];

    protected $appends = ['avatar_url'];
    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
            return Storage::disk('public')->url($this->avatar);
        }
        // Return a default avatar from a placeholder service with the team's name
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random&color=fff';
    }
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'team_members')->withTimestamps();
    }

    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function groupChats()
    {
        return $this->hasMany(GroupChatHeader::class)->orderBy('updated_at', 'desc');
    }

    public function sharedFiles(): BelongsToMany
    {
        return $this->belongsToMany(File::class, 'file_teams')
            ->withPivot('shared_by', 'shared_at')
            ->withTimestamps()
            ->withPivot('created_at'); // Keep this if you want to order by it
    }


}
