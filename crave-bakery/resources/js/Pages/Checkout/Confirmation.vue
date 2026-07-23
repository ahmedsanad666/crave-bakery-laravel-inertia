<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import CheckoutLayout from '@/Layouts/CheckoutLayout.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const formatMoney = (price) => {
    const value = Number(price);

    if (Number.isNaN(value)) {
        return '$0.00';
    }

    return `$${value.toFixed(2)}`;
};

const etaLabel = computed(() => {
    if (!props.order.estimated_delivery_at) {
        return 'Soon';
    }

    return new Date(props.order.estimated_delivery_at).toLocaleDateString(
        undefined,
        {
            weekday: 'short',
            month: 'short',
            day: 'numeric',
        },
    );
});

const paymentLabel = computed(() => {
    if (props.order.payment_method === 'cod') {
        return 'Cash on Delivery';
    }

    return props.order.payment_method || 'Pending';
});
</script>

<template>
    <CheckoutLayout>
        <Head :title="`Order ${order.order_number}`" />

        <div class="container-page max-w-[1200px] py-xxl">
            <!-- Stepper: confirmation active -->
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
                        class="flex items-center gap-2 rounded-full bg-surface-container-high px-6 py-2 text-on-surface-variant"
                    >
                        <span class="font-bold">2.</span>
                        <span class="font-sans text-label-caps uppercase">
                            Payment
                        </span>
                    </div>
                    <div class="hidden h-px w-12 bg-outline-variant sm:block"></div>
                    <div
                        class="flex items-center gap-2 rounded-full bg-primary-container px-6 py-2 text-on-primary-container"
                    >
                        <span class="font-bold">3.</span>
                        <span class="font-sans text-label-caps uppercase">
                            Confirmation
                        </span>
                    </div>
                </div>
            </div>

            <div class="mx-auto max-w-2xl">
                <div
                    class="card-shadow rounded-xl border border-outline-variant bg-white p-xxl text-center"
                >
                    <div
                        class="mx-auto mb-lg flex h-24 w-24 items-center justify-center rounded-full bg-primary-container/10"
                    >
                        <svg
                            class="h-16 w-16 text-secondary"
                            fill="none"
                            stroke="currentColor"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="3"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <polyline
                                class="animate-check"
                                points="20 6 9 17 4 12"
                            />
                        </svg>
                    </div>

                    <h1
                        class="mb-sm font-serif text-headline-lg text-primary"
                    >
                        Order Confirmed!
                    </h1>
                    <p class="mb-xl font-sans text-body-lg text-on-surface-variant">
                        Thank you for your purchase. We've sent a confirmation
                        email to
                        <strong class="text-on-surface">{{ order.email }}</strong>.
                        Pay with cash when your order arrives.
                    </p>

                    <div
                        class="mb-xxl grid grid-cols-1 gap-xl rounded-xl bg-surface-container-low p-xl text-left sm:grid-cols-2"
                    >
                        <div>
                            <p
                                class="mb-xs font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Order Number
                            </p>
                            <p class="font-sans text-title-lg text-primary">
                                {{ order.order_number }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="mb-xs font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Estimated Delivery
                            </p>
                            <p class="font-sans text-title-lg text-primary">
                                {{ etaLabel }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="mb-xs font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Payment
                            </p>
                            <p class="font-sans text-title-lg text-primary">
                                {{ paymentLabel }}
                            </p>
                        </div>
                        <div>
                            <p
                                class="mb-xs font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Total
                            </p>
                            <p class="font-sans text-title-lg text-primary">
                                {{ formatMoney(order.total) }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center gap-md sm:flex-row">
                        <Link
                            :href="route('orders.show', order.id)"
                            class="inline-flex h-12 items-center justify-center rounded-full bg-primary px-xl font-sans font-bold text-on-primary transition-transform hover:scale-[1.02]"
                        >
                            View Order
                        </Link>
                        <Link
                            :href="route('products.index')"
                            class="inline-flex h-12 items-center justify-center rounded-full border-2 border-primary px-xl font-sans font-bold text-primary transition-colors hover:bg-surface-container-low"
                        >
                            Continue Shopping
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </CheckoutLayout>
</template>
