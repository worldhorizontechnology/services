<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ customers: Object });
</script>

<template>
    <Head title="Customers" />
    <AppLayout title="Customers">
        <section class="rounded-2xl border border-border bg-panel p-6">
            <p class="mb-4 text-sm text-txt-muted">CRM customer records and acquisition sources.</p>
            <div v-for="customer in customers?.data ?? []" :key="customer.id" class="border-b border-border py-3 last:border-0">
                <Link :href="route('customers.show', customer.id)" class="font-semibold text-txt-main hover:text-accent-beige">
                        {{ customer.first_name }} {{ customer.last_name }}
                    </Link>
                    <span class="ml-3 text-sm text-txt-muted">{{ customer.phone }}</span>
                </div>
        </section>
    </AppLayout>
</template>
