<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ campaigns: Array, channels: Array });

function destroyCampaign(id) {
    if (confirm('Delete this campaign?')) {
        router.delete(route('campaigns.destroy', id));
    }
}
</script>

<template>
    <Head title="Campaigns" />
    <AppLayout title="Campaigns & Channels">
        <section class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <p class="text-sm text-txt-muted">Marketing campaigns and their linked channels</p>
                <Link :href="route('campaigns.create')" class="rounded-xl bg-accent-beige px-4 py-2.5 text-sm font-semibold text-main shadow-lg shadow-accent-beige/20">Create campaign</Link>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Campaign</th><th class="px-6 py-4">Channel</th><th class="px-6 py-4">Promo code</th><th class="px-6 py-4 text-right">Budget</th><th class="px-6 py-4 text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="campaign in campaigns ?? []" :key="campaign.id" class="hover:bg-main/30">
                            <td class="px-6 py-4 font-semibold text-txt-main">{{ campaign.name }}</td>
                            <td class="px-6 py-4 text-txt-muted">{{ campaign.channel?.name || 'No channel' }}</td>
                            <td class="px-6 py-4 text-txt-muted">{{ campaign.promo_code || '—' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-txt-main">${{ campaign.budget ?? '0.00' }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('campaigns.edit', campaign.id)" class="text-accent-beige hover:underline">Edit</Link>
                                <button type="button" class="ml-3 text-accent-pink hover:underline" @click="destroyCampaign(campaign.id)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!(campaigns ?? []).length"><td colspan="5" class="px-6 py-12 text-center text-txt-muted">No campaigns yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
