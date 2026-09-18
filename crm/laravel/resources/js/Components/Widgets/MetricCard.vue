<script setup>
defineProps({
    label: String,
    value: [String, Number],
    trend: { type: String, default: '' },
    tone: { type: String, default: 'blue' },
});
</script>

<template>
    <article class="rounded-2xl border border-border bg-panel p-5 shadow-xl">
        <div class="mb-3 flex items-center justify-between text-xs font-semibold uppercase tracking-wider text-txt-muted">
            <span>{{ label }}</span><span class="text-accent-beige">●</span>
        </div>
        <div class="flex items-baseline justify-between gap-3">
            <strong class="text-2xl font-black text-txt-main">{{ value }}</strong>
            <span v-if="trend" class="text-xs font-semibold text-accent-pink">{{ trend }}</span>
        </div>
    </article>
</template>
