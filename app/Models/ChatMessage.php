<?php

	namespace App\Models;

	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;
	use Illuminate\Database\Eloquent\Relations\BelongsTo;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;

	class ChatMessage extends Model
	{
		use HasFactory;

		protected $fillable = [
			'chat_header_id',
			'role',
			'content',
			'prompt_tokens',
			'completion_tokens',
		];

		/**
		 * Get the chat header that the message belongs to.
		 */
		public function chatHeader(): BelongsTo
		{
			return $this->belongsTo(ChatHeader::class);
		}

        public function files(): BelongsToMany
        {
            return $this->belongsToMany(File::class, 'chat_message_file');
        }
	}
