<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

defineProps({ orders: Object, customers: Array, services: Array });
</script>

<template>
    <Head title="Orders" />
    <AppLayout title="Orders & Execution">
        <section class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div><p class="text-sm text-txt-muted">Order registry and fulfillment workload</p></div>
                <Link :href="route('orders.create')" class="rounded-xl bg-accent-beige px-4 py-2.5 text-sm font-semibold text-main shadow-lg shadow-accent-beige/20">Create order</Link>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <table class="w-full text-left text-sm"><thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted"><tr><th class="px-6 py-4">Order</th><th class="px-6 py-4">Customer</th><th class="px-6 py-4">Services</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Total</th></tr></thead>
                    <tbody class="divide-y divide-border/70"><tr v-for="order in orders?.data || []" :key="order.id" class="hover:bg-main/30"><td class="px-6 py-4"><Link :href="route('orders.show', order.id)" class="font-mono font-bold text-accent-beige">#ORD-{{ order.id }}</Link></td><td class="px-6 py-4 text-txt-main">{{ order.customer?.first_name }} {{ order.customer?.last_name }}</td><td class="px-6 py-4 text-txt-muted">{{ order.services?.map(service => service.name).join(', ') || 'No service line' }}</td><td class="px-6 py-4"><StatusBadge :status="order.status" /><StatusBadge class="ml-2" :status="order.payment_status" /></td><td class="px-6 py-4 text-right font-bold text-txt-main">${{ order.total_amount }}</td></tr><tr v-if="!(orders?.data || []).length"><td colspan="5" class="px-6 py-12 text-center text-txt-muted">No orders yet.</td></tr></tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
