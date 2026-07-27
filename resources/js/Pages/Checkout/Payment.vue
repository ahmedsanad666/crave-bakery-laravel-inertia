<script setup>
import { computed } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import StripePayment from '@/Components/Public/Gateways/StripePayment.vue';
import CheckoutLayout from '@/Layouts/CheckoutLayout.vue';

const props = defineProps({
    cart: {
        type: Object,
        required: true,
    },
    totals: {
        type: Object,
        required: true,
    },
    stripe_key: {
        type: String,
        default: '',
    },
});

const page = usePage();
const flashError = computed(() => page.props.flash?.error ?? null);

const formatMoney = (price) => {
    const value = Number(price);

    if (Number.isNaN(value)) {
        return '$0.00';
    }

    return `$${value.toFixed(2)}`;
};

const itemCount = computed(() => Number(props.cart.item_count ?? 0));
</script>

<template>
    <CheckoutLayout>
        <Head title="Complete Payment" />

        <div class="container-page max-w-[640px] py-xxl">
            <div class="mb-xxl flex justify-center">
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <div
                        class="flex items-center gap-2 rounded-full bg-surface-container-high px-6 py-2 text-on-surface-variant"
                    >
                        <span class="font-bold">1.</span>
                        <span class="font-sans text-label-caps uppercase">
                            Delivery
                        </span>
                    </div>
                    <div class="hidden h-px w-12 bg-outline-variant sm:block"></div>
                    <div
                        class="flex items-center gap-2 rounded-full bg-primary-container px-6 py-2 text-on-primary-container"
                    >
                        <span class="font-bold">2.</span>
                        <span class="font-sans text-label-caps uppercase">
                            Payment
                        </span>
                    </div>
                    <div class="hidden h-px w-12 bg-outline-variant sm:block"></div>
                    <div
                        class="flex items-center gap-2 rounded-full bg-surface-container-high px-6 py-2 text-on-surface-variant"
                    >
                        <span class="font-bold">3.</span>
                        <span class="font-sans text-label-caps uppercase">
                            Confirmation
                        </span>
                    </div>
                </div>
            </div>

            <div class="mb-lg text-center">
                <h1 class="font-serif text-headline-md text-primary">
                    Complete Your Payment
                </h1>
                <p class="mt-sm font-sans text-body-sm text-on-surface-variant">
                    {{ itemCount }} item{{ itemCount === 1 ? '' : 's' }} ·
                    {{ formatMoney(totals.total) }}
                </p>
                <p class="mt-xs font-sans text-body-sm text-on-surface-variant">
                    Your cart is held until payment succeeds.
                </p>
            </div>

            <div
                v-if="flashError"
                class="mb-lg rounded-[10px] border border-error/20 bg-error/10 p-4"
            >
                <p class="font-sans text-sm text-error">{{ flashError }}</p>
            </div>

            <div
                class="card-shadow mb-lg rounded-xl border border-outline-variant bg-white p-xl"
            >
                <h2 class="mb-lg font-sans text-title-lg font-semibold text-primary">
                    Payment Details
                </h2>

                <StripePayment
                    :order-total="totals.total"
                    :stripe-key="stripe_key"
                />
            </div>

            <div class="text-center">
                <Link
                    :href="route('checkout')"
                    class="font-sans text-body-sm font-semibold text-primary underline-offset-2 hover:underline"
                >
                    Back to checkout
                </Link>
            </div>
        </div>
    </CheckoutLayout>
</template>
