<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            // Disimpan sebagai string, bukan integer: chat_id grup bernilai negatif
            // dan supergroup berawalan -100 (mis. -1001234567890), sehingga cast ke
            // integer berisiko merusak nilainya.
            $table->string('telegram_chat_id', 32)->nullable()->after('whatsapp_number');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('telegram_chat_id');
        });
    }
};
