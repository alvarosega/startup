<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Libro Mayor de Puntos (Loyalty Ledger)
        Schema::create('loyalty_ledger', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount'); // Positivo o Negativo
            $table->string('type'); // purchase, expiration, bonus
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        // 2. Verificaciones de Identidad (KYC)
        Schema::create('user_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Solicitante
            $table->foreignId('reviewer_id')->nullable()->constrained('users'); // Auditor Admin
            $table->string('front_ci_path')->nullable();
            $table->string('back_ci_path')->nullable();
            $table->string('selfie_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            // Relación con tabla de motivos estandarizados
            $table->foreignId('rejection_reason_id')->nullable()->constrained('rejection_reasons');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_verifications');
        Schema::dropIfExists('loyalty_ledger');
    }
};