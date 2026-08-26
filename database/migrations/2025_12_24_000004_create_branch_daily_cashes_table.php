<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void 
    {
        Schema::create('branch_daily_cashes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->foreignUuid('opened_by_admin_id')->constrained('admins');
            $table->foreignUuid('closed_by_admin_id')->nullable()->constrained('admins');
            
            $table->date('date');
            $table->decimal('opening_amount', 10, 2)->default(0.00);
            $table->decimal('expected_amount', 10, 2)->default(0.00);
            $table->decimal('actual_amount', 10, 2)->nullable();
            $table->decimal('difference', 10, 2)->nullable();
            
            $table->string('status', 20)->default('open')->index(); // open, closed
            $table->text('closing_notes')->nullable();
            
            $table->timestamp('opened_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            // Garantiza exactamente una caja diaria por fecha y por sucursal
            $table->unique(['branch_id', 'date'], 'idx_branch_daily_cash_unique');
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('branch_daily_cashes');
    }
};