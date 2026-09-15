<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

defineProps({ customers: Object });
</script>

<template>
    <Head title="Customers" />
    <AuthenticatedLayout>
        <template #header><h2 class="text-xl font-semibold text-gray-800">Customers</h2></template>
        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="rounded-lg bg-white p-6 shadow-sm">
                <p class="mb-4 text-sm text-gray-600">CRM customer records for agent testing.</p>
                <div v-for="customer in customers?.data ?? []" :key="customer.id" class="border-b py-3 last:border-0">
                    <Link :href="route('customers.show', customer.id)" class="font-semibold text-gray-900 hover:text-indigo-600">
                        {{ customer.first_name }} {{ customer.last_name }}
                    </Link>
                    <span class="ml-3 text-sm text-gray-500">{{ customer.phone }}</span>
                </div>
            </div>
        </main>
    </AuthenticatedLayout>
</template>
