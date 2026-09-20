<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

defineProps({ channels: { type: Array, default: () => [] }, campaigns: { type: Array, default: () => [] } });

const form = useForm({
    first_name: '',
    last_name: '',
    phone: '',
    email: '',
    channel_id: '',
    campaign_id: '',
    entry_point: '',
});

const submit = () => form.post(route('customers.store'));
</script>

<template>
    <Head title="Create Customer" />
    <AppLayout title="Create Customer">
        <form @submit.prevent="submit" class="max-w-xl space-y-5 rounded-2xl border border-border bg-panel p-6 shadow-xl">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="first_name" value="First name" />
                    <TextInput id="first_name" type="text" class="mt-1" v-model="form.first_name" required />
                    <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div>
                    <InputLabel for="last_name" value="Last name" />
                    <TextInput id="last_name" type="text" class="mt-1" v-model="form.last_name" />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>
            <div>
                <InputLabel for="phone" value="Phone" />
                <TextInput id="phone" type="text" class="mt-1" v-model="form.phone" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>
            <div>
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1" v-model="form.email" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="channel_id" value="Channel" />
                    <select id="channel_id" v-model="form.channel_id" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                        <option value="">No channel</option>
                        <option v-for="channel in channels" :key="channel.id" :value="channel.id">{{ channel.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.channel_id" />
                </div>
                <div>
                    <InputLabel for="campaign_id" value="Campaign" />
                    <select id="campaign_id" v-model="form.campaign_id" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                        <option value="">No campaign</option>
                        <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">{{ campaign.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.campaign_id" />
                </div>
            </div>
            <div>
                <InputLabel for="entry_point" value="Entry point" />
                <TextInput id="entry_point" type="text" class="mt-1" v-model="form.entry_point" placeholder="e.g. promo code SUMMER26" />
                <InputError class="mt-2" :message="form.errors.entry_point" />
            </div>
            <div class="flex justify-end gap-3 border-t border-border pt-5">
                <SecondaryButton type="button" @click="$inertia.visit(route('customers.index'))">Cancel</SecondaryButton>
                <PrimaryButton :disabled="form.processing">Save customer</PrimaryButton>
            </div>
        </form>
    </AppLayout>
</template>
