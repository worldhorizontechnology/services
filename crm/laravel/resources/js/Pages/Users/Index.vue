<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ users: { type: Array, default: () => [] } });
</script>

<template>
	<Head title="Team" />
	<AppLayout title="Team & Executors"><section class="space-y-6"><p class="text-sm text-slate-500">Users, roles, workload and service qualifications.</p><div class="overflow-hidden rounded-2xl border border-slate-800 bg-slate-900"><table class="w-full text-left text-sm"><thead class="border-b border-slate-800 bg-slate-950/50 text-xs uppercase text-slate-500"><tr><th class="px-6 py-4">Name</th><th class="px-6 py-4">Email</th><th class="px-6 py-4">Role</th></tr></thead><tbody class="divide-y divide-slate-800"><tr v-for="user in users" :key="user.id"><td class="px-6 py-4 font-semibold text-white">{{ user.first_name }} {{ user.last_name }}</td><td class="px-6 py-4 text-slate-400">{{ user.email }}</td><td class="px-6 py-4 text-slate-300">{{ user.role }}</td></tr></tbody></table></div></section></AppLayout>
</template>
