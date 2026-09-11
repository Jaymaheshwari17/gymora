<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds plan_start_date to members table.
     * - joining_date = original gym joining date (NEVER changes)
     * - plan_start_date = start date of current active plan (updates on renew/upgrade)
     */
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->date('plan_start_date')->nullable()->after('joining_date');
        });

        // Backfill: set plan_start_date = joining_date for all existing members
        DB::statement('UPDATE members SET plan_start_date = joining_date WHERE plan_start_date IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn('plan_start_date');
        });
    }
};
