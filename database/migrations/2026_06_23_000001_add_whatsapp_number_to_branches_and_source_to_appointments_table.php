<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('whatsapp_number', 20)->nullable()->after('alamat');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('source', ['self_register', 'admin'])->default('admin')->after('ai_notes');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('whatsapp_number');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
