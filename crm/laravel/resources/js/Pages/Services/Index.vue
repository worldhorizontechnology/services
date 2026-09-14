<template>
    <MainLayout>
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-2xl font-black text-txt-main">Service Catalog</h1>
                <p class="text-txt-muted text-sm mt-1">Manage available services and pricing</p>
            </div>
            <button 
                @click="showForm = true"
                class="px-5 py-2.5 bg-accent-beige text-main rounded-xl text-sm font-bold hover:bg-txt-main transition-colors"
            >
                + Add Service
            </button>
        </div>

        <!-- Render the grid/table of services here -->
        <ServicesTab v-if="!showForm" :services="services" />

        <!-- Render the form when adding a new service -->
        <div v-else class="flex justify-center mt-10">
            <ServiceForm class="w-full" @cancel="showForm = false" @saved="showForm = false" />
        </div>
    </MainLayout>
</template>

<script setup>
import { ref } from 'vue';
import MainLayout from '@/Layouts/MainLayout.vue';
import ServicesTab from '@/Components/ServicesTab.vue'; // The grid component you have
import ServiceForm from '@/Components/ServiceForm.vue';

defineProps({
    services: Array
});

const showForm = ref(false);
</script>