<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({ services: { type: Array, default: () => [] } });

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    role: 'executor',
    phone: '',
    telegram_id: '',
    telegram_username: '',
    calendarId: '',
    instagram_id: '',
    instagram_username: '',
    services: [],
});

const submit = () => form.post(route('users.store'));
</script>

<template>
    <Head title="Create User" />
    <AppLayout title="Create User">
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
                <InputLabel for="password" value="Password" />
                <TextInput id="password" type="password" class="mt-1" v-model="form.password" required />
                <InputError class="mt-2" :message="form.errors.password" />
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
                <p class="mt-1 text-[10px] text-txt-muted">Hold Ctrl / Cmd to select multiple services</p>
            </div>
            <div class="flex justify-end gap-3 border-t border-border pt-5">
                <SecondaryButton type="button" @click="$inertia.visit(route('users.index'))">Cancel</SecondaryButton>
                <PrimaryButton :disabled="form.processing">Save teammate</PrimaryButton>
            </div>
        </form>
    </AppLayout>
</template>
