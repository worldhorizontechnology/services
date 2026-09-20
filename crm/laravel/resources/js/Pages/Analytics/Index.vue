<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MetricCard from '@/Components/Widgets/MetricCard.vue';

const props = defineProps({ snapshots: Object, metrics: Object });
const rows = computed(() => props.snapshots?.data || []);
const total = computed(() => props.metrics || {
    totalRevenue: rows.value.reduce((sum, row) => sum + Number(row.total_revenue || 0), 0),
    adSpend: rows.value.reduce((sum, row) => sum + Number(row.ad_spend || 0), 0),
    roas: '—',
    cac: '—',
});
</script>

<template>
    <Head title="Marketing & Analytics" />
    <AppLayout title="Marketing & Analytics">
        <section class="space-y-6">
            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                <MetricCard label="Total Revenue" :value="`$${Number(total.totalRevenue || 0).toFixed(2)}`" trend="Live" tone="emerald" />
                <MetricCard label="Ad Spend" :value="`$${Number(total.adSpend || 0).toFixed(2)}`" tone="amber" />
                <MetricCard label="Average ROAS" :value="total.roas || '—'" tone="blue" />
                <MetricCard label="CAC" :value="total.cac || '—'" tone="purple" />
            </div>
            <div class="grid gap-6 lg:grid-cols-3">
                <section class="rounded-2xl border border-border bg-panel p-6 shadow-xl lg:col-span-2">
                    <h2 class="font-bold text-txt-main">Revenue vs advertising spend</h2>
                    <p class="mt-1 text-xs text-txt-muted">Data from marketing analytics snapshots.</p>
                    <div class="mt-8 flex h-56 items-end gap-3 border-b border-border">
                        <div v-for="row in rows.slice(-12)" :key="row.id" class="flex flex-1 flex-col justify-end gap-2">
                            <div class="rounded-t bg-accent-beige/80" :style="{ height: `${Math.max(8, Math.min(100, Number(row.total_revenue || 0) / 100))}%` }"></div>
                            <span class="truncate text-center text-[10px] text-txt-muted">{{ row.period_date || '—' }}</span>
                        </div>
                        <div v-if="!rows.length" class="w-full pb-20 text-center text-sm text-txt-muted">No snapshots yet.</div>
                    </div>
                </section>
                <section class="rounded-2xl border border-border bg-panel p-6 shadow-xl">
                    <h2 class="font-bold text-txt-main">Channel conversion</h2>
                    <p class="mt-1 text-xs text-txt-muted">Acquisition distribution.</p>
                    <div class="mt-8 flex h-48 items-center justify-center rounded-full border-[24px] border-accent-beige/70 text-center text-sm text-txt-muted">Analytics<br>snapshot</div>
                </section>
            </div>
        </section>
    </AppLayout>
</template>
