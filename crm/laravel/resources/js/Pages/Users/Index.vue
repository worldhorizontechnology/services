<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({ users: { type: Array, default: () => [] } });
</script>

<template>
	<Head title="Team" />
	<AppLayout title="Team & Executors">
		<section class="space-y-6">
			<div class="flex flex-wrap items-end justify-between gap-4">
				<p class="text-sm text-txt-muted">Users, roles, workload and service qualifications.</p>
				<Link :href="route('users.create')" class="rounded-xl bg-accent-beige px-4 py-2.5 text-sm font-semibold text-main shadow-lg shadow-accent-beige/20">Add teammate</Link>
			</div>
			<div class="overflow-hidden rounded-2xl border border-border bg-panel shadow-xl">
				<table class="w-full text-left text-sm">
					<thead class="border-b border-border bg-main/50 text-xs uppercase tracking-wide text-txt-muted">
						<tr><th class="px-6 py-4">Name</th><th class="px-6 py-4">Email</th><th class="px-6 py-4">Role</th><th class="px-6 py-4">Active orders</th><th class="px-6 py-4 text-right">Action</th></tr>
					</thead>
					<tbody class="divide-y divide-border/70">
						<tr v-for="user in users" :key="user.id" class="hover:bg-main/30">
							<td class="px-6 py-4 font-semibold text-txt-main">{{ user.first_name }} {{ user.last_name }}</td>
							<td class="px-6 py-4 text-txt-muted">{{ user.email }}</td>
							<td class="px-6 py-4"><span class="rounded-full border border-accent-beige/30 bg-accent-beige/10 px-2.5 py-1 text-xs font-semibold uppercase text-accent-beige">{{ user.role }}</span></td>
							<td class="px-6 py-4 text-txt-main">{{ user.assignments_count ?? 0 }}</td>
							<td class="px-6 py-4 text-right"><Link :href="route('users.show', user.id)" class="text-accent-beige hover:underline">View</Link></td>
						</tr>
						<tr v-if="!users.length"><td colspan="5" class="px-6 py-12 text-center text-txt-muted">No team members yet.</td></tr>
					</tbody>
				</table>
			</div>
		</section>
	</AppLayout>
</template>
