<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ channels: Array });
</script>

<template>
    <Head title="Channels" />
    <AppLayout title="Traffic Channels">
            <div class="rounded-2xl border border-border bg-panel p-6">
                <div v-for="channel in channels ?? []" :key="channel.id" class="flex justify-between border-b border-border py-3 last:border-0">
                    <span class="font-semibold text-txt-main">{{ channel.name }}</span>
                    <span class="text-sm text-txt-muted">{{ channel.type }} · {{ channel.campaigns_count }} campaigns</span>
                </div>
            </div>
    </AppLayout>
</template>
