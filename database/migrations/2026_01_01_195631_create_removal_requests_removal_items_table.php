<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('removal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique(); 
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('admin_id')->constrained('admins');
            $table->foreignUuid('approved_by')->nullable()->constrained('admins');
            
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->enum('reason', ['expiration', 'damage', 'theft', 'internal_use', 'admin_error']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('removal_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('removal_request_id')->constrained()->onDelete('cascade');
            $table->foreignUuid('inventory_lot_id')->constrained('inventory_lots');
            
            $table->decimal('quantity', 12, 3); 
            $table->decimal('unit_cost', 12, 2); 
            
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('removal_items');
        Schema::dropIfExists('removal_requests');
    }
};