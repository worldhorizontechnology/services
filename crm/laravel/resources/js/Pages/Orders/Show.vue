<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

const props = defineProps({ order: Object });

function destroyOrder() {
    if (confirm('Delete this order?')) {
        router.delete(route('orders.destroy', props.order.id));
    }
}
</script>

<template>
    <Head :title="`Order #ORD-${order.id}`" />
    <AppLayout :title="`Order #ORD-${order.id}`">
        <div class="max-w-3xl space-y-6">
            <div class="rounded-2xl border border-border bg-panel p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-sm text-txt-muted">Customer</p>
                        <p class="text-lg font-bold text-txt-main">{{ order.customer?.first_name }} {{ order.customer?.last_name }}</p>
                        <p class="text-sm text-txt-muted">{{ order.customer?.phone }}</p>
                    </div>
                    <div class="flex gap-2">
                        <StatusBadge :status="order.status" />
                        <StatusBadge :status="order.payment_status" />
                    </div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                    <div><p class="text-txt-muted">Campaign</p><p class="text-txt-main">{{ order.campaign?.name || 'Direct visit' }}</p></div>
                    <div><p class="text-txt-muted">Discount</p><p class="text-txt-main">${{ order.discount_amount }}</p></div>
                    <div><p class="text-txt-muted">Total</p><p class="text-lg font-bold text-accent-beige">${{ order.total_amount }}</p></div>
                </div>
                <div class="mt-6 flex gap-3 border-t border-border pt-5">
                    <Link :href="route('orders.edit', order.id)" class="rounded-xl bg-accent-beige px-4 py-2 text-sm font-semibold text-main">Edit</Link>
                    <button type="button" class="rounded-xl border border-border px-4 py-2 text-sm font-semibold text-accent-pink hover:bg-border" @click="destroyOrder">Delete</button>
                    <Link :href="route('orders.index')" class="ml-auto self-center text-sm text-txt-muted hover:text-txt-main">Back to orders</Link>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <h2 class="border-b border-border px-6 py-4 font-bold text-txt-main">Services</h2>
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Service</th><th class="px-6 py-4 text-right">Qty</th><th class="px-6 py-4 text-right">Price</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="service in order.services ?? []" :key="service.id" class="hover:bg-main/30">
                            <td class="px-6 py-4 text-txt-main">{{ service.name }}</td>
                            <td class="px-6 py-4 text-right text-txt-muted">{{ service.pivot?.quantity }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-txt-main">${{ service.pivot?.price }}</td>
                        </tr>
                        <tr v-if="!(order.services ?? []).length"><td colspan="3" class="px-6 py-8 text-center text-txt-muted">No services on this order.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <h2 class="border-b border-border px-6 py-4 font-bold text-txt-main">Assignments</h2>
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Executor</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Due</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="assignment in order.assignments ?? []" :key="assignment.id" class="hover:bg-main/30">
                            <td class="px-6 py-4 text-txt-main">{{ assignment.executor?.first_name }} {{ assignment.executor?.last_name }}</td>
                            <td class="px-6 py-4"><StatusBadge :status="assignment.status" /></td>
                            <td class="px-6 py-4 text-txt-muted">{{ assignment.due_date || '—' }}</td>
                        </tr>
                        <tr v-if="!(order.assignments ?? []).length"><td colspan="3" class="px-6 py-8 text-center text-txt-muted">No executor assigned yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
