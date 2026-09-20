<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({ channel: Object });

const form = useForm({
    name: props.channel.name,
    type: props.channel.type,
});

const submit = () => form.put(route('channels.update', props.channel.id));
</script>

<template>
    <Head title="Edit Channel" />
    <AppLayout title="Edit Channel">
        <form @submit.prevent="submit" class="max-w-xl space-y-5 rounded-2xl border border-border bg-panel p-6 shadow-xl">
            <div>
                <InputLabel for="name" value="Channel name" />
                <TextInput id="name" type="text" class="mt-1" v-model="form.name" required />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <InputLabel for="type" value="Type" />
                <select id="type" v-model="form.type" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                </select>
                <InputError class="mt-2" :message="form.errors.type" />
            </div>
            <div class="flex justify-end gap-3 border-t border-border pt-5">
                <SecondaryButton type="button" @click="$inertia.visit(route('channels.index'))">Cancel</SecondaryButton>
                <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>
            </div>
        </form>
    </AppLayout>
</template>
