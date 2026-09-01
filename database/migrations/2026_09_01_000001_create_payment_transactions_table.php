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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
            $table->foreignId('gym_id')->constrained('gyms')->onDelete('cascade');
            
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_mode')->default('cash'); // cash, upi, card, net_banking, cheque, other
            $table->string('notes')->nullable();
            
            $table->timestamps();

            // High performance compound indexes for fast filtering and reports
            $table->index(['gym_id', 'payment_date']);
            $table->index(['payment_id', 'payment_date']);
            $table->index(['member_id', 'gym_id']);
        });

        // Add performance indexes on payments table if missing
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['gym_id', 'due_amount']);
            $table->index(['gym_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gym_id', 'due_amount']);
            $table->dropIndex(['gym_id', 'status']);
        });
    }
};
