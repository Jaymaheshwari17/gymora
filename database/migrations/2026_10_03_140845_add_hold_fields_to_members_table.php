<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            // Date when plan was put on hold
            $table->date('hold_start_date')->nullable()->after('plan_start_date');
            // Override expiry date (used when plan is resumed after hold — remaining months counted)
            $table->date('plan_end_date')->nullable()->after('hold_start_date');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['hold_start_date', 'plan_end_date']);
        });
    }
};
