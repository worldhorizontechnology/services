<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Migrations\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('marketing_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            
            // Unique keys and time dimensions
            $table->date('period_date');
            $table->string('period_type')->default('daily'); // e.g., daily, weekly, monthly
            
            // Relationships mapped from your model methods
            $table->foreignId('channel_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained()->onDelete('set null');

            // Volumetric Counters
            $table->integer('new_leads_count')->default(0);
            $table->integer('new_paying_customers_count')->default(0);
            $table->integer('total_orders_count')->default(0);
            $table->integer('completed_orders_count')->default(0);
            $table->integer('cancelled_orders_count')->default(0);

            // Financial Metrics (Strict 15,2 decimal layout for CRM ledgers)
            $table->decimal('total_revenue', 15, 2)->default(0.00);
            $table->decimal('total_discounts', 15, 2)->default(0.00);
            $table->decimal('average_order_value', 15, 2)->default(0.00);
            $table->decimal('ad_spend', 15, 2)->default(0.00);

            // Analytical Ratios and Multipliers (Using standard float/decimal precisions)
            $table->decimal('conversion_rate', 5, 2)->default(0.00); // Percentage: 0.00% to 100.00%
            $table->decimal('cac', 15, 2)->default(0.00);            // Customer Acquisition Cost
            $table->decimal('roas', 8, 2)->default(0.00);           // Return on Ad Spend (multiplier scale)

            // Dynamic JSON Array Objects (Stores structural array matrix payloads)
            $table->json('services_breakdown')->nullable();
            $table->json('ai_context_data')->nullable();

            $table->timestamps();

            // Compound database index optimizing analytical reads and updateOrCreate checks
            $table->index(['period_date', 'period_type', 'campaign_id', 'channel_id'], 'analytics_lookup_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('marketing_analytics_snapshots');
    }
};
