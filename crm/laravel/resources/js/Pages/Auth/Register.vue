<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ services: { type: Array, default: () => [] } });

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'client',
    phone: '',
    telegram_id: '',
    telegram_username: '',
    calendarId: '',
    instagram_id: '',
    instagram_username: '',
    services: [],
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                <InputLabel for="first_name" value="First name" />

                <TextInput
                    id="first_name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.first_name"
                    required
                    autofocus
                    autocomplete="given-name"
                />

                <InputError class="mt-2" :message="form.errors.first_name" />
                </div>
                <div>
                    <InputLabel for="last_name" value="Last name" />
                    <TextInput id="last_name" type="text" class="mt-1 block w-full" v-model="form.last_name" autocomplete="family-name" />
                    <InputError class="mt-2" :message="form.errors.last_name" />
                </div>
            </div>

            <div class="mt-4">
                <InputLabel for="role" value="Role" />
                <select id="role" v-model="form.role" class="mt-1 block w-full rounded-md border-border bg-main text-txt-main">
                    <option value="client">Client</option><option value="manager">Manager</option><option value="executor">Executor</option><option value="admin">Admin</option>
                </select>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div v-if="form.role === 'executor'" class="mt-4">
                <InputLabel for="services" value="Qualified services" />
                <select id="services" v-model="form.services" multiple class="mt-1 block h-28 w-full rounded-md border-border bg-main text-txt-main">
                    <option v-for="service in props.services" :key="service.id" :value="service.id">{{ service.name }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.services" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    class="ms-4"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
