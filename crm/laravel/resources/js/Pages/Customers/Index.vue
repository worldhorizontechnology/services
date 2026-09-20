<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ customers: Object });

function destroyCustomer(id) {
    if (confirm('Delete this customer?')) {
        router.delete(route('customers.destroy', id));
    }
}
</script>

<template>
    <Head title="Customers" />
    <AppLayout title="Customers">
        <section class="space-y-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <p class="text-sm text-txt-muted">CRM customer records and acquisition sources.</p>
                <Link :href="route('customers.create')" class="rounded-xl bg-accent-beige px-4 py-2.5 text-sm font-semibold text-main shadow-lg shadow-accent-beige/20">Add customer</Link>
            </div>
            <div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
                        <tr><th class="px-6 py-4">Customer</th><th class="px-6 py-4">Contacts</th><th class="px-6 py-4">Channel</th><th class="px-6 py-4">Campaign</th><th class="px-6 py-4 text-right">Action</th></tr>
                    </thead>
                    <tbody class="divide-y divide-border/70">
                        <tr v-for="customer in customers?.data ?? []" :key="customer.id" class="hover:bg-main/30">
                            <td class="px-6 py-4">
                                <Link :href="route('customers.show', customer.id)" class="font-semibold text-txt-main hover:text-accent-beige">{{ customer.first_name }} {{ customer.last_name }}</Link>
                            </td>
                            <td class="px-6 py-4 text-txt-muted">
                                <div>{{ customer.phone || '—' }}</div>
                                <div class="text-xs">{{ customer.email || '' }}</div>
                            </td>
                            <td class="px-6 py-4"><span v-if="customer.channel" class="rounded border border-accent-beige/20 bg-accent-beige/10 px-2.5 py-1 text-xs text-accent-beige">{{ customer.channel.name }}</span><span v-else class="text-txt-muted">—</span></td>
                            <td class="px-6 py-4 text-txt-muted">{{ customer.campaign?.name || '—' }}</td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="route('customers.edit', customer.id)" class="text-accent-beige hover:underline">Edit</Link>
                                <button type="button" class="ml-3 text-accent-pink hover:underline" @click="destroyCustomer(customer.id)">Delete</button>
                            </td>
                        </tr>
                        <tr v-if="!(customers?.data ?? []).length"><td colspan="5" class="px-6 py-12 text-center text-txt-muted">No customers yet.</td></tr>
                    </tbody>
                </table>
            </div>
        </section>
    </AppLayout>
</template>
