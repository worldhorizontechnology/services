<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
defineProps({ user: Object });
</script>
<template><Head title="Edit User" /><AppLayout title="Edit User"><section class="rounded-2xl border border-slate-800 bg-slate-900 p-6 text-slate-400">User edit form placeholder for {{ user?.email }}.</section></AppLayout></template>
