<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;

	class ActionItem extends Model
	{
		use HasFactory;

		protected $fillable = [
			'user_id',
			'content',
			'is_done',
			'due_date', // Add if you want to set due dates later
		];

		/**
		 * The attributes that should be cast.
		 *
		 * @var array<string, string>
		 */
		protected $casts = [
			'is_done' => 'boolean',
			'due_date' => 'date', // Cast if using date type
		];

		/**
		 * Get the user that owns the action item.
		 */
		public function user(): BelongsTo
		{
			return $this->belongsTo(User::class);
		}
	}
