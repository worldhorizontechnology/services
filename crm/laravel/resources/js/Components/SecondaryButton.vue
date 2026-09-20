<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-border bg-panel px-4 py-2.5 text-xs font-semibold uppercase tracking-widest text-txt-main shadow-sm transition duration-150 ease-in-out hover:bg-border focus:outline-none focus:ring-2 focus:ring-accent-pink focus:ring-offset-2 focus:ring-offset-panel disabled:opacity-25"
    >
        <slot />
    </button>
</template>
