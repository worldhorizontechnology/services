<template>
  <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-bold text-white text-base">Revenue vs Ad Spend</h3>
        <p class="text-xs text-slate-400">Financial dynamics over the selected timeframe</p>
      </div>
      <span class="text-xs font-mono text-blue-400 bg-blue-500/10 px-2 py-1 rounded border border-blue-500/20">
        {{ labels.length }} data points
      </span>
    </div>
    <div class="h-72 w-full relative">
      <canvas ref="canvasRef"></canvas>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
  labels: { type: Array, required: true },
  revenueData: { type: Array, required: true },
  spendData: { type: Array, required: true }
});

const canvasRef = ref(null);
let chartInstance = null;

function renderChart() {
  if (!canvasRef.value) return;
  if (chartInstance) chartInstance.destroy();

  chartInstance = new Chart(canvasRef.value.getContext('2d'), {
    type: 'line',
    data: {
      labels: props.labels,
      datasets: [
        {
          label: 'Revenue ($)',
          data: props.revenueData,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          fill: true,
          tension: 0.35,
          borderWidth: 2,
        },
        {
          label: 'Spend ($)',
          data: props.spendData,
          borderColor: '#f59e0b',
          borderDash: [4, 4],
          fill: false,
          tension: 0.35,
          borderWidth: 2,
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { labels: { color: '#94a3b8', font: { size: 12 } } }
      },
      scales: {
        x: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } },
        y: { grid: { color: '#1e293b' }, ticks: { color: '#94a3b8' } }
      }
    }
  });
}

watch(() => [props.labels, props.revenueData, props.spendData], renderChart, { deep: true });

onMounted(renderChart);
onUnmounted(() => {
  if (chartInstance) chartInstance.destroy();
});
</script>