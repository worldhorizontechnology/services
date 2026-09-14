<template>
    <form @submit.prevent="submit" class="bg-panel border border-border p-6 rounded-2xl max-w-lg">
        <h3 class="text-lg font-bold mb-6 text-txt-main">
            {{ isEditing ? 'Edit Service' : 'New Service' }}
        </h3>

        <div class="space-y-5">
            <!-- Service Name -->
            <div>
                <label class="block text-xs font-medium text-txt-muted mb-1.5 uppercase tracking-wide">
                    Service Name
                </label>
                <input 
                    v-model="form.name" 
                    type="text" 
                    class="w-full bg-main border border-border rounded-xl px-4 py-2.5 text-txt-main focus:ring-1 focus:ring-accent-pink focus:border-accent-pink transition-colors"
                    placeholder="e.g. Security Audit"
                >
                <div v-if="form.errors.name" class="text-accent-pink text-xs mt-1">{{ form.errors.name }}</div>
            </div>

            <!-- Price -->
            <div>
                <label class="block text-xs font-medium text-txt-muted mb-1.5 uppercase tracking-wide">
                    Price ($)
                </label>
                <input 
                    v-model="form.price" 
                    type="number" 
                    step="0.01"
                    class="w-full bg-main border border-border rounded-xl px-4 py-2.5 text-txt-main focus:ring-1 focus:ring-accent-pink focus:border-accent-pink transition-colors"
                    placeholder="0.00"
                >
                <div v-if="form.errors.price" class="text-accent-pink text-xs mt-1">{{ form.errors.price }}</div>
            </div>

            <!-- Is Active Toggle -->
            <div class="flex items-center gap-3 pt-2">
                <input 
                    v-model="form.is_active" 
                    type="checkbox" 
                    id="is_active"
                    class="w-5 h-5 rounded border-border bg-main text-accent-pink focus:ring-accent-pink focus:ring-offset-panel"
                >
                <label for="is_active" class="text-sm font-medium text-txt-main cursor-pointer">
                    Service is active
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-8 flex items-center justify-end gap-3 pt-5 border-t border-border">
            <button 
                type="button" 
                @click="$emit('cancel')"
                class="px-5 py-2.5 rounded-xl text-sm font-medium text-txt-muted hover:bg-border transition-colors"
            >
                Cancel
            </button>
            <button 
                type="submit" 
                :disabled="form.processing"
                class="px-5 py-2.5 bg-accent-pink text-main rounded-xl text-sm font-medium hover:bg-accent-pink-hover transition-colors disabled:opacity-50"
            >
                {{ form.processing ? 'Saving...' : 'Save' }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    service: {
        type: Object,
        default: () => ({ name: '', price: '', is_active: true })
    },
    isEditing: Boolean
});

const emit = defineEmits(['cancel', 'saved']);

const form = useForm({
    name: props.service.name,
    price: props.service.price,
    is_active: props.service.is_active,
});

const submit = () => {
    if (props.isEditing) {
        form.put(`/services/${props.service.id}`, {
            onSuccess: () => emit('saved')
        });
    } else {
        form.post('/services', {
            onSuccess: () => {
                form.reset();
                emit('saved');
            }
        });
    }
};
</script>