<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('providers', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(UUID())'));            $table->string('company_name'); 
            $table->string('commercial_name')->nullable();
            $table->string('tax_id')->unique(); 
            $table->string('internal_code')->nullable()->unique();
            $table->string('contact_name')->nullable();
            $table->string('email_orders')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->integer('lead_time_days')->default(1); 
            $table->decimal('min_order_value', 12, 2)->default(0); 
            $table->integer('credit_days')->default(0); 
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unsignedInteger('version')->default(0);
            $table->softDeletes();
            $table->index(['is_active', 'id']);
        });
    }
    public function down(): void { Schema::dropIfExists('providers'); }
};