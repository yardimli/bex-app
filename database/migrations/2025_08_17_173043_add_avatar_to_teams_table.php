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
        Schema::table('teams', function (Blueprint $table) {
            // Add the new 'avatar' column.
            // It's a string to store the file path.
            // It's nullable because existing teams won't have an avatar.
            // 'after('description')' is optional but keeps the table organized.
            $table->string('avatar')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            // This allows you to rollback the migration if needed.
            $table->dropColumn('avatar');
        });
    }
};
