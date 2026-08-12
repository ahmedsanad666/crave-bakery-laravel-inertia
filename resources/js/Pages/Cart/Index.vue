<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconArrowRight,
    IconCircleCheck,
    IconShoppingCartOff,
    IconX,
} from '@tabler/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CartItemRow from '@/Components/Public/CartItemRow.vue';
import CartOrderSummary from '@/Components/Public/CartOrderSummary.vue';
import PromoCodeCard from '@/Components/Public/PromoCodeCard.vue';

const props = defineProps({
    cart: {
        type: Object,
        default: () => ({
            items: [],
            item_count: 0,
            subtotal: 0,
            promo_code: null,
            discount_amount: 0,
            total_after_discount: 0,
        }),
    },
    promoCodes: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const brandName = computed(
    () =>
        page.props.siteSettings?.site_name?.trim()
        || page.props.siteSettings?.name?.trim()
        || 'Crave Bakery',
);

const promoForm = useForm({
    promo_code: props.cart.promo_code ?? '',
});

watch(
    () => props.cart.promo_code,
    (code) => {
        promoForm.promo_code = code ?? '';
        promoForm.clearErrors();
    },
);

const items = computed(() => props.cart.items ?? []);
const itemCount = computed(() => Number(props.cart.item_count ?? 0));
const subtotal = computed(() => Number(props.cart.subtotal ?? 0));
const discountAmount = computed(() => Number(props.cart.discount_amount ?? 0));
const totalAfterDiscount = computed(() =>
    Number(props.cart.total_after_discount ?? subtotal.value),
);
const appliedPromo = computed(() => props.cart.promo_code ?? null);
const isEmpty = computed(() => items.value.length === 0);
const hasPromoList = computed(() => (props.promoCodes?.length ?? 0) > 0);

const promoTrack = ref(null);

const itemCountLabel = computed(() => {
    const count = itemCount.value;

    if (count === 1) {
        return '1 item in your basket';
    }

    return `${count} items in your basket`;
});

const handleApplyPromo = (code = null) => {
    if (typeof code === 'string' && code.trim() !== '') {
        promoForm.promo_code = code.trim().toUpperCase();
    }

    promoForm.post(route('cart.promo.apply'), {
        preserveScroll: true,
    });
};

const handleRemovePromo = () => {
    router.delete(route('cart.promo.remove'), {
        preserveScroll: true,
    });
};

const scrollPromos = (direction) => {
    const el = promoTrack.value;
    if (!el) {
        return;
    }

    const amount = Math.min(el.clientWidth * 0.85, 420);
    el.scrollBy({ left: direction * amount, behavior: 'smooth' });
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
                                    v-model="promoForm.promo_code"
                                    type="text"
                                    placeholder="Enter code"
                                    class="h-12 flex-grow rounded-lg border border-outline-variant px-md font-sans uppercase focus:border-primary focus:outline-none md:w-48"
                                    :class="{
                                        'border-error': Boolean(
                                            promoForm.errors.promo_code,
                                        ),
                                    }"
                                    :disabled="promoForm.processing"
                                    @keyup.enter="handleApplyPromo()"
                                />
                                <button
                                    type="button"
                                    class="h-12 rounded-full bg-primary px-lg font-sans font-bold text-white transition-all hover:opacity-90 disabled:opacity-50"
                                    :disabled="promoForm.processing"
                                    @click="handleApplyPromo()"
                                >
                                    Apply
                                </button>
                            </div>
                            <p
                                v-if="promoForm.errors.promo_code"
                                class="mt-xs font-sans text-body-sm font-bold text-error"
                            >
                                {{ promoForm.errors.promo_code }}
                            </p>
                            <div
                                v-else-if="appliedPromo"
                                class="mt-xs flex flex-wrap items-center gap-2 font-sans text-body-sm font-bold text-secondary"
                                role="status"
                            >
                                <IconCircleCheck :size="16" stroke-width="1.5" />
                                <span>{{ appliedPromo }} applied</span>
                                <button
                                    type="button"
                                    class="inline-flex items-center gap-0.5 text-error hover:underline"
                                    @click="handleRemovePromo"
                                >
                                    <IconX :size="14" stroke-width="1.5" />
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Available promo codes -->
                    <section
                        v-if="!isEmpty && hasPromoList"
                        class="space-y-md"
                    >
                        <div class="flex items-end justify-between gap-md">
                            <div>
                                <h2 class="font-serif text-headline-sm text-primary">
                                    Available promo codes
                                </h2>
                                <div class="mt-xs h-1 w-16 rounded-full bg-accent" />
                            </div>
                            <div class="flex items-center gap-sm">
                                <button
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-outline text-primary transition-all hover:bg-primary hover:text-white"
                                    aria-label="Previous promo codes"
                                    @click="scrollPromos(-1)"
                                >
                                    <IconArrowLeft :size="18" stroke-width="1.5" />
                                </button>
                                <button
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-full border border-outline text-primary transition-all hover:bg-primary hover:text-white"
                                    aria-label="Next promo codes"
                                    @click="scrollPromos(1)"
                                >
                                    <IconArrowRight :size="18" stroke-width="1.5" />
                                </button>
                            </div>
                        </div>

                        <div
                            ref="promoTrack"
                            class="no-scrollbar flex snap-x snap-mandatory gap-md overflow-x-auto scroll-smooth pb-sm"
                            style="touch-action: pan-x"
                        >
                            <PromoCodeCard
                                v-for="promo in promoCodes"
                                :key="promo.id"
                                :promo="promo"
                                :brand="brandName"
                                notch-class="bg-surface"
                                show-apply
                                @apply="handleApplyPromo"
                            />
                        </div>
                    </section>

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
                        :discount-amount="discountAmount"
                        :promo-code="appliedPromo"
                        :total-after-discount="totalAfterDiscount"
                    />
                </div>
            </div>
        </main>
    </AppLayout>
</template>
