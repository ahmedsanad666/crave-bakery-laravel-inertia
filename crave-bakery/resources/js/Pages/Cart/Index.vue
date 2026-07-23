<script setup>
import { computed, ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconCircleCheck,
    IconShoppingCartOff,
} from '@tabler/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CartItemRow from '@/Components/Public/CartItemRow.vue';
import CartOrderSummary from '@/Components/Public/CartOrderSummary.vue';

const props = defineProps({
    cart: {
        type: Object,
        default: () => ({
            items: [],
            item_count: 0,
            subtotal: 0,
        }),
    },
});

const promoCode = ref('');
const promoNotice = ref('');

const items = computed(() => props.cart.items ?? []);
const itemCount = computed(() => Number(props.cart.item_count ?? 0));
const subtotal = computed(() => Number(props.cart.subtotal ?? 0));
const isEmpty = computed(() => items.value.length === 0);

const itemCountLabel = computed(() => {
    const count = itemCount.value;

    if (count === 1) {
        return '1 item in your basket';
    }

    return `${count} items in your basket`;
});

const handleApplyPromo = () => {
    promoNotice.value = 'Promo codes coming soon.';
};
</script>

<template>
    <AppLayout>
        <Head title="Your Cart" />

        <main class="container-page max-w-[1200px] py-xxl">
            <div class="mb-xl">
                <h1
                    class="font-serif text-display-lg-mobile text-primary md:text-display-lg"
                >
                    Your Cart
                </h1>
                <p class="font-sans text-body-lg text-on-surface-variant">
                    {{ itemCountLabel }}
                </p>
            </div>

            <div class="flex flex-col gap-lg lg:flex-row">
                <!-- Left: items -->
                <div class="space-y-lg lg:w-[65%]">
                    <div
                        v-if="!isEmpty"
                        class="card-shadow overflow-hidden rounded-xl bg-white"
                    >
                        <CartItemRow
                            v-for="(item, index) in items"
                            :key="item.id"
                            :item="item"
                            :is-last="index === items.length - 1"
                        />
                    </div>

                    <div
                        v-if="!isEmpty"
                        class="flex flex-col items-start justify-between gap-md md:flex-row md:items-center"
                    >
                        <Link
                            :href="route('products.index')"
                            class="flex items-center gap-sm font-sans font-bold text-primary hover:underline"
                        >
                            <IconArrowLeft :size="18" stroke-width="1.5" />
                            Continue Shopping
                        </Link>

                        <div class="w-full md:w-auto">
                            <label
                                class="mb-xs block font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Have a promo code?
                            </label>
                            <div class="flex gap-sm">
                                <input
                                    v-model="promoCode"
                                    type="text"
                                    placeholder="Enter code"
                                    class="h-12 flex-grow rounded-lg border border-outline-variant px-md font-sans focus:border-primary focus:outline-none md:w-48"
                                />
                                <button
                                    type="button"
                                    class="h-12 rounded-full bg-primary px-lg font-sans font-bold text-white transition-all hover:opacity-90"
                                    @click="handleApplyPromo"
                                >
                                    Apply
                                </button>
                            </div>
                            <p
                                v-if="promoNotice"
                                class="mt-xs flex items-center gap-1 font-sans text-body-sm font-bold text-secondary"
                                role="status"
                            >
                                <IconCircleCheck :size="16" stroke-width="1.5" />
                                {{ promoNotice }}
                            </p>
                        </div>
                    </div>

                    <!-- Empty / upsell -->
                    <div
                        class="mt-xxl flex flex-col items-center rounded-xl border-2 border-dashed border-outline-variant bg-surface-container-lowest/50 p-xl text-center"
                        :class="isEmpty ? 'mt-0' : ''"
                    >
                        <IconShoppingCartOff
                            :size="64"
                            stroke-width="1"
                            class="mb-md text-outline-variant"
                        />
                        <h4 class="font-serif text-headline-sm text-on-surface-variant">
                            {{
                                isEmpty
                                    ? 'Your basket is empty'
                                    : 'Looking for more treats?'
                            }}
                        </h4>
                        <p
                            class="mb-lg max-w-sm font-sans text-body-sm text-on-surface-variant"
                        >
                            {{
                                isEmpty
                                    ? 'Add something freshly baked from the catalogue to get started.'
                                    : 'Your basket is looking a bit lonely. Explore our freshly baked collection.'
                            }}
                        </p>
                        <Link
                            :href="route('products.index')"
                            class="rounded-full border border-primary px-xl py-md font-sans font-bold text-primary transition-all hover:bg-primary hover:text-white"
                        >
                            Browse Catalogue
                        </Link>
                    </div>
                </div>

                <!-- Right: summary -->
                <div class="lg:w-[35%]">
                    <CartOrderSummary
                        :subtotal="subtotal"
                        :item-count="itemCount"
                    />
                </div>
            </div>
        </main>
    </AppLayout>
</template>
