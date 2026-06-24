<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->string('email');
            $table->string('token');
            $table->timestamp('expires_at')->index();
            $table->timestamp('created_at')->nullable();

            // Llave primaria compuesta: Máximo rendimiento de indexación física
            $table->primary(['email', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};