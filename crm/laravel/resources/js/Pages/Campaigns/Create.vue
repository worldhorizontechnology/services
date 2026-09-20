<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({ channels: { type: Array, default: () => [] } });

const form = useForm({
    channel_id: '',
    name: '',
    promo_code: '',
    budget: '',
});

const submit = () => form.post(route('campaigns.store'));
</script>

<template>
    <Head title="Create Campaign" />
    <AppLayout title="Create Campaign">
        <form @submit.prevent="submit" class="max-w-xl space-y-5 rounded-2xl border border-border bg-panel p-6 shadow-xl">
            <div>
                <InputLabel for="channel_id" value="Channel" />
                <select id="channel_id" v-model="form.channel_id" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                    <option value="" disabled>Select a channel</option>
                    <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.channel_id" />
            </div>
            <div>
                <InputLabel for="name" value="Campaign name" />
                <TextInput id="name" type="text" class="mt-1" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="promo_code" value="Promo code" />
                <TextInput id="promo_code" type="text" class="mt-1" v-model="form.promo_code" />
                <InputError class="mt-2" :message="form.errors.promo_code" />
            </div>
            <div>
                <InputLabel for="budget" value="Budget ($)" />
                <TextInput id="budget" type="number" step="0.01" class="mt-1" v-model="form.budget" />
                <InputError class="mt-2" :message="form.errors.budget" />
            </div>
            <div class="flex justify-end gap-3 border-t border-border pt-5">
                <SecondaryButton type="button" @click="$inertia.visit(route('campaigns.index'))">Cancel</SecondaryButton>
                <PrimaryButton :disabled="form.processing">Save campaign</PrimaryButton>
            </div>
        </form>
    </AppLayout>
</template>
