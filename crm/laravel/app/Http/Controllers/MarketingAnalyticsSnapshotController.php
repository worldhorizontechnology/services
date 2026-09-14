<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MarketingAnalyticsSnapshot;
use App\Models\Channel;
use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MarketingAnalyticsSnapshotController extends Controller
{
    /**
     * 1. DISPLAY LISTING (index)
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period_type' => 'nullable|string|in:daily,weekly,monthly',
            'channel_id'  => 'nullable|integer|exists:channels,id',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'date_from'   => 'nullable|date',
            'date_to'     => 'nullable|date',
            'per_page'    => 'nullable|integer|min:1|max:100',
        ]);

        $query = MarketingAnalyticsSnapshot::query()
            ->with(['channel', 'campaign'])
            ->when($request->filled('period_type'), fn($q) => $q->where('period_type', $validated['period_type']))
            ->when($request->filled('channel_id'), fn($q) => $q->where('channel_id', $validated['channel_id']))
            ->when($request->filled('campaign_id'), fn($q) => $q->where('campaign_id', $validated['campaign_id']))
            ->when($request->filled('date_from'), fn($q) => $q->where('period_date', '>=', $validated['date_from']))
            ->when($request->filled('date_to'), fn($q) => $q->where('period_date', '<=', $validated['date_to']))
            ->orderByDesc('period_date')
            ->orderByDesc('id');

        return response()->json($query->paginate($request->input('per_page', 15)));
    }

    /**
     * 2. SHOW FORM / DATA FOR CREATION (create)
     * Возвращает метаданные и справочники (каналы, кампании), необходимые фронтенду для построения формы создания.
     */
    public function create(): JsonResponse
    {
        return response()->json([
            'period_types' => ['daily', 'weekly', 'monthly'],
            'channels'     => Channel::select('id', 'name')->get(),
            'campaigns'    => Campaign::select('id', 'name')->get(),
            'default_values' => [
                'period_date' => Carbon::now()->toDateString(),
                'period_type' => 'daily',
                'new_leads_count' => 0,
                'new_paying_customers_count' => 0,
                'total_revenue' => 0.0,
                'ad_spend' => 0.0,
            ]
        ]);
    }

    /**
     * 3. STORE NEWLY CREATED RESOURCE (store)
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period_date'                => 'required|date',
            'period_type'                => 'required|string|in:daily,weekly,monthly',
            'channel_id'                 => 'nullable|integer|exists:channels,id',
            'campaign_id'                => 'nullable|integer|exists:campaigns,id',
            'new_leads_count'            => 'required|integer|min:0',
            'new_paying_customers_count' => 'required|integer|min:0',
            'total_revenue'              => 'required|numeric|min:0',
            'total_discounts'            => 'nullable|numeric|min:0',
            'ad_spend'                   => 'nullable|numeric|min:0',
            'total_orders_count'         => 'required|integer|min:0',
            'completed_orders_count'     => 'required|integer|min:0',
            'cancelled_orders_count'     => 'required|integer|min:0',
            'services_breakdown'         => 'nullable|array',
            'ai_context_data'            => 'nullable|array',
        ]);

        $leads     = $validated['new_leads_count'];
        $paying    = $validated['new_paying_customers_count'];
        $revenue   = $validated['total_revenue'];
        $adSpend   = $validated['ad_spend'] ?? 0.0;
        $completed = $validated['completed_orders_count'];

        $validated['conversion_rate']     = $leads > 0 ? round(($paying / $leads) * 100, 2) : 0.0;
        $validated['average_order_value'] = $completed > 0 ? round($revenue / $completed, 2) : 0.0;
        $validated['cac']                 = $paying > 0 ? round($adSpend / $paying, 2) : 0.0;
        $validated['roas']                = $adSpend > 0 ? round($revenue / $adSpend, 2) : 0.0;

        $snapshot = MarketingAnalyticsSnapshot::create($validated);

        return response()->json([
            'message'  => 'Снапшот создан',
            'snapshot' => $snapshot->load(['channel', 'campaign']),
        ], 201);
    }

    /**
     * 4. DISPLAY SPECIFIED RESOURCE (show)
     */
    public function show(MarketingAnalyticsSnapshot $analyticsSnapshot): JsonResponse
    {
        return response()->json(
            $analyticsSnapshot->load(['channel', 'campaign'])
        );
    }

    /**
     * 5. SHOW FORM / DATA FOR EDITING (edit)
     * Возвращает редактируемую запись вместе со справочниками.
     */
    public function edit(MarketingAnalyticsSnapshot $analyticsSnapshot): JsonResponse
    {
        return response()->json([
            'snapshot'     => $analyticsSnapshot->load(['channel', 'campaign']),
            'period_types' => ['daily', 'weekly', 'monthly'],
            'channels'     => Channel::select('id', 'name')->get(),
            'campaigns'    => Campaign::select('id', 'name')->get(),
        ]);
    }

    /**
     * 6. UPDATE SPECIFIED RESOURCE (update)
     */
    public function update(Request $request, MarketingAnalyticsSnapshot $analyticsSnapshot): JsonResponse
    {
        $validated = $request->validate([
            'period_type'                => 'sometimes|string|in:daily,weekly,monthly',
            'channel_id'                 => 'nullable|integer|exists:channels,id',
            'campaign_id'                => 'nullable|integer|exists:campaigns,id',
            'new_leads_count'            => 'sometimes|integer|min:0',
            'new_paying_customers_count' => 'sometimes|integer|min:0',
            'total_revenue'              => 'sometimes|numeric|min:0',
            'total_discounts'            => 'sometimes|numeric|min:0',
            'ad_spend'                   => 'sometimes|numeric|min:0',
            'total_orders_count'         => 'sometimes|integer|min:0',
            'completed_orders_count'     => 'sometimes|integer|min:0',
            'cancelled_orders_count'     => 'sometimes|integer|min:0',
            'services_breakdown'         => 'nullable|array',
            'ai_context_data'            => 'nullable|array',
        ]);

        $analyticsSnapshot->update($validated);

        // Перерасчёт
        $leads     = $analyticsSnapshot->new_leads_count;
        $paying    = $analyticsSnapshot->new_paying_customers_count;
        $revenue   = $analyticsSnapshot->total_revenue;
        $adSpend   = $analyticsSnapshot->ad_spend;
        $completed = $analyticsSnapshot->completed_orders_count;

        $analyticsSnapshot->update([
            'conversion_rate'     => $leads > 0 ? round(($paying / $leads) * 100, 2) : 0.0,
            'average_order_value' => $completed > 0 ? round($revenue / $completed, 2) : 0.0,
            'cac'                 => $paying > 0 ? round($adSpend / $paying, 2) : 0.0,
            'roas'                => $adSpend > 0 ? round($revenue / $adSpend, 2) : 0.0,
        ]);

        return response()->json([
            'message'  => 'Снапшот обновлен',
            'snapshot' => $analyticsSnapshot->load(['channel', 'campaign']),
        ]);
    }

    /**
     * 7. REMOVE SPECIFIED RESOURCE (destroy)
     */
    public function destroy(MarketingAnalyticsSnapshot $analyticsSnapshot): JsonResponse
    {
        $analyticsSnapshot->delete();

        return response()->json([
            'message' => 'Снапшот успешно удален',
        ]);
    }

    /**
     * CUSTOM METHOD: AGGREGATE PERIOD
     */
    public function aggregate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date'  => 'required|date',
            'end_date'    => 'required|date|after_or_equal:start_date',
            'period_type' => 'nullable|string|in:daily,weekly,monthly',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'channel_id'  => 'nullable|integer|exists:channels,id',
        ]);

        $snapshot = MarketingAnalyticsSnapshot::aggregatePeriod(
            Carbon::parse($validated['start_date']),
            Carbon::parse($validated['end_date']),
            $validated['period_type'] ?? 'daily',
            $validated['campaign_id'] ?? null,
            $validated['channel_id'] ?? null
        );

        return response()->json([
            'message'  => 'Снапшот успешно агрегирован',
            'snapshot' => $snapshot->load(['channel', 'campaign']),
        ], 201);
    }
}




/////////////
// namespace App\Http\Controllers;

// use App\Models\MarketingAnalyticsSnapshot;
// use Carbon\Carbon;
// use Illuminate\Http\Request;
// use Illuminate\Http\JsonResponse;

// class MarketingAnalyticsSnapshotController extends Controller
// {
//     /**
//      * Fetch aggregated analytics data for the AI Agent within a date range.
//      * GET /api/analytics/snapshots?start_date=2026-09-01&end_date=2026-09-07&period_type=weekly
//      */
//     public function index(Request $request): JsonResponse
//     {
//         $validated = $request->validate([
//             'start_date' => 'required|date',
//             'end_date' => 'required|date|after_or_equal:start_date',
//             'period_type' => 'nullable|in:daily,weekly,monthly',
//             'campaign_id' => 'nullable|integer|exists:campaigns,id',
//             'channel_id' => 'nullable|integer|exists:channels,id',
//         ]);

//         $query = MarketingAnalyticsSnapshot::query()
//             ->whereBetween('period_date', [$validated['start_date'], $validated['end_date']]);

//         if (!empty($validated['period_type'])) {
//             $query->where('period_type', $validated['period_type']);
//         }

//         if (!empty($validated['campaign_id'])) {
//             $query->where('campaign_id', $validated['campaign_id']);
//         }

//         if (!empty($validated['channel_id'])) {
//             $query->where('channel_id', $validated['channel_id']);
//         }

//         $snapshots = $query->with(['campaign:id,name', 'channel:id,name'])
//             ->orderBy('period_date', 'asc')
//             ->get();

//         return response()->json([
//             'status' => 'success',
//             'count' => $snapshots->count(),
//             'data' => $snapshots,
//         ]);
//     }

//     /**
//      * Trigger explicit analytics recalculation (via admin panel or background jobs).
//      * POST /api/analytics/recalculate
//      */
//     public function recalculate(Request $request): JsonResponse
//     {
//         $validated = $request->validate([
//             'start_date' => 'required|date',
//             'end_date' => 'required|date|after_or_equal:start_date',
//             'period_type' => 'required|in:daily,weekly,monthly',
//             'campaign_id' => 'nullable|integer|exists:campaigns,id',
//             'channel_id' => 'nullable|integer|exists:channels,id',
//         ]);

//         $startDate = Carbon::parse($validated['start_date'])->startOfDay();
//         $endDate = Carbon::parse($validated['end_date'])->endOfDay();

//         $snapshot = MarketingAnalyticsSnapshot::aggregatePeriod(
//             $startDate,
//             $endDate,
//             $validated['period_type'],
//             $validated['campaign_id'] ?? null,
//             $validated['channel_id'] ?? null
//         );

//         return response()->json([
//             'status' => 'success',
//             'message' => 'Analytics snapshot aggregated successfully.',
//             'data' => $snapshot,
//         ]);
//     }
// }

/////////////

