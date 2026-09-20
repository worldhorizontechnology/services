<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import StatusBadge from '@/Components/UI/StatusBadge.vue';

defineProps({ user: Object, assignments: Object });
</script>
<template>
    <Head title="Team Member" />
    <AppLayout title="Team Member">
        <div class="max-w-3xl space-y-6">
            <div class="rounded-2xl border border-border bg-panel p-6 shadow-xl">
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-xl font-bold text-txt-main">{{ user?.first_name }} {{ user?.last_name }}</h1>
                        <p class="mt-2 text-txt-muted">{{ user?.role }} · {{ user?.email }}</p>
                        <p class="text-sm text-txt-muted">{{ user?.phone || 'No phone' }}</p>
                    </div>
                    <Link :href="route('users.edit', user.id)" class="rounded-xl bg-accent-beige px-4 py-2 text-sm font-semibold text-main">Edit</Link>
                </div>
                <div v-if="user?.services?.length" class="mt-4 flex flex-wrap gap-2">
                    <span v-for="service in user.services" :key="service.id" class="rounded-full border border-accent-beige/20 bg-accent-beige/10 px-2.5 py-1 text-xs text-accent-beige">{{ service.name }}</span>
                </div>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <h2 class="border-b border-border px-6 py-4 font-bold text-txt-main">Assignments</h2>
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Order</th><th class="px-6 py-4">Status</th><th class="px-6 py-4">Due</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="assignment in assignments?.data ?? []" :key="assignment.id" class="hover:bg-main/30">
                            <td class="px-6 py-4"><Link v-if="assignment.order" :href="route('orders.show', assignment.order.id)" class="font-mono font-bold text-accent-beige">#ORD-{{ assignment.order.id }}</Link><span v-else class="text-txt-muted">—</span></td>
                            <td class="px-6 py-4"><StatusBadge :status="assignment.status" /></td>
                            <td class="px-6 py-4 text-txt-muted">{{ assignment.due_date || '—' }}</td>
                        </tr>
                        <tr v-if="!(assignments?.data ?? []).length"><td colspan="3" class="px-6 py-8 text-center text-txt-muted">No assignments yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
