<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class MarketingAnalyticsSnapshot extends Model
{
    protected $fillable = [
        'period_date',
        'period_type',
        'channel_id',
        'campaign_id',
        'new_leads_count',
        'new_paying_customers_count',
        'conversion_rate',
        'total_revenue',
        'total_discounts',
        'average_order_value',
        'ad_spend',
        'cac',
        'roas',
        'total_orders_count',
        'completed_orders_count',
        'cancelled_orders_count',
        'services_breakdown',
        'ai_context_data',
    ];

    protected $casts = [
        'period_date' => 'date',
        'services_breakdown' => 'array',
        'ai_context_data' => 'array',
        'conversion_rate' => 'float',
        'total_revenue' => 'float',
        'total_discounts' => 'float',
        'average_order_value' => 'float',
        'ad_spend' => 'float',
        'cac' => 'float',
        'roas' => 'float',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Aggregates metrics for the given timeframe and stores the snapshot.
     */
    public static function aggregatePeriod(
        Carbon $startDate,
        Carbon $endDate,
        string $periodType = 'daily',
        ?int $campaignId = null,
        ?int $channelId = null
    ): self {
        $startDateStr = $startDate->toDateTimeString();
        $endDateStr = $endDate->toDateTimeString();

        // 1. Calculate leads count
        $newLeadsCount = DB::table('customers')
            ->whereBetween('created_at', [$startDateStr, $endDateStr])
            ->when($campaignId, fn($q) => $q->where('campaign_id', $campaignId))
            ->when($channelId, fn($q) => $q->where('channel_id', $channelId))
            ->count();

        // 2. Calculate order statistics via raw aggregate SQL to optimize DB memory
        $ordersStats = DB::table('orders')
            ->whereBetween('created_at', [$startDateStr, $endDateStr])
            ->when($campaignId, fn($q) => $q->where('campaign_id', $campaignId))
            ->selectRaw("
                COUNT(id) as total_orders,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_orders,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_orders,
                SUM(CASE WHEN status = 'completed' THEN total_amount ELSE 0 END) as total_revenue,
                SUM(discount_amount) as total_discounts,
                COUNT(DISTINCT CASE WHEN status = 'completed' THEN customer_id END) as paying_customers
            ")
            ->first();

        $totalOrdersCount = (int) ($ordersStats->total_orders ?? 0);
        $completedOrdersCount = (int) ($ordersStats->completed_orders ?? 0);
        $cancelledOrdersCount = (int) ($ordersStats->cancelled_orders ?? 0);
        $totalRevenue = (float) ($ordersStats->total_revenue ?? 0.0);
        $totalDiscounts = (float) ($ordersStats->total_discounts ?? 0.0);
        $newPayingCustomersCount = (int) ($ordersStats->paying_customers ?? 0);

        // 3. Retrieve advertising spend (budget)
        $adSpend = 0.0;
        if ($campaignId) {
            $adSpend = (float) (DB::table('campaigns')->where('id', $campaignId)->value('budget') ?? 0.0);
        }

        // 4. Calculate key metrics and ratios
        $conversionRate = $newLeadsCount > 0 ? round(($newPayingCustomersCount / $newLeadsCount) * 100, 2) : 0.0;
        $averageOrderValue = $completedOrdersCount > 0 ? round($totalRevenue / $completedOrdersCount, 2) : 0.0;
        $cac = $newPayingCustomersCount > 0 ? round($adSpend / $newPayingCustomersCount, 2) : 0.0;
        $roas = $adSpend > 0 ? round($totalRevenue / $adSpend, 2) : 0.0;

        // 5. Build service performance breakdown for AI context
        $servicesBreakdown = DB::table('order_service')
            ->join('orders', 'order_service.order_id', '=', 'orders.id')
            ->join('services', 'order_service.service_id', '=', 'services.id')
            ->whereBetween('orders.created_at', [$startDateStr, $endDateStr])
            ->when($campaignId, fn($q) => $q->where('orders.campaign_id', $campaignId))
            ->select(
                'services.name',
                DB::raw('SUM(order_service.quantity) as total_qty'),
                DB::raw('SUM(order_service.price * order_service.quantity) as total_sales')
            )
            ->groupBy('services.id', 'services.name')
            ->orderByDesc('total_sales')
            ->get()
            ->toArray();

        // 6. Save or update snapshot record
        return static::updateOrCreate(
            [
                'period_date' => $startDate->toDateString(),
                'period_type' => $periodType,
                'campaign_id' => $campaignId,
                'channel_id' => $channelId,
            ],
            [
                'new_leads_count' => $newLeadsCount,
                'new_paying_customers_count' => $newPayingCustomersCount,
                'conversion_rate' => $conversionRate,
                'total_revenue' => $totalRevenue,
                'total_discounts' => $totalDiscounts,
                'average_order_value' => $averageOrderValue,
                'ad_spend' => $adSpend,
                'cac' => $cac,
                'roas' => $roas,
                'total_orders_count' => $totalOrdersCount,
                'completed_orders_count' => $completedOrdersCount,
                'cancelled_orders_count' => $cancelledOrdersCount,
                'services_breakdown' => $servicesBreakdown,
                'ai_context_data' => [
                    'generated_at' => now()->toIso8601String(),
                    'period' => [
                        'start' => $startDate->toDateString(),
                        'end' => $endDate->toDateString(),
                        'type' => $periodType,
                    ],
                    'summary_metrics' => [
                        'revenue' => $totalRevenue,
                        'orders' => $completedOrdersCount,
                        'conversion' => "{$conversionRate}%",
                    ],
                ],
            ]
        );
    }
}
