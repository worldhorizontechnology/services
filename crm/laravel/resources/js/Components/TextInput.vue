<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main placeholder:text-txt-muted focus:border-accent-pink focus:ring-1 focus:ring-accent-pink"
        v-model="model"
        ref="input"
    />
</template>
