<script setup>
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import Footer from '@/Components/Layout/Footer.vue';

defineProps({
    title: { type: String, default: 'ApexCRM' },
});
</script>

<template>
    <div class="min-h-screen bg-main text-txt-main lg:flex">
        <Sidebar />
        <main class="flex min-h-screen min-w-0 flex-1 flex-col bg-main">
            <Header :title="title">
                <template #actions><slot name="actions" /></template>
            </Header>
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
                <slot />
            </div>
            <Footer />
        </main>
    </div>
</template>
