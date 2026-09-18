<script setup>
import { Link } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';

const links = [
    { label: 'Marketing & Analytics', route: 'analytics.index', icon: '▥' },
    { label: 'Orders & Execution', route: 'orders.index', icon: '▣' },
    { label: 'Services', route: 'services.index', icon: '◈' },
    { label: 'Customers', route: 'customers.index', icon: '♙' },
    { label: 'Team', route: 'users.index', icon: '✓' },
    { label: 'Campaigns', route: 'campaigns.index', icon: '◆' },
    { label: 'Channels', route: 'channels.index', icon: '⌁' },
];
</script>

<template>
    <aside class="flex w-full shrink-0 flex-col border-b border-border bg-panel lg:min-h-screen lg:w-64 lg:border-b-0 lg:border-r">
        <div class="flex h-16 items-center gap-3 border-b border-border px-5">
            <ApplicationLogo class="h-9 w-9 fill-current text-accent-beige" />
            <span class="text-lg font-bold tracking-wide text-txt-main">CRM</span>
        </div>
        <nav class="flex gap-1 overflow-x-auto p-3 lg:block lg:space-y-1">
            <Link
                v-for="link in links"
                :key="link.route"
                :href="route(link.route)"
                class="flex shrink-0 items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-txt-muted transition hover:bg-main hover:text-txt-main"
                :class="route().current(link.route) ? 'bg-accent-beige text-main shadow-lg shadow-accent-beige/20' : ''"
            >
                <span class="w-5 text-center text-base">{{ link.icon }}</span>
                <span>{{ link.label }}</span>
            </Link>
        </nav>
        <div class="mt-auto hidden items-center gap-3 border-t border-border p-4 lg:flex">
            <div class="flex h-9 w-9 items-center justify-center rounded-full border border-border bg-main text-xs font-bold text-accent-beige">AD</div>
            <div class="min-w-0">
                <p class="truncate text-sm font-medium text-txt-main">{{ $page.props.auth?.user?.name || 'Admin User' }}</p>
                <p class="text-xs text-txt-muted">Administrator</p>
            </div>
        </div>
    </aside>
</template>
