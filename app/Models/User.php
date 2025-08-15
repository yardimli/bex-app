<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Foundation\Auth\User as Authenticatable;
	use Illuminate\Notifications\Notifiable;
	use Laravel\Sanctum\HasApiTokens;
	use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
    use Illuminate\Support\Facades\Storage;

	use App\Models\ChatHeader;
	use App\Models\ActionItem;

	class User extends Authenticatable // <-- CHANGE TO THIS
	{
		use HasApiTokens, HasFactory, Notifiable;

		// Adapt traits as needed

		/**
		 * The attributes that are mass assignable.
		 *
		 * @var array<int, string>
		 */
		protected $fillable = [
			'name',
			'email',
			'password',
            'avatar',
		];

		/**
		 * The attributes that should be hidden for serialization.
		 *
		 * @var array<int, string>
		 */
		protected $hidden = [
			'password',
			'remember_token',
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'email_verified_at' => 'datetime',
			'password' => 'hashed', // Use 'hashed' for Laravel 10+
		];

        public function getAvatarUrlAttribute()
        {
            if ($this->avatar && Storage::disk('public')->exists($this->avatar)) {
                return Storage::disk('public')->url($this->avatar);
            }
            // Return a default avatar from a placeholder service
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&background=random';
        }

		public function chatHeaders()
		{
			return $this->hasMany(ChatHeader::class)->orderBy('updated_at', 'desc'); // Order by most recently updated
		}

		public function actionItems() : HasMany
		{
			return $this->hasMany(ActionItem::class)->orderBy('created_at', 'asc'); // Or order as you prefer
		}

		public function notes(): HasMany
		{
			return $this->hasMany(Note::class)->orderBy('updated_at', 'desc'); // Order by most recently updated
		}

        public function teams()
        {
            return $this->belongsToMany(Team::class, 'team_members')->withTimestamps();
        }

        public function teamMemberships()
        {
            return $this->hasMany(TeamMember::class);
        }

        public function sentMessages()
        {
            return $this->hasMany(Message::class, 'sender_id');
        }

        public function receivedMessages()
        {
            return $this->hasMany(MessageRecipient::class, 'recipient_id');
        }

        public function files(): HasMany
        {
            return $this->hasMany(File::class);
        }

        public function createdGroupChats(): HasMany
        {
            return $this->hasMany(GroupChatHeader::class, 'creator_id')->orderBy('updated_at', 'desc');
        }

        public function groupChats(): BelongsToMany
        {
            return $this->belongsToMany(GroupChatHeader::class, 'group_chat_header_user')->orderBy('updated_at', 'desc');
        }

        public function favoriteFiles(): BelongsToMany
        {
            return $this->belongsToMany(File::class, 'file_favorites');
        }


    }
