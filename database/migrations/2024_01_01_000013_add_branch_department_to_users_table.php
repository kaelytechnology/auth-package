<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add branch and department foreign keys
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_deleted');
            $table->unsignedBigInteger('department_id')->nullable()->after('branch_id');
            
            // Add foreign key constraints
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['department_id']);
            
            // Drop columns
            $table->dropColumn(['branch_id', 'department_id']);
        });
    }
}; 