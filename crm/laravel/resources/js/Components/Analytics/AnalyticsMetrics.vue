<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wider">Total Revenue</span>
        <span class="text-emerald-400 font-bold">$</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-2xl font-black text-white">${{ metrics.totalRevenue.toLocaleString() }}</span>
        <span class="text-xs font-semibold text-emerald-400">+14.2%</span>
      </div>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wider">Ad Spend</span>
        <span class="text-amber-400 font-bold">⚡</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-2xl font-black text-white">${{ metrics.adSpend.toLocaleString() }}</span>
        <span class="text-xs font-semibold text-rose-400">+5.1%</span>
      </div>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wider">Average ROAS</span>
        <span class="text-blue-400 font-bold">📊</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-2xl font-black text-white">{{ metrics.roas }}x</span>
        <span class="text-xs font-semibold text-emerald-400">+0.8x</span>
      </div>
    </div>

    <div class="p-5 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col justify-between">
      <div class="flex items-center justify-between text-slate-400 mb-2">
        <span class="text-xs font-semibold uppercase tracking-wider">Lead Cost (CAC)</span>
        <span class="text-purple-400 font-bold">🎯</span>
      </div>
      <div class="flex items-baseline justify-between">
        <span class="text-2xl font-black text-white">${{ metrics.cac }}</span>
        <span class="text-xs font-semibold text-emerald-400">-2.4%</span>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  metrics: {
    type: Object,
    required: true,
    default: () => ({ totalRevenue: 0, adSpend: 0, roas: '0.00', cac: '0.00' })
  }
});
</script>