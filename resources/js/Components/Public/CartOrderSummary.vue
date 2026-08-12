<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { IconLock, IconShieldCheck } from '@tabler/icons-vue';

const props = defineProps({
    subtotal: {
        type: Number,
        default: 0,
    },
    itemCount: {
        type: Number,
        default: 0,
    },
    discountAmount: {
        type: Number,
        default: 0,
    },
    promoCode: {
        type: String,
        default: null,
    },
    totalAfterDiscount: {
        type: Number,
        default: null,
    },
});

const isEmpty = computed(() => props.itemCount <= 0);
const hasDiscount = computed(() => Number(props.discountAmount) > 0);

const displayTotal = computed(() => {
    if (props.totalAfterDiscount != null) {
        return Number(props.totalAfterDiscount);
    }

    return Math.max(0, Number(props.subtotal) - Number(props.discountAmount));
});

const formatMoney = (price) => {
    const value = Number(price);

    if (Number.isNaN(value)) {
        return '$0.00';
    }

    return `$${value.toFixed(2)}`;
};
</script>

<template>
    <div class="card-shadow sticky top-28 rounded-xl bg-white p-lg">
        <h2 class="mb-lg font-serif text-headline-md text-primary">
            Order Summary
        </h2>

        <div class="mb-lg space-y-md border-b border-outline-variant pb-lg">
            <div class="flex justify-between font-sans text-body-lg">
                <span class="text-on-surface-variant">Subtotal</span>
                <span class="font-bold text-on-surface">{{ formatMoney(subtotal) }}</span>
            </div>
            <div
                v-if="hasDiscount"
                class="flex justify-between font-sans text-body-lg"
            >
                <span class="text-on-surface-variant">
                    Discount
                    <span v-if="promoCode" class="font-bold text-accent">
                        ({{ promoCode }})
                    </span>
                </span>
                <span class="font-bold text-success">
                    -{{ formatMoney(discountAmount) }}
                </span>
            </div>
            <div class="flex justify-between font-sans text-body-lg">
                <span class="text-on-surface-variant">Delivery</span>
                <span class="font-bold text-secondary">Calculated at checkout</span>
            </div>
        </div>

        <div class="mb-xl flex items-center justify-between">
            <span class="font-serif text-headline-sm text-primary">Total</span>
            <span class="text-3xl font-bold text-secondary-container">
                {{ formatMoney(displayTotal) }}
            </span>
        </div>

        <Link
            v-if="!isEmpty"
            :href="route('checkout')"
            class="flex w-full items-center justify-center gap-sm rounded-full bg-secondary-container py-md font-sans font-bold text-white shadow-md transition-all hover:opacity-95 active:scale-[0.98]"
        >
            <IconLock :size="18" stroke-width="1.5" />
            Proceed to Checkout
        </Link>
        <button
            v-else
            type="button"
            class="flex w-full cursor-not-allowed items-center justify-center gap-sm rounded-full bg-secondary-container py-md font-sans font-bold text-white opacity-50 shadow-md"
            disabled
        >
            <IconLock :size="18" stroke-width="1.5" />
            Proceed to Checkout
        </button>

        <div class="mt-xl text-center">
            <div
                class="mb-sm flex items-center justify-center gap-1 font-sans text-body-sm text-on-surface-variant"
            >
                <IconShieldCheck :size="16" stroke-width="1.5" />
                Cash on delivery available
            </div>
        </div>
    </div>
</template>
