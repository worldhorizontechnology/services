<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ campaigns: Array, channels: Array });
</script>

<template>
    <Head title="Campaigns" />
    <AppLayout title="Campaigns & Channels">
            <div class="rounded-2xl border border-border bg-panel p-6">
                <div v-for="campaign in campaigns ?? []" :key="campaign.id" class="border-b border-border py-3 last:border-0">
                    <div class="font-semibold text-txt-main">{{ campaign.name }}</div>
                    <div class="text-sm text-txt-muted">{{ campaign.channel?.name || 'No channel' }} · budget {{ campaign.budget }}</div>
                </div>
            </div>
    </AppLayout>
</template>
