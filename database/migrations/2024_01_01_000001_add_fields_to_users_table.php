<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Agregar campos adicionales a la tabla users existente
            $table->boolean('is_active')->default(true)->after('password');
            $table->softDeletes(); // Sin after() para evitar problemas con columnas inexistentes
            
            // Campos de auditoría
            $table->unsignedBigInteger('user_add')->nullable()->after('deleted_at');
            $table->unsignedBigInteger('user_edit')->nullable()->after('user_add');
            $table->unsignedBigInteger('user_deleted')->nullable()->after('user_edit');
            
            // Agregar claves foráneas para auditoría
            $table->foreign('user_add')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_edit')->references('id')->on('users')->onDelete('set null');
            $table->foreign('user_deleted')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar claves foráneas primero
            $table->dropForeign(['user_add']);
            $table->dropForeign(['user_edit']);
            $table->dropForeign(['user_deleted']);
            
            // Eliminar columnas
            $table->dropColumn([
                'is_active',
                'deleted_at',
                'user_add',
                'user_edit',
                'user_deleted'
            ]);
        });
    }
}; 