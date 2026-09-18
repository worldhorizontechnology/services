<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center bg-main px-4 py-8 text-txt-main sm:justify-center">
        <div>
            <Link href="/">
                <ApplicationLogo class="h-20 w-20 fill-current text-accent-beige" />
            </Link>
        </div>

        <div class="mt-6 w-full max-w-md overflow-hidden rounded-2xl border border-border bg-panel px-6 py-6 shadow-2xl">
            <slot />
        </div>
    </div>
</template>
