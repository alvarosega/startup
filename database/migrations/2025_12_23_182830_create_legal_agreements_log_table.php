<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_agreements_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('document_version')->default('1.0');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->timestamp('accepted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_agreements_log');
    }
};