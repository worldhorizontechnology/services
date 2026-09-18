<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ customer: Object });
</script>

<template>
    <Head title="Customer" />
    <AppLayout title="Customer Profile">
        <main class="max-w-3xl">
            <div class="rounded-2xl border border-border bg-panel p-6 shadow-xl">
                <h1 class="text-2xl font-bold text-txt-main">{{ customer.first_name }} {{ customer.last_name }}</h1>
                <p class="mt-3 text-txt-muted">{{ customer.phone || 'No phone' }}</p>
                <p class="text-txt-muted">{{ customer.email || 'No email' }}</p>
                <Link :href="route('customers.index')" class="mt-6 inline-block text-sm text-accent-beige">Back to customers</Link>
            </div>
        </main>
    </AppLayout>
</template>
