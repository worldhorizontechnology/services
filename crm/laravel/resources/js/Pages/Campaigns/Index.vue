<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ campaigns: Array, channels: Array });
</script>

<template>
    <Head title="Campaigns" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Campaigns</h2></template>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div v-for="campaign in campaigns ?? []" :key="campaign.id" class="border-b py-3 last:border-0">
                    <div class="font-semibold">{{ campaign.name }}</div>
                    <div class="text-sm text-gray-500">{{ campaign.channel?.name || 'No channel' }} · budget {{ campaign.budget }}</div>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
