<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ channels: Array });
</script>

<template>
    <Head title="Channels" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Channels</h2></template>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <div v-for="channel in channels ?? []" :key="channel.id" class="flex justify-between border-b py-3 last:border-0">
                    <span class="font-semibold">{{ channel.name }}</span>
                    <span class="text-sm text-gray-500">{{ channel.type }} · {{ channel.campaigns_count }} campaigns</span>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
