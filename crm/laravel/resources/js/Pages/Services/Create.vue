<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServiceForm from '@/Components/ServiceForm.vue';
</script>

<template>
    <Head title="Create Service" />
    <AppLayout title="Create Service">
        <ServiceForm @saved="router.visit(route('services.index'))" @cancel="router.visit(route('services.index'))" />
    </AppLayout>
</template>
