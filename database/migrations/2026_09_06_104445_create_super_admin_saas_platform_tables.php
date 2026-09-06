<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modify users table role column to support superadmin
        try {
            DB::statement("ALTER TABLE users MODIFY COLUMN role VARCHAR(50) NOT NULL DEFAULT 'member'");
        } catch (\Exception $e) {
            // In case DB doesn't support raw ALTER, fallback
        }

        // 2. Add status & subscription columns to gyms table
        Schema::table('gyms', function (Blueprint $table) {
            if (!Schema::hasColumn('gyms', 'status')) {
                $table->enum('status', ['active', 'suspended', 'expired', 'trial'])->default('active')->after('name');
            }
            if (!Schema::hasColumn('gyms', 'subscription_end_date')) {
                $table->date('subscription_end_date')->nullable()->after('status');
            }
        });

        // 3. Create saas_plans table
        if (!Schema::hasTable('saas_plans')) {
            Schema::create('saas_plans', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->string('code', 50)->unique();
                $table->decimal('price', 10, 2)->default(0.00);
                $table->string('billing_cycle', 30)->default('monthly'); // monthly, quarterly, yearly, lifetime
                $table->integer('max_members')->nullable()->default(500);
                $table->text('description')->nullable();
                $table->json('features')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        // 4. Create gym_subscriptions table (SaaS subscriptions collected from gyms)
        if (!Schema::hasTable('gym_subscriptions')) {
            Schema::create('gym_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('gym_id')->constrained('gyms')->onDelete('cascade');
                $table->unsignedBigInteger('saas_plan_id')->nullable();
                $table->string('plan_name', 100);
                $table->decimal('amount_paid', 10, 2)->default(0.00);
                $table->date('start_date');
                $table->date('end_date');
                $table->string('billing_cycle', 30)->default('monthly');
                $table->string('payment_method', 50)->default('UPI'); // UPI, Bank Transfer, Razorpay, Cash
                $table->enum('payment_status', ['paid', 'pending', 'failed'])->default('paid');
                $table->enum('status', ['active', 'expired', 'cancelled', 'trial'])->default('active');
                $table->string('transaction_reference', 100)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 5. Create platform_expenses table (Hosting, Server, SMS, Marketing kharcha)
        if (!Schema::hasTable('platform_expenses')) {
            Schema::create('platform_expenses', function (Blueprint $table) {
                $table->id();
                $table->string('title', 150);
                $table->string('category', 80)->default('Hosting & Cloud'); // Hosting & Cloud, SMS & WhatsApp API, Domain & SSL, Tools & Software, Marketing, Salaries, Maintenance, Other
                $table->decimal('amount', 10, 2);
                $table->date('expense_date');
                $table->string('payment_method', 50)->nullable()->default('UPI'); // UPI, Bank Transfer, Card, Cash
                $table->string('vendor_name', 100)->nullable(); // AWS, Hostinger, Meta, Twilio
                $table->string('receipt_url', 255)->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_expenses');
        Schema::dropIfExists('gym_subscriptions');
        Schema::dropIfExists('saas_plans');

        Schema::table('gyms', function (Blueprint $table) {
            if (Schema::hasColumn('gyms', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('gyms', 'subscription_end_date')) {
                $table->dropColumn('subscription_end_date');
            }
        });
    }
};
