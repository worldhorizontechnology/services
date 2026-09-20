<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    order: Object,
    customers: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    campaigns: { type: Array, default: () => [] },
});

const form = useForm({
    customer_id: props.order.customer_id,
    campaign_id: props.order.campaign_id ?? '',
    status: props.order.status,
    payment_status: props.order.payment_status,
    discount_amount: props.order.discount_amount,
    services: props.order.services?.length
        ? props.order.services.map((service) => ({ service_id: service.id, quantity: service.pivot?.quantity ?? 1 }))
        : [{ service_id: '', quantity: 1 }],
});

function addLine() {
    form.services.push({ service_id: '', quantity: 1 });
}

function removeLine(index) {
    form.services.splice(index, 1);
}

const estimatedTotal = computed(() => {
    const subtotal = form.services.reduce((sum, line) => {
        const service = props.services.find((item) => item.id === Number(line.service_id));
        return sum + (service ? Number(service.price) * Number(line.quantity || 0) : 0);
    }, 0);
    return Math.max(0, subtotal - Number(form.discount_amount || 0));
});

const submit = () => form.put(route('orders.update', props.order.id));
</script>

<template>
    <Head title="Edit Order" />
    <AppLayout title="Edit Order">
        <form @submit.prevent="submit" class="max-w-2xl space-y-5 rounded-2xl border border-border bg-panel p-6 shadow-xl">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <InputLabel for="customer_id" value="Customer" />
                    <select id="customer_id" v-model="form.customer_id" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                        <option v-for="customer in customers" :key="customer.id" :value="customer.id">{{ customer.first_name }} {{ customer.last_name }} ({{ customer.phone }})</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.customer_id" />
                </div>
                <div>
                    <InputLabel for="campaign_id" value="Marketing campaign" />
                    <select id="campaign_id" v-model="form.campaign_id" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                        <option value="">No campaign (direct visit)</option>
                        <option v-for="campaign in campaigns" :key="campaign.id" :value="campaign.id">{{ campaign.name }}</option>
                    </select>
                    <InputError class="mt-2" :message="form.errors.campaign_id" />
                </div>
            </div>

            <div>
                <InputLabel value="Services" />
                <div class="mt-1 space-y-3">
                    <div v-for="(line, index) in form.services" :key="index" class="flex items-center gap-3">
                        <select v-model="line.service_id" class="flex-1 rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                            <option value="" disabled>Select a service</option>
                            <option v-for="service in services" :key="service.id" :value="service.id">{{ service.name }} — ${{ service.price }}</option>
                        </select>
                        <input type="number" min="1" v-model="line.quantity" class="w-24 rounded-xl border border-border bg-main px-3 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink" />
                        <button type="button" class="text-accent-pink hover:underline" @click="removeLine(index)" v-if="form.services.length > 1">Remove</button>
                    </div>
                </div>
                <button type="button" class="mt-3 text-sm text-accent-beige hover:underline" @click="addLine">+ Add another service</button>
                <InputError class="mt-2" :message="form.errors.services" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <InputLabel for="discount_amount" value="Discount ($)" />
                    <input id="discount_amount" type="number" step="0.01" v-model="form.discount_amount" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink" />
                    <InputError class="mt-2" :message="form.errors.discount_amount" />
                </div>
                <div>
                    <InputLabel for="status" value="Status" />
                    <select id="status" v-model="form.status" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                        <option value="new">New</option>
                        <option value="in_progress">In progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <InputLabel for="payment_status" value="Payment" />
                    <select id="payment_status" v-model="form.payment_status" class="mt-1 w-full rounded-xl border border-border bg-main px-4 py-2.5 text-txt-main focus:border-accent-pink focus:ring-1 focus:ring-accent-pink">
                        <option value="unpaid">Unpaid</option>
                        <option value="paid">Paid</option>
                        <option value="refunded">Refunded</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between border-t border-border pt-5">
                <span class="text-sm text-txt-muted">Estimated total: <strong class="text-txt-main">${{ estimatedTotal.toFixed(2) }}</strong></span>
                <div class="flex gap-3">
                    <SecondaryButton type="button" @click="$inertia.visit(route('orders.index'))">Cancel</SecondaryButton>
                    <PrimaryButton :disabled="form.processing">Save changes</PrimaryButton>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
