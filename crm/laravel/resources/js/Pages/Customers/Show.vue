<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ customer: Object });
</script>

<template>
    <Head title="Customer" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Customer details</h2></template>
        <main class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <h1 class="text-2xl font-bold">{{ customer.first_name }} {{ customer.last_name }}</h1>
                <p class="mt-3 text-gray-600">{{ customer.phone || 'No phone' }}</p>
                <p class="text-gray-600">{{ customer.email || 'No email' }}</p>
                <Link :href="route('customers.index')" class="mt-6 inline-block text-sm text-indigo-600">Back to customers</Link>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
