<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('removal_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 32)->unique();
            $table->foreignUuid('branch_id')->constrained('branches')->restrictOnDelete();
            $table->foreignUuid('admin_id')->constrained('admins')->restrictOnDelete();
            $table->foreignUuid('approved_by_id')->constrained('admins')->restrictOnDelete();
            
            $table->string('status')->default('approved')->index(); 
            $table->timestamp('approved_at')->useCurrent();
            $table->string('reason')->index(); 
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('removal_requests');
    }
};