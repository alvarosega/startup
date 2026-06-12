<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Niveles (Gamificación Base)
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->integer('min_points')->default(0);
            $table->string('color_hex', 7)->default('#000000');
            $table->json('benefits_json')->nullable();
            $table->timestamps();
        });
    
        // 2. Motivos de Rechazo
        Schema::create('rejection_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('reason_text');
            $table->timestamps();
        });

        // 3, 4 y 5 ELIMINADOS. Dejamos que Spatie maneje roles y permisos.
    }

    public function down(): void
    {
        Schema::dropIfExists('rejection_reasons');
        Schema::dropIfExists('levels');
    }
};