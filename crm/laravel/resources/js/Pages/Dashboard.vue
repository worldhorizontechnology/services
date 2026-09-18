<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    calendar: { type: Object, default: null },
});
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout title="Dashboard">
        <section class="space-y-6">
            <div class="rounded-2xl border border-border bg-panel p-6 shadow-xl">
                <p class="text-xs font-semibold uppercase tracking-widest text-accent-pink">Workspace</p>
                <h1 class="mt-2 text-3xl font-black text-txt-main">Operational overview</h1>
                <p class="mt-2 max-w-2xl text-sm text-txt-muted">Use the existing CRM modules to manage orders, services, customers and the team.</p>
            </div>
            <div class="grid gap-5 md:grid-cols-3">
                <Link :href="route('analytics.index')" class="rounded-2xl border border-border bg-panel p-5 transition hover:border-accent-beige">
                    <span class="text-xs uppercase tracking-wider text-txt-muted">Analytics</span><strong class="mt-3 block text-xl text-txt-main">Marketing performance</strong>
                </Link>
                <Link :href="route('orders.index')" class="rounded-2xl border border-border bg-panel p-5 transition hover:border-accent-beige">
                    <span class="text-xs uppercase tracking-wider text-txt-muted">Orders</span><strong class="mt-3 block text-xl text-txt-main">Sales & execution</strong>
                </Link>
                <a v-if="calendar" :href="calendar.url" target="_blank" rel="noreferrer" class="rounded-2xl border border-border bg-panel p-5 transition hover:border-accent-beige">
                    <span class="text-xs uppercase tracking-wider text-txt-muted">Calendar</span><strong class="mt-3 block text-xl text-txt-main">Open your Google Calendar</strong>
                </a>
                <div v-else class="rounded-2xl border border-border bg-panel p-5">
                    <span class="text-xs uppercase tracking-wider text-txt-muted">Calendar</span><strong class="mt-3 block text-xl text-txt-main">No calendar connected</strong>
                </div>
            </div>
        </section>
    </AppLayout>
</template>
