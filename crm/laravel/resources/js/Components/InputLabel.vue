<script setup>
defineProps({
    value: {
        type: String,
    },
});
</script>

<template>
    <label class="block text-xs font-medium uppercase tracking-wide text-txt-muted">
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
