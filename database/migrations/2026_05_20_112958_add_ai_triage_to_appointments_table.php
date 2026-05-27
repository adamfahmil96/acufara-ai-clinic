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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('ai_urgency')->nullable()->after('complaint_summary');
            $table->text('ai_recommendation')->nullable()->after('ai_urgency');
            $table->text('ai_notes')->nullable()->after('ai_recommendation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['ai_urgency', 'ai_recommendation', 'ai_notes']);
        });
    }
};
