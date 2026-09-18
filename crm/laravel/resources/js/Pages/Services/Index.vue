<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ services: { type: Array, default: () => [] } });
</script>

<template>
    <Head title="Services" />
    <AppLayout title="Service Catalog">
        <section class="space-y-6">
            <div class="flex items-center justify-between"><div><p class="text-sm text-txt-muted">Services and qualified executors</p></div><Link :href="route('services.create')" class="rounded-xl bg-accent-beige px-4 py-2.5 text-sm font-semibold text-main">Add service</Link></div>
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                <article v-for="service in services" :key="service.id" class="rounded-2xl border border-border bg-panel p-6 shadow-xl"><div class="flex items-start justify-between"><span class="rounded bg-accent-pink/10 px-2 py-1 font-mono text-xs text-accent-pink">ID {{ service.id }}</span><strong class="text-lg text-accent-beige">${{ service.price }}</strong></div><h2 class="mt-5 font-bold text-txt-main">{{ service.name }}</h2><p class="mt-2 text-sm text-txt-muted">{{ service.executors?.length || 0 }} qualified executors</p><span class="mt-6 inline-block rounded-full border border-accent-pink/20 bg-accent-pink/10 px-2.5 py-1 text-xs text-accent-pink">{{ service.is_active ? 'Active' : 'Inactive' }}</span></article>
                <div v-if="!services.length" class="rounded-2xl border border-dashed border-border p-10 text-center text-txt-muted md:col-span-2 xl:col-span-3">No services yet.</div>
            </div>
        </section>
    </AppLayout>
</template>