<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void 
    {
        Schema::create('branch_schedule_exceptions', function (Blueprint $table) {
            $table->uuid('id')->primary();$table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('date');$table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();$table->boolean('is_closed')->default(false);
            $table->string('reason', 255)->nullable();$table->timestamps();

            $table->unique(['branch_id', 'date'], 'idx_branch_exception_date_unique');
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('branch_schedule_exceptions');
    }
};