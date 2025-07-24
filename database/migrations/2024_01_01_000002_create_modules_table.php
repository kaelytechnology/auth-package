<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('order')->default(0);
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('route')->nullable();
            $table->boolean('is_active')->default(true);
            // Campo para árbol de módulos (parent_id)
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            // Campos de auditoría
            $table->unsignedBigInteger('user_add')->nullable();
            $table->unsignedBigInteger('user_edit')->nullable();
            $table->unsignedBigInteger('user_deleted')->nullable();
            
            $table->foreign('user_add')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_edit')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_deleted')->references('id')->on('users')->onDelete('set null');
            // Foreign key para parent_id (puede ser 0 para raíz, o id de otro módulo)
            $table->foreign('parent_id')->references('id')->on('modules')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
}; 