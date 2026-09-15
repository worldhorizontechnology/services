<template>
  <AppLayout title="Marketing & Analytics">
    <!-- HEADER WITH FILTER BAR COMPONENT -->
    <template #header>
      <Header title="Marketing & Analytics" subtitle="Cross-channel performance metrics and acquisition dynamics">
        <template #actions>
          <AnalyticsFilterBar 
            v-model="filters"
            :channels="channels"
            :campaigns="campaigns"
            @reset="resetFilters"
          />
        </template>
      </Header>
    </template>

    <!-- MAIN CONTENT -->
    <main class="p-6 space-y-6 flex-1 overflow-y-auto">
      <!-- KPI METRICS COMPONENT -->
      <AnalyticsMetrics :metrics="metrics" />

      <!-- CHARTS COMPONENTS -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <RevenueChart 
          class="lg:col-span-2"
          :labels="chartData.labels"
          :revenue-data="chartData.revenue"
          :spend-data="chartData.spend"
        />
        
        <ChannelChart 
          :snapshots="snapshotList"
        />
      </div>

      <!-- SNAPSHOTS TABLE COMPONENT -->
      <AnalyticsSnapshotsTable :snapshots="snapshotList" />
    </main>

    <!-- FOOTER COMPONENT -->
    <Footer />
  </AppLayout>
</template>

<script setup>
import { reactive, computed, watch } from 'vue';
import { router } from '@inertiajs/vue3';

// Standard Layouts & Layout Components
import AppLayout from '@/Layouts/AppLayout.vue';
import Header from '@/Components/Header.vue';
import Footer from '@/Components/Footer.vue';

// Dedicated Analytics Components
import AnalyticsFilterBar from '@/Components/Analytics/AnalyticsFilterBar.vue';
import AnalyticsMetrics from '@/Components/Analytics/AnalyticsMetrics.vue';
import RevenueChart from '@/Components/Analytics/RevenueChart.vue';
import ChannelChart from '@/Components/Analytics/ChannelChart.vue';
import AnalyticsSnapshotsTable from '@/Components/Analytics/AnalyticsSnapshotsTable.vue';

// --- PROPS FROM LARAVEL INERTIA ---
const props = defineProps({
  snapshots: Object, // Paginated object from Laravel
  channels: Array,
  campaigns: Array,
  filters: Object,
});

// --- REHYDRATE FILTERS STATE ---
const filters = reactive({
  period_type: props.filters?.period_type || '',
  channel_id: props.filters?.channel_id || '',
  campaign_id: props.filters?.campaign_id || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
});

// Flat items array from pagination
const snapshotList = computed(() => props.snapshots?.data || []);

// --- SERVER-SIDE FILTERING VIA INERTIA ---
watch(
  filters,
  (newFilters) => {
    // Clean empty values
    const queryParams = Object.fromEntries(
      Object.entries(newFilters).filter(([_, v]) => v !== '' && v !== null)
    );

    router.get(route('analytics.index'), queryParams, {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  },
  { deep: true }
);

// --- COMPUTED AGGREGATED METRICS ---
const metrics = computed(() => {
  const rev = snapshotList.value.reduce((acc, i) => acc + Number(i.total_revenue || 0), 0);
  const spend = snapshotList.value.reduce((acc, i) => acc + Number(i.ad_spend || 0), 0);
  const leads = snapshotList.value.reduce((acc, i) => acc + Number(i.new_leads_count || 0), 0);

  return {
    totalRevenue: rev,
    adSpend: spend,
    roas: spend > 0 ? (rev / spend).toFixed(2) : '0.00',
    cac: leads > 0 ? (spend / leads).toFixed(2) : '0.00',
  };
});

// --- COMPUTED CHART DATA ---
const chartData = computed(() => {
  const sortedSnapshots = [...snapshotList.value].reverse();

  return {
    labels: sortedSnapshots.map(i => i.period_date),
    revenue: sortedSnapshots.map(i => i.total_revenue),
    spend: sortedSnapshots.map(i => i.ad_spend),
  };
});

function resetFilters() {
  filters.period_type = '';
  filters.channel_id = '';
  filters.campaign_id = '';
  filters.date_from = '';
  filters.date_to = '';
}
</script>