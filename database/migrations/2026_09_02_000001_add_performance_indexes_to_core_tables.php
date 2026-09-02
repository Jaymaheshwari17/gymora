<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for ultra-fast database indexing.
     */
    public function up(): void
    {
        // 1. Members Performance Indexes
        Schema::table('members', function (Blueprint $table) {
            $table->index(['gym_id', 'status'], 'idx_members_gym_status');
            $table->index(['gym_id', 'created_at'], 'idx_members_gym_created');
            $table->index(['gym_id', 'joining_date'], 'idx_members_gym_joining');
        });

        // 2. Payments Performance Indexes
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['gym_id', 'created_at'], 'idx_payments_gym_created');
            $table->index(['gym_id', 'member_id'], 'idx_payments_gym_member');
        });

        // 3. Attendance Performance Indexes (Essential for instant daily dashboard stats)
        Schema::table('attendance', function (Blueprint $table) {
            $table->index(['gym_id', 'date', 'status'], 'idx_attendance_gym_date_status');
            $table->index(['gym_id', 'date'], 'idx_attendance_gym_date');
        });

        // 4. Expenses Performance Indexes
        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['gym_id', 'expense_date'], 'idx_expenses_gym_expense_date');
            $table->index(['gym_id', 'created_at'], 'idx_expenses_gym_created');
        });

        // 5. Users Role Index
        Schema::table('users', function (Blueprint $table) {
            $table->index(['gym_id', 'role'], 'idx_users_gym_role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropIndex('idx_members_gym_status');
            $table->dropIndex('idx_members_gym_created');
            $table->dropIndex('idx_members_gym_joining');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_gym_created');
            $table->dropIndex('idx_payments_gym_member');
        });

        Schema::table('attendance', function (Blueprint $table) {
            $table->dropIndex('idx_attendance_gym_date_status');
            $table->dropIndex('idx_attendance_gym_date');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('idx_expenses_gym_expense_date');
            $table->dropIndex('idx_expenses_gym_created');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_gym_role');
        });
    }
};
