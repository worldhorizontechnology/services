<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
defineProps({ user: Object });
</script>
<template><Head title="Team Member" /><AppLayout title="Team Member"><section class="rounded-2xl border border-slate-800 bg-slate-900 p-6"><h1 class="text-xl font-bold text-white">{{ user?.first_name }} {{ user?.last_name }}</h1><p class="mt-2 text-slate-400">{{ user?.role }} · {{ user?.email }}</p></section></AppLayout></template>
