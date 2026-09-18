<script setup>
defineProps({ title: { type: String, default: 'ApexCRM' } });
</script>

<template>
    <header class="sticky top-0 z-10 flex min-h-16 items-center justify-between gap-4 border-b border-border bg-panel/80 px-4 backdrop-blur-md sm:px-6 lg:px-8">
        <h1 class="text-lg font-bold tracking-tight text-txt-main sm:text-xl">{{ title }}</h1>
        <div class="flex items-center gap-3">
            <div class="hidden rounded-lg border border-border bg-main p-1 text-xs sm:flex">
                <button class="rounded-md bg-border px-3 py-1.5 font-medium text-txt-main">Today</button>
                <button class="px-3 py-1.5 text-txt-muted hover:text-txt-main">7 Days</button>
                <button class="px-3 py-1.5 text-txt-muted hover:text-txt-main">30 Days</button>
            </div>
            <slot name="actions" />
        </div>
    </header>
</template>
