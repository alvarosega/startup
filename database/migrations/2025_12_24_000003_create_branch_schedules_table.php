<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void 
    {
        Schema::create('branch_schedules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0 = Domingo, 1 = Lunes, ..., 6 = Sábado
            $table->time('opening_time');
            $table->time('closing_time');
            $table->boolean('is_closed')->default(false);
            $table->timestamps();

            $table->unique(['branch_id', 'day_of_week'], 'idx_branch_day_unique');
        });
    }

    public function down(): void 
    {
        Schema::dropIfExists('branch_schedules');
    }
};