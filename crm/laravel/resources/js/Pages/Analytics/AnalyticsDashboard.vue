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
          :snapshots="filteredSnapshots"
        />
      </div>

      <!-- SNAPSHOTS TABLE COMPONENT -->
      <AnalyticsSnapshotsTable :snapshots="filteredSnapshots" />
    </main>

    <!-- FOOTER COMPONENT -->
    <Footer />
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';

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

// --- STATE: FILTERS ---
const filters = reactive({
  period: '7d',
  dateFrom: '',
  dateTo: '',
  channelId: '',
  campaignId: '',
});

// --- DICTIONARIES ---
const channels = ref([
  { id: 1, name: 'Google Ads' },
  { id: 2, name: 'Telegram Bot' },
  { id: 3, name: 'Direct Visit' },
  { id: 4, name: 'Flyer Discount' },
]);

const campaigns = ref([
  { id: 101, name: 'Summer Offer 2026' },
  { id: 102, name: 'Remarketing Q3' },
  { id: 103, name: 'Brand Search' },
]);

// --- RAW SNAPSHOTS DATA ---
const rawSnapshots = ref([
  { id: 1, period_date: '2026-09-07', channel_id: 1, channel_name: 'Google Ads', campaign_id: 101, campaign_name: 'Summer Offer 2026', new_leads_count: 24, ad_spend: 300, total_revenue: 1200 },
  { id: 2, period_date: '2026-09-08', channel_id: 1, channel_name: 'Google Ads', campaign_id: 101, campaign_name: 'Summer Offer 2026', new_leads_count: 32, ad_spend: 400, total_revenue: 1900 },
  { id: 3, period_date: '2026-09-09', channel_id: 2, channel_name: 'Telegram Bot', campaign_id: 102, campaign_name: 'Remarketing Q3', new_leads_count: 45, ad_spend: 500, total_revenue: 3000 },
  { id: 4, period_date: '2026-09-10', channel_id: 2, channel_name: 'Telegram Bot', campaign_id: 102, campaign_name: 'Remarketing Q3', new_leads_count: 38, ad_spend: 450, total_revenue: 2500 },
  { id: 5, period_date: '2026-09-11', channel_id: 3, channel_name: 'Direct Visit', campaign_id: null, campaign_name: null, new_leads_count: 15, ad_spend: 0, total_revenue: 2200 },
  { id: 6, period_date: '2026-09-12', channel_id: 1, channel_name: 'Google Ads', campaign_id: 103, campaign_name: 'Brand Search', new_leads_count: 50, ad_spend: 400, total_revenue: 3100 },
  { id: 7, period_date: '2026-09-13', channel_id: 4, channel_name: 'Flyer Discount', campaign_id: 101, campaign_name: 'Summer Offer 2026', new_leads_count: 28, ad_spend: 450, total_revenue: 3450 },
]);

// --- COMPUTED FILTERED SNAPSHOTS ---
const filteredSnapshots = computed(() => {
  return rawSnapshots.value.filter(item => {
    if (filters.channelId && item.channel_id !== Number(filters.channelId)) return false;
    if (filters.campaignId && item.campaign_id !== Number(filters.campaignId)) return false;
    if (filters.dateFrom && item.period_date < filters.dateFrom) return false;
    if (filters.dateTo && item.period_date > filters.dateTo) return false;
    return true;
  });
});

// --- COMPUTED AGGREGATED METRICS ---
const metrics = computed(() => {
  const rev = filteredSnapshots.value.reduce((acc, i) => acc + i.total_revenue, 0);
  const spend = filteredSnapshots.value.reduce((acc, i) => acc + i.ad_spend, 0);
  const leads = filteredSnapshots.value.reduce((acc, i) => acc + i.new_leads_count, 0);

  return {
    totalRevenue: rev,
    adSpend: spend,
    roas: spend > 0 ? (rev / spend).toFixed(2) : '0.00',
    cac: leads > 0 ? (spend / leads).toFixed(2) : '0.00',
  };
});

// --- COMPUTED CHART DATA ---
const chartData = computed(() => {
  return {
    labels: filteredSnapshots.value.map(i => i.period_date),
    revenue: filteredSnapshots.value.map(i => i.total_revenue),
    spend: filteredSnapshots.value.map(i => i.ad_spend),
  };
});

function resetFilters() {
  filters.period = '7d';
  filters.dateFrom = '';
  filters.dateTo = '';
  filters.channelId = '';
  filters.campaignId = '';
}
</script>