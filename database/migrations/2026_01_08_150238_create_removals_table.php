<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Cabecera (La Solicitud)
        Schema::create('removal_requests', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); 
            
            // Branch es ID numérico (CORRECTO: foreignId)
            $table->foreignId('branch_id')->constrained('branches');
            
            // CORRECCIÓN AQUÍ: User es UUID (Debe ser foreignUuid)
            $table->foreignUuid('user_id')->constrained('users'); 
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            
            // Approved_by ya lo tenías bien como UUID
            $table->foreignUuid('approved_by')->nullable()->constrained('users'); 
            
            $table->timestamp('approved_at')->nullable();

            $table->enum('reason', ['expiration', 'damage', 'theft', 'internal_use', 'admin_error']);
            
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Detalle
        Schema::create('removal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('removal_request_id')->constrained('removal_requests')->onDelete('cascade');
            
            // Inventory Lots usa ID numérico, así que foreignId es correcto aquí
            $table->foreignId('inventory_lot_id')->constrained('inventory_lots');
            
            $table->decimal('quantity', 10, 2); 
            $table->decimal('unit_cost', 10, 2); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('removal_items');
        Schema::dropIfExists('removal_requests');
    }
};