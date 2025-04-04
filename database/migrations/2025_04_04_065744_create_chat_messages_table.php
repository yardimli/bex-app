<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration
	{
		/**
		 * Run the migrations.
		 */
		public function up(): void
		{
			Schema::create('chat_messages', function (Blueprint $table) {
				$table->id();
				$table->foreignId('chat_header_id')->constrained()->onDelete('cascade'); // Link to header
				$table->enum('role', ['user', 'assistant']); // Who sent it?
				$table->text('content'); // The message text
				$table->unsignedInteger('prompt_tokens')->nullable(); // Optional usage
				$table->unsignedInteger('completion_tokens')->nullable(); // Optional usage
				$table->timestamps();

				// Index for faster message retrieval per chat
				$table->index('chat_header_id');
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('chat_messages');
		}
	};
