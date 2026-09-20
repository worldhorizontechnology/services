<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({ user: Object, services: { type: Array, default: () => [] } });

const form = useForm({
    first_name: props.user.first_name,
    last_name: props.user.last_name,
    email: props.user.email,
    role: props.user.role,
    phone: props.user.phone,
    telegram_id: props.user.telegram_id,
    telegram_username: props.user.telegram_username,
    calendarId: props.user.calendarId,
    instagram_id: props.user.instagram_id,
    instagram_username: props.user.instagram_username,
    is_active: props.user.is_active ?? true,
    services: props.user.services?.map((service) => service.id) ?? [],
});

const submit = () => form.put(route('users.update', props.user.id));
</script>

<template>
    <Head title="Edit User" />
    <AppLayout title="Edit User">
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
                <InputLabel for="email" value="Email" />
                <TextInput id="email" type="email" class="mt-1" v-model="form.email" required />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
                <InputLabel for="role" value="Role" />
                <select id="role" v-model="form.role" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="executor">Executor</option>
                    <option value="client">Client</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>
            <div>
                <InputLabel for="phone" value="Phone" />
                <TextInput id="phone" type="text" class="mt-1" v-model="form.phone" />
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>
            <div v-if="form.role === 'executor'">
                <InputLabel value="Qualified services" />
                <select multiple v-model="form.services" class="mt-1 h-32 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                    <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }} — ${{ service.price }}</option>
                </select>
            </div>
            <div class="flex items-center gap-3">
                <input id="is_active" type="checkbox" v-model="form.is_active" class="h-5 w-5 rounded border-border bg-main text-accent-pink focus:ring-accent-pink" />
                <label for="is_active" class="cursor-pointer text-sm font-medium text-txt-main">Active account</label>
            </div>
            <div class="flex justify-end gap-3 border-t border-border pt-5">
                <SecondaryButton type="button" @click="$inertia.visit(route('users.index'))">Cancel</SecondaryButton>
                <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>
            </div>
        </form>
    </AppLayout>
</template>
