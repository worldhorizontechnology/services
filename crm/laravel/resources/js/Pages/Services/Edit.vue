<script setup>
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServiceForm from '@/Components/ServiceForm.vue';

defineProps({ service: Object });
</script>

<template>
    <Head title="Edit Service" />
    <AppLayout title="Edit Service">
        <ServiceForm
            :service="service"
            is-editing
            @saved="router.visit(route('services.index'))"
            @cancel="router.visit(route('services.index'))"
        />
    </AppLayout>
</template>
