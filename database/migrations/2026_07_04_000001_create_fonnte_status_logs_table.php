<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fonnte_status_logs', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_connected')->default(false);
            $table->string('status_message')->nullable();
            $table->timestamp('last_email_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fonnte_status_logs');
    }
};
