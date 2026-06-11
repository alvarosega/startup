<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches');
            $table->foreignUuid('provider_id')->constrained('providers');
            $table->foreignUuid('admin_id')->constrained('admins');
            $table->string('document_number', 32)->unique();
            $table->date('purchase_date')->index();
            $table->enum('payment_type', ['CASH', 'CREDIT'])->default('CASH');
            $table->decimal('total_amount', 14, 2)->default(0); 
            $table->string('status')->default('COMPLETED');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void {
        Schema::dropIfExists('purchases');
    }
};