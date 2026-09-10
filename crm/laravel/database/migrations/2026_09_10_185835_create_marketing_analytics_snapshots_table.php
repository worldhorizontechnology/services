<?php

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->date('period_date');
            $table->enum('period_type', ['daily', 'weekly', 'monthly'])->default('daily');

            // Foreign keys for tracking breakdown by channel and campaign
            $table->foreignId('channel_id')->nullable()->constrained('channels')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();

            // Lead generation and acquisition metrics
            $table->unsignedInteger('new_leads_count')->default(0);
            $table->unsignedInteger('new_paying_customers_count')->default(0);
            $table->decimal('conversion_rate', 5, 2)->default(0.00);

            // Financial and unit economics metrics
            $table->decimal('total_revenue', 12, 2)->default(0.00);
            $table->decimal('total_discounts', 12, 2)->default(0.00);
            $table->decimal('average_order_value', 10, 2)->default(0.00);
            $table->decimal('ad_spend', 12, 2)->default(0.00);
            $table->decimal('cac', 10, 2)->default(0.00);
            $table->decimal('roas', 8, 2)->default(0.00);

            // Order metrics
            $table->unsignedInteger('total_orders_count')->default(0);
            $table->unsignedInteger('completed_orders_count')->default(0);
            $table->unsignedInteger('cancelled_orders_count')->default(0);

            // Structured JSON payload for AI processing
            $table->jsonb('services_breakdown')->nullable();
            $table->jsonb('ai_context_data')->nullable();

            $table->timestamps();

            // Composite unique index to avoid duplicates and speed up range queries
            $table->unique(['period_date', 'period_type', 'channel_id', 'campaign_id'], 'mas_unique_period_index');
            $table->index(['period_date', 'period_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_analytics_snapshots');
    }
};