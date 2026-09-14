<template>
  <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col">
    <h3 class="font-bold text-white text-base mb-1">Conversion by Channel</h3>
    <p class="text-xs text-slate-400 mb-4">Lead distribution per acquisition source</p>
    <div class="h-64 w-full flex items-center justify-center relative">
      <canvas ref="canvasRef"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  snapshots: { type: Array, required: true }
});

const canvasRef = ref(null);
let chartInstance = null;

function renderChart() {
  if (!canvasRef.value) return;
  if (chartInstance) chartInstance.destroy();

  const channelMap = {};
  props.snapshots.forEach(s => {
    channelMap[s.channel_name] = (channelMap[s.channel_name] || 0) + s.new_leads_count;
  });

  chartInstance = new Chart(canvasRef.value.getContext('2d'), {
    type: 'doughnut',
    data: {
      labels: Object.keys(channelMap),
      datasets: [{
        data: Object.values(channelMap),
        backgroundColor: ['#3b82f6', '#06b6d4', '#a855f7', '#f59e0b', '#10b981'],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { color: '#94a3b8', boxWidth: 10 } }
      }
    }
  });
}

watch(() => props.snapshots, renderChart, { deep: true });

onMounted(renderChart);
onUnmounted(() => {
  if (chartInstance) chartInstance.destroy();
});
</script>