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
        // Add is_platform_admin column to users table if not exists
        if (!Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_platform_admin')->default(false)->after('status')->index();
            });
        }

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->restrictOnDelete();
            $table->string('status', 30)->default('TRIALING')->index(); // TRIALING, ACTIVE, PAST_DUE, CANCELLED, EXPIRED, SUSPENDED
            $table->timestamp('trial_ends_at')->nullable()->index();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable()->index();
            $table->timestamp('cancelled_at')->nullable();
            $table->boolean('cancel_at_period_end')->default(false);
            $table->string('provider', 50)->default('manual')->index(); // manual, stripe, easypaisa, jazzcash, test
            $table->string('provider_subscription_id')->nullable()->index();
            $table->timestamps();

            $table->index(['business_id', 'status']);
        });

        Schema::create('billing_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained('subscriptions')->nullOnDelete();
            $table->string('invoice_number', 50)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 10)->default('PKR');
            $table->string('status', 30)->default('paid')->index(); // paid, pending, failed, refunded
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('provider_invoice_id')->nullable();
            $table->timestamps();
        });

        Schema::create('billing_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 50)->index();
            $table->string('event_id', 100)->index();
            $table->string('event_type', 100)->index();
            $table->string('payload_hash', 64)->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->string('status', 30)->default('processed'); // processed, failed, ignored
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });

        Schema::create('business_suspensions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('businesses')->cascadeOnDelete();
            $table->text('reason');
            $table->foreignId('suspended_by')->constrained('users');
            $table->timestamp('suspended_at');
            $table->foreignId('unsuspended_by')->nullable()->constrained('users');
            $table->timestamp('unsuspended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type', 30)->default('string'); // string, boolean, integer, json
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_settings');
        Schema::dropIfExists('business_suspensions');
        Schema::dropIfExists('billing_events');
        Schema::dropIfExists('billing_invoices');
        Schema::dropIfExists('subscriptions');

        if (Schema::hasColumn('users', 'is_platform_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('is_platform_admin');
            });
        }
    }
};
