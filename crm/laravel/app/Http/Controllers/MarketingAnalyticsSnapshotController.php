<?php

namespace App\Http\Controllers;

use App\Models\MarketingAnalyticsSnapshot;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MarketingAnalyticsSnapshotController extends Controller
{
    /**
     * Fetch aggregated analytics data for the AI Agent within a date range.
     * GET /api/analytics/snapshots?start_date=2026-09-01&end_date=2026-09-07&period_type=weekly
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'nullable|in:daily,weekly,monthly',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'channel_id' => 'nullable|integer|exists:channels,id',
        ]);

        $query = MarketingAnalyticsSnapshot::query()
            ->whereBetween('period_date', [$validated['start_date'], $validated['end_date']]);

        if (!empty($validated['period_type'])) {
            $query->where('period_type', $validated['period_type']);
        }

        if (!empty($validated['campaign_id'])) {
            $query->where('campaign_id', $validated['campaign_id']);
        }

        if (!empty($validated['channel_id'])) {
            $query->where('channel_id', $validated['channel_id']);
        }

        $snapshots = $query->with(['campaign:id,name', 'channel:id,name'])
            ->orderBy('period_date', 'asc')
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $snapshots->count(),
            'data' => $snapshots,
        ]);
    }

    /**
     * Trigger explicit analytics recalculation (via admin panel or background jobs).
     * POST /api/analytics/recalculate
     */
    public function recalculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'period_type' => 'required|in:daily,weekly,monthly',
            'campaign_id' => 'nullable|integer|exists:campaigns,id',
            'channel_id' => 'nullable|integer|exists:channels,id',
        ]);

        $startDate = Carbon::parse($validated['start_date'])->startOfDay();
        $endDate = Carbon::parse($validated['end_date'])->endOfDay();

        $snapshot = MarketingAnalyticsSnapshot::aggregatePeriod(
            $startDate,
            $endDate,
            $validated['period_type'],
            $validated['campaign_id'] ?? null,
            $validated['channel_id'] ?? null
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Analytics snapshot aggregated successfully.',
            'data' => $snapshot,
        ]);
    }
}
