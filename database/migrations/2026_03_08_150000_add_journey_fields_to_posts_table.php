<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // category enumに'journey'を追加
        DB::statement("ALTER TABLE posts MODIFY COLUMN category ENUM('tech', 'life', 'idea', 'journey')");

        Schema::table('posts', function (Blueprint $table) {
            $table->foreignId('journey_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('author_name')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropForeign(['journey_id']);
            $table->dropColumn(['journey_id', 'author_name']);
        });

        DB::statement("ALTER TABLE posts MODIFY COLUMN category ENUM('tech', 'life', 'idea')");
    }
};
