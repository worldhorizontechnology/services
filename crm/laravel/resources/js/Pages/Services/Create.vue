<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
</script>

<template>
	<Head title="Create Service" />
	<AppLayout title="Create Service"><section class="max-w-2xl rounded-2xl border border-slate-800 bg-slate-900 p-6"><h1 class="text-xl font-bold text-white">Create service</h1><p class="mt-2 text-sm text-slate-500">Service form placeholder. The catalog relation is defined through service_user.</p><Link :href="route('services.index')" class="mt-6 inline-block text-sm text-blue-400">Back to catalog</Link></section></AppLayout>
</template>
