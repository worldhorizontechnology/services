<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ channels: Array });

function destroyChannel(id) {
    if (confirm('Delete this channel?')) {
        router.delete(route('channels.destroy', id));
    }
}
</script>

<template>
    <Head title="Channels" />
    <AppLayout title="Traffic Channels">
        <section class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <p class="text-sm text-txt-muted">Acquisition channels feeding campaigns and customers</p>
                <Link :href="route('channels.create')" class="rounded-xl bg-accent-beige px-4 py-2.5 text-sm font-semibold text-main shadow-lg shadow-accent-beige/20">Create channel</Link>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Channel</th><th class="px-6 py-4">Type</th><th class="px-6 py-4 text-right">Campaigns</th><th class="px-6 py-4 text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="channel in channels ?? []" :key="channel.id" class="hover:bg-main/30">
                            <td class="px-6 py-4 font-semibold text-txt-main">{{ channel.name }}</td>
                            <td class="px-6 py-4 text-txt-muted">{{ channel.type }}</td>
                            <td class="px-6 py-4 text-right text-txt-main">{{ channel.campaigns_count ?? 0 }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('channels.edit', channel.id)" class="text-accent-beige hover:underline">Edit</Link>
                                <button type="button" class="ml-3 text-accent-pink hover:underline" @click="destroyChannel(channel.id)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!(channels ?? []).length"><td colspan="4" class="px-6 py-12 text-center text-txt-muted">No channels yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
