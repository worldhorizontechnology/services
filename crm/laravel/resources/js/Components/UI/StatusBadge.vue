<script setup>
defineProps({ status: { type: String, default: 'unknown' } });
</script>

<template>
    <span class="inline-flex rounded-full border border-border bg-main px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-txt-muted">
        {{ status || 'unknown' }}
    </span>
</template>
