<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\Relations\HasMany;
	use Illuminate\Support\Facades\DB;

	class ChatHeader extends Model
	{
		use HasFactory;

		protected $fillable = [
			'user_id',
			'title',
		];

		/**
		 * Get the user that owns the chat header.
		 */
		public function user(): BelongsTo
		{
			return $this->belongsTo(User::class);
		}

		/**
		 * Get the messages for the chat header.
		 */
		public function messages(): HasMany
		{
			return $this->hasMany(ChatMessage::class)->orderBy('created_at', 'asc');
		}

		protected static function booted()
		{
			static::deleting(function ($chatHeader) {
				// Manually delete related messages if cascade isn't set at DB level
				// Wrap in transaction for safety, although the controller method already uses one
				DB::transaction(function () use ($chatHeader) {
					$chatHeader->messages()->delete();
				});
			});
		}
	}
