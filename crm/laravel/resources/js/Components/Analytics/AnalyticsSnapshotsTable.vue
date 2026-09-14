<template>
  <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
      <h3 class="font-bold text-white text-sm">Analytics Snapshots (`marketing_analytics_snapshots`)</h3>
      <span class="text-xs text-slate-500">Records: {{ snapshots.length }}</span>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm">
        <thead class="bg-slate-950/50 text-slate-400 uppercase text-[11px] font-semibold tracking-wider border-b border-slate-800">
          <tr>
            <th class="py-3 px-6">Date</th>
            <th class="py-3 px-6">Channel</th>
            <th class="py-3 px-6">Campaign</th>
            <th class="py-3 px-6 text-right">Leads</th>
            <th class="py-3 px-6 text-right">Ad Spend</th>
            <th class="py-3 px-6 text-right">Revenue</th>
            <th class="py-3 px-6 text-right">ROAS</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-800/60 text-slate-300">
          <tr v-for="item in snapshots" :key="item.id" class="hover:bg-slate-800/30 transition-colors">
            <td class="py-3.5 px-6 font-mono text-xs text-slate-300">{{ item.period_date }}</td>
            <td class="py-3.5 px-6">
              <span class="px-2 py-0.5 bg-blue-500/10 text-blue-400 text-xs rounded border border-blue-500/20 font-medium">
                {{ item.channel_name }}
              </span>
            </td>
            <td class="py-3.5 px-6 text-slate-400 text-xs">{{ item.campaign_name || '—' }}</td>
            <td class="py-3.5 px-6 text-right font-semibold text-white">{{ item.new_leads_count }}</td>
            <td class="py-3.5 px-6 text-right font-mono text-amber-400">${{ item.ad_spend.toFixed(2) }}</td>
            <td class="py-3.5 px-6 text-right font-mono text-emerald-400 font-bold">${{ item.total_revenue.toFixed(2) }}</td>
            <td class="py-3.5 px-6 text-right font-mono font-bold text-white">
              {{ item.ad_spend > 0 ? (item.total_revenue / item.ad_spend).toFixed(2) + 'x' : '0.00x' }}
            </td>
          </tr>
          <tr v-if="snapshots.length === 0">
            <td colspan="7" class="py-8 text-center text-slate-500 text-xs">
              No analytics snapshots found matching the active filters.
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
defineProps({
  snapshots: { type: Array, required: true }
});
</script>