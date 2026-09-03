<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            if (!Schema::hasColumn('gyms', 'contact_number')) {
                $table->string('contact_number', 20)->nullable()->after('logo');
            }
            if (!Schema::hasColumn('gyms', 'address')) {
                $table->text('address')->nullable()->after('contact_number');
            }
            if (!Schema::hasColumn('gyms', 'gst_number')) {
                $table->string('gst_number', 50)->nullable()->after('address');
            }
            if (!Schema::hasColumn('gyms', 'instagram_link')) {
                $table->string('instagram_link', 255)->nullable()->after('gst_number');
            }
            if (!Schema::hasColumn('gyms', 'facebook_link')) {
                $table->string('facebook_link', 255)->nullable()->after('instagram_link');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gyms', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['contact_number', 'address', 'gst_number', 'instagram_link', 'facebook_link'] as $col) {
                if (Schema::hasColumn('gyms', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
