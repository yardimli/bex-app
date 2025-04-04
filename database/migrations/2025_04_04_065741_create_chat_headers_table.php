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
			Schema::create('chat_headers', function (Blueprint $table) {
				$table->id();
				$table->foreignId('user_id')->constrained()->onDelete('cascade'); // Link to user
				$table->string('title')->default('New Chat'); // Default title
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 */
		public function down(): void
		{
			Schema::dropIfExists('chat_headers');
		}
	};
