<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

defineProps({ customer: Object });
</script>

<template>
    <Head title="Customer" />
    <AppLayout title="Customer Profile">
        <div class="max-w-3xl space-y-6">
            <div class="rounded-2xl border border-border bg-panel p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-txt-main">{{ customer.first_name }} {{ customer.last_name }}</h1>
                        <p class="mt-3 text-txt-muted">{{ customer.phone || 'No phone' }}</p>
                        <p class="text-txt-muted">{{ customer.email || 'No email' }}</p>
                    </div>
                    <Link :href="route('customers.edit', customer.id)" class="rounded-xl bg-accent-beige px-4 py-2 text-sm font-semibold text-main">Edit</Link>
                </div>
                <Link :href="route('customers.index')" class="mt-6 inline-block text-sm text-accent-beige hover:underline">Back to customers</Link>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <h2 class="border-b border-border px-6 py-4 font-bold text-txt-main">Order history</h2>
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Order</th><th class="px-6 py-4">Status</th><th class="px-6 py-4 text-right">Total</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="order in customer.orders ?? []" :key="order.id" class="hover:bg-main/30">
                            <td class="px-6 py-4"><Link :href="route('orders.show', order.id)" class="font-mono font-bold text-accent-beige">#ORD-{{ order.id }}</Link></td>
                            <td class="px-6 py-4"><StatusBadge :status="order.status" /></td>
                            <td class="px-6 py-4 text-right font-bold text-txt-main">${{ order.total_amount }}</td>
                        </tr>
                        <tr v-if="!(customer.orders ?? []).length"><td colspan="3" class="px-6 py-8 text-center text-txt-muted">No orders yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
