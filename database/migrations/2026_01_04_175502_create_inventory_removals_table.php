<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_removals', function (Blueprint $table) {
            $table->char('id', 16)->charset('binary')->primary(); // <--- Binario

            $table->char('branch_id', 16)->charset('binary');
            $table->foreign('branch_id')->references('id')->on('branches');

            $table->char('sku_id', 16)->charset('binary');
            $table->foreign('sku_id')->references('id')->on('skus');

            $table->char('admin_id', 16)->charset('binary');
            $table->foreign('admin_id')->references('id')->on('admins');

            $table->char('approved_by', 16)->charset('binary')->nullable();
            $table->foreign('approved_by')->references('id')->on('admins');
                
            $table->integer('cantidad');
            $table->string('motivo'); 
            $table->text('observaciones')->nullable();
            $table->string('evidencia_url')->nullable(); 
            $table->string('estado')->default('pendiente');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_removals');
    }
};