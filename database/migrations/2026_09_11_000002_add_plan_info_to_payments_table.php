<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add plan_id and plan_duration_months to payments table.
     * This allows each payment invoice to show the correct plan
     * even after the member renews/upgrades to a different plan.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('gym_id')->constrained('plans')->onDelete('set null');
            $table->unsignedTinyInteger('plan_duration_months')->nullable()->after('plan_id');
            $table->string('plan_name', 100)->nullable()->after('plan_duration_months');
        });

        // Backfill: copy current plan info from member into existing payments
        DB::statement('
            UPDATE payments p
            JOIN members m ON m.id = p.member_id
            JOIN plans pl ON pl.id = m.plan_id
            SET p.plan_id = m.plan_id,
                p.plan_duration_months = pl.duration_months,
                p.plan_name = pl.plan_group_name
            WHERE p.plan_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
            $table->dropColumn(['plan_id', 'plan_duration_months', 'plan_name']);
        });
    }
};
