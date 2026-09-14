<template>
    <div class="min-h-screen bg-main text-txt-main flex">
        <!-- Sidebar / Navigation -->
        <aside class="w-64 bg-panel border-r border-border flex flex-col">
            <div class="h-16 flex items-center px-6 border-b border-border">
                <span class="font-black text-xl text-txt-main tracking-wider">
                    SYS<span class="text-accent-pink">.</span>ADMIN
                </span>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2">
                <Link href="/dashboard" class="nav-link" :class="{ 'active': $page.url === '/dashboard' }">
                    Dashboard
                </Link>
                <Link href="/services" class="nav-link" :class="{ 'active': $page.url.startsWith('/services') }">
                    Services
                </Link>
                <Link href="/users" class="nav-link" :class="{ 'active': $page.url.startsWith('/users') }">
                    Staff
                </Link>
                <Link href="/orders" class="nav-link" :class="{ 'active': $page.url.startsWith('/orders') }">
                    Orders
                </Link>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Header -->
            <header class="h-16 bg-panel border-b border-border flex items-center justify-between px-8">
                <div class="text-txt-muted text-sm font-medium">
                    {{ $page.component }}
                </div>
                
                <div class="flex items-center gap-4">
                    <button class="text-txt-muted hover:text-accent-pink transition-colors">
                        <!-- Notification Icon -->
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </button>
                    <!-- User Avatar/Initials -->
                    <div class="w-8 h-8 rounded-full bg-border flex items-center justify-center text-xs font-bold text-accent-beige">
                        {{ $page.props.auth.user.name.charAt(0) }}
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-8 overflow-y-auto h-[calc(100vh-4rem)]">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<style scoped>
.nav-link {
    display: flex;
    align-items: center;
    padding: 0.625rem 1rem;
    border-radius: 0.75rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #9ca3af;
    transition: all 200ms;
}
.nav-link:hover {
    background-color: #374151;
    color: #f3f4f6;
}
.nav-link.active {
    background-color: rgb(236 72 153 / 10%);
    color: #ec4899;
}
</style>