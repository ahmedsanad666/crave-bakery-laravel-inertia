<script setup>
import { computed, ref, watch } from 'vue';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    IconChevronDown,
    IconChevronUp,
    IconCreditCard,
    IconMapPin,
    IconPlus,
    IconStar,
    IconTruck,
} from '@tabler/icons-vue';
import AddressFormModal from '@/Components/Public/AddressFormModal.vue';
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
    addresses: {
        type: Array,
        default: () => [],
    },
    default_address_id: {
        type: Number,
        default: null,
    },
    prefill: {
        type: Object,
        default: () => ({}),
    },
    paymentMethods: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const itemsExpanded = ref(true);
const quoting = ref(false);
const addressModalOpen = ref(false);

const defaultPaymentMethod = props.paymentMethods[0]?.name ?? '';

const form = useForm({
    address_id: props.default_address_id ?? null,
    email: props.prefill.email ?? '',
    delivery_method: props.totals.delivery_method || 'standard',
    delivery_notes: '',
    payment_method: defaultPaymentMethod,
    promo_code: props.totals.promo_code || '',
});

watch(
    () => props.paymentMethods,
    (methods) => {
        const names = methods.map((m) => m.name);
        if (form.payment_method && names.includes(form.payment_method)) {
            return;
        }
        form.payment_method = names[0] ?? '';
    },
    { deep: true },
);

watch(
    () => [props.default_address_id, props.addresses],
    () => {
        const ids = props.addresses.map((a) => a.id);
        if (form.address_id && ids.includes(form.address_id)) {
            return;
        }
        form.address_id = props.default_address_id ?? ids[0] ?? null;
    },
    { deep: true },
);

const items = computed(() => props.cart.items ?? []);
const itemCount = computed(() => Number(props.cart.item_count ?? 0));
const errors = computed(() => page.props.errors ?? {});
const hasAddresses = computed(() => props.addresses.length > 0);
const hasPaymentMethods = computed(() => props.paymentMethods.length > 0);
const selectedMethod = computed(() =>
    props.paymentMethods.find((m) => m.name === form.payment_method),
);
const canPlaceOrder = computed(
    () =>
        hasAddresses.value &&
        hasPaymentMethods.value &&
        !!form.address_id &&
        !!form.payment_method &&
        !form.processing &&
        !quoting.value,
);

const gatewayIcons = {
    stripe: IconCreditCard,
    cod: IconTruck,
};

const formatMoney = (price) => {
    const value = Number(price);

    if (Number.isNaN(value)) {
        return '$0.00';
    }

    return `$${value.toFixed(2)}`;
};

const formatAddressLines = (address) => {
    const lines = [
        address.address_line1,
        address.address_line2,
        [address.city, address.state, address.postal_code].filter(Boolean).join(', '),
        address.country,
    ];
    return lines.filter(Boolean);
};

const deliveryLabel = computed(() => {
    const fee = Number(props.totals.delivery_fee ?? 0);

    return fee <= 0 ? 'Free' : formatMoney(fee);
});

const standardFeeHint = computed(() => {
    return '2 business days · Free over $30';
});

watch(
    () => form.delivery_method,
    (method) => {
        if (!method || quoting.value) {
            return;
        }

        quoting.value = true;
        router.get(
            route('checkout'),
            {
                delivery_method: method,
                promo_code: form.promo_code || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                only: [
                    'cart',
                    'totals',
                    'addresses',
                    'default_address_id',
                    'paymentMethods',
                ],
                onFinish: () => {
                    quoting.value = false;
                },
            },
        );
    },
);

const submit = () => {
    if (!canPlaceOrder.value) {
        return;
    }

    form.post(route('orders.store'), {
        preserveScroll: true,
    });
};

const placeOrderLabel = computed(() => {
    if (form.processing) {
        return form.payment_method === 'stripe'
            ? 'Continuing to payment…'
            : 'Placing order…';
    }

    if (form.payment_method === 'stripe') {
        return `Continue to Payment — ${formatMoney(props.totals.total)}`;
    }

    return `Place Order — ${formatMoney(props.totals.total)}`;
});

const paymentDisclaimer = computed(() => {
    if (form.payment_method === 'stripe') {
        return 'You will enter your card details securely on the next step.';
    }

    if (selectedMethod.value?.instructions) {
        return selectedMethod.value.instructions;
    }

    return 'By placing your order, you agree to pay cash on delivery.';
});
</script>

<template>
    <CheckoutLayout>
        <Head title="Secure Checkout" />

        <div class="container-page max-w-[1200px] py-xxl">
            <div class="mb-xxl flex justify-center">
                <div class="flex flex-wrap items-center justify-center gap-4">
                    <div
                        class="flex items-center gap-2 rounded-full bg-primary-container px-6 py-2 text-on-primary-container"
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

            <form
                class="grid grid-cols-1 gap-xl lg:grid-cols-[65%_35%]"
                @submit.prevent="submit"
            >
                <div class="space-y-lg">
                    <section
                        class="card-shadow rounded-xl border border-outline-variant bg-white p-xl"
                    >
                        <div
                            class="mb-lg flex flex-col gap-md sm:flex-row sm:items-center sm:justify-between"
                        >
                            <h2 class="font-serif text-headline-sm text-primary">
                                Delivery Information
                            </h2>
                            <button
                                type="button"
                                class="inline-flex h-10 items-center justify-center gap-1.5 rounded-full border border-primary px-4 font-sans text-body-sm font-bold text-primary transition-colors hover:bg-surface-variant/10"
                                @click="addressModalOpen = true"
                            >
                                <IconPlus :size="16" stroke-width="1.5" />
                                Add address
                            </button>
                        </div>

                        <div class="mb-lg flex flex-col gap-xs">
                            <label
                                class="font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Email Address
                            </label>
                            <input
                                v-model="form.email"
                                type="email"
                                required
                                class="h-12 rounded-[10px] border border-outline-variant bg-white px-md outline-none focus:border-primary"
                                :class="{ 'border-error': form.errors.email }"
                                placeholder="john.doe@example.com"
                                autocomplete="email"
                            />
                            <p
                                v-if="form.errors.email"
                                class="text-xs font-semibold text-error"
                            >
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="mb-sm flex items-center justify-between">
                            <p
                                class="font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Delivery address
                            </p>
                        </div>

                        <div
                            v-if="!hasAddresses"
                            class="rounded-xl border border-dashed border-outline-variant bg-surface-container-low px-lg py-xl text-center"
                        >
                            <div
                                class="mx-auto mb-md flex h-12 w-12 items-center justify-center rounded-full bg-white text-outline"
                            >
                                <IconMapPin :size="24" stroke-width="1.5" />
                            </div>
                            <p class="font-sans text-title-lg font-semibold text-primary">
                                No saved addresses
                            </p>
                            <p class="mt-1 font-sans text-body-sm text-on-surface-variant">
                                Add a delivery address to place your order.
                            </p>
                            <button
                                type="button"
                                class="mt-md inline-flex h-11 items-center gap-1.5 rounded-full bg-secondary px-5 font-sans text-body-sm font-bold text-white"
                                @click="addressModalOpen = true"
                            >
                                <IconPlus :size="16" stroke-width="1.5" />
                                Add address
                            </button>
                        </div>

                        <div v-else class="space-y-md">
                            <label
                                v-for="address in addresses"
                                :key="address.id"
                                class="group block cursor-pointer"
                            >
                                <input
                                    v-model="form.address_id"
                                    class="peer sr-only"
                                    type="radio"
                                    :value="address.id"
                                />
                                <div
                                    class="rounded-xl border-2 border-outline-variant p-md transition-all group-hover:bg-surface-container-low peer-checked:border-secondary peer-checked:bg-secondary/5"
                                >
                                    <div class="mb-sm flex flex-wrap items-center gap-sm">
                                        <span
                                            class="rounded-[6px] bg-surface-container-high px-2 py-1 font-sans text-[12px] font-bold text-primary"
                                        >
                                            {{ address.label }}
                                        </span>
                                        <span
                                            v-if="address.is_default"
                                            class="inline-flex items-center gap-1 rounded-[6px] bg-secondary/10 px-2 py-1 font-sans text-[12px] font-bold text-secondary"
                                        >
                                            <IconStar :size="12" stroke-width="1.5" />
                                            Default
                                        </span>
                                    </div>
                                    <p class="font-sans text-title-lg font-semibold text-primary">
                                        {{ address.first_name }}
                                        {{ address.last_name }}
                                    </p>
                                    <p
                                        v-if="address.phone"
                                        class="font-sans text-body-sm text-on-surface-variant"
                                    >
                                        {{ address.phone }}
                                    </p>
                                    <p
                                        v-for="(line, index) in formatAddressLines(address)"
                                        :key="index"
                                        class="font-sans text-body-sm text-on-surface-variant"
                                    >
                                        {{ line }}
                                    </p>
                                </div>
                            </label>
                        </div>

                        <p
                            v-if="form.errors.address_id"
                            class="mt-md text-xs font-semibold text-error"
                        >
                            {{ form.errors.address_id }}
                        </p>

                        <div class="mt-lg flex flex-col gap-xs">
                            <label
                                class="font-sans text-label-caps uppercase text-on-surface-variant"
                            >
                                Delivery Notes (Optional)
                            </label>
                            <textarea
                                v-model="form.delivery_notes"
                                class="h-24 resize-none rounded-[10px] border border-outline-variant bg-white p-md outline-none focus:border-primary"
                                placeholder="e.g. Ring the bell twice..."
                            />
                        </div>

                        <p
                            v-if="form.errors.cart || errors.cart"
                            class="mt-md text-sm font-semibold text-error"
                        >
                            {{ form.errors.cart || errors.cart }}
                        </p>
                    </section>

                    <section
                        class="card-shadow rounded-xl border border-outline-variant bg-white p-xl"
                    >
                        <h2 class="mb-lg font-serif text-headline-sm text-primary">
                            Delivery Method
                        </h2>
                        <div class="grid grid-cols-1 gap-md md:grid-cols-2">
                            <label class="group cursor-pointer">
                                <input
                                    v-model="form.delivery_method"
                                    class="peer sr-only"
                                    type="radio"
                                    value="standard"
                                />
                                <div
                                    class="flex items-center justify-between rounded-xl border-2 border-outline-variant p-md transition-all group-hover:bg-surface-container-low peer-checked:border-secondary"
                                >
                                    <div class="flex flex-col">
                                        <span class="font-sans text-title-lg font-semibold">
                                            Standard Delivery
                                        </span>
                                        <span
                                            class="font-sans text-body-sm text-on-surface-variant"
                                        >
                                            {{ standardFeeHint }}
                                        </span>
                                    </div>
                                    <span class="font-bold text-secondary">
                                        {{
                                            form.delivery_method === 'standard'
                                                ? deliveryLabel
                                                : 'From $0'
                                        }}
                                    </span>
                                </div>
                            </label>

                            <label class="group cursor-pointer">
                                <input
                                    v-model="form.delivery_method"
                                    class="peer sr-only"
                                    type="radio"
                                    value="express"
                                />
                                <div
                                    class="flex items-center justify-between rounded-xl border-2 border-outline-variant p-md transition-all group-hover:bg-surface-container-low peer-checked:border-secondary"
                                >
                                    <div class="flex flex-col">
                                        <span class="font-sans text-title-lg font-semibold">
                                            Express Shipping
                                        </span>
                                        <span
                                            class="font-sans text-body-sm text-on-surface-variant"
                                        >
                                            Next day
                                        </span>
                                    </div>
                                    <span class="font-bold text-secondary">$9.99</span>
                                </div>
                            </label>
                        </div>
                    </section>

                    <section
                        class="card-shadow rounded-xl border border-outline-variant bg-white p-xl"
                    >
                        <h2 class="mb-lg font-serif text-headline-sm text-primary">
                            Payment
                        </h2>

                        <div
                            v-if="!hasPaymentMethods"
                            class="rounded-[10px] border border-warning/20 bg-warning/10 p-4"
                        >
                            <p class="font-sans text-sm text-warning">
                                No payment methods are available right now. Please
                                contact the store or try again later.
                            </p>
                        </div>

                        <div v-else class="space-y-md">
                            <label
                                v-for="method in paymentMethods"
                                :key="method.name"
                                class="group cursor-pointer"
                            >
                                <input
                                    v-model="form.payment_method"
                                    class="peer sr-only"
                                    type="radio"
                                    :value="method.name"
                                />
                                <div
                                    class="flex items-start gap-md rounded-xl border-2 border-outline-variant p-md transition-all group-hover:bg-surface-container-low peer-checked:border-secondary peer-checked:bg-surface-container-low"
                                >
                                    <div
                                        class="flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-secondary/10 text-secondary"
                                    >
                                        <component
                                            :is="
                                                gatewayIcons[method.name] ??
                                                IconCreditCard
                                            "
                                            :size="24"
                                            stroke-width="1.5"
                                        />
                                    </div>
                                    <div>
                                        <p
                                            class="font-sans text-title-lg font-semibold text-primary"
                                        >
                                            {{ method.label }}
                                        </p>
                                        <p
                                            class="mt-xs font-sans text-body-sm text-on-surface-variant"
                                        >
                                            {{ method.description }}
                                        </p>
                                        <p
                                            v-if="
                                                method.name === 'cod' &&
                                                method.instructions &&
                                                form.payment_method === 'cod'
                                            "
                                            class="mt-sm font-sans text-body-sm text-on-surface-variant"
                                        >
                                            {{ method.instructions }}
                                        </p>
                                    </div>
                                </div>
                            </label>
                        </div>
                        <p
                            v-if="errors.payment_method"
                            class="mt-sm font-sans text-sm text-error"
                        >
                            {{ errors.payment_method }}
                        </p>
                    </section>
                </div>

                <aside class="h-fit lg:sticky lg:top-32">
                    <div
                        class="card-shadow rounded-xl border border-outline-variant bg-white p-xl"
                    >
                        <h2 class="mb-lg font-serif text-headline-sm text-primary">
                            Order Summary
                        </h2>

                        <div class="mb-lg">
                            <button
                                type="button"
                                class="flex w-full items-center justify-between py-2 font-sans text-on-surface-variant transition-colors hover:text-primary"
                                @click="itemsExpanded = !itemsExpanded"
                            >
                                <span class="font-semibold">
                                    {{
                                        itemsExpanded
                                            ? 'Hide items'
                                            : `Show all ${itemCount} item${itemCount === 1 ? '' : 's'}`
                                    }}
                                </span>
                                <IconChevronUp
                                    v-if="itemsExpanded"
                                    :size="20"
                                    stroke-width="1.5"
                                />
                                <IconChevronDown
                                    v-else
                                    :size="20"
                                    stroke-width="1.5"
                                />
                            </button>

                            <div
                                v-show="itemsExpanded"
                                class="mt-md space-y-md border-t border-outline-variant pt-md"
                            >
                                <div
                                    v-for="item in items"
                                    :key="item.id"
                                    class="flex items-center gap-md"
                                >
                                    <div
                                        class="h-12 w-12 flex-shrink-0 overflow-hidden rounded-lg bg-surface-container"
                                    >
                                        <img
                                            v-if="item.product?.thumbnail"
                                            :src="item.product.thumbnail"
                                            :alt="item.product?.name"
                                            class="h-full w-full object-cover"
                                        />
                                    </div>
                                    <div class="min-w-0 flex-grow">
                                        <p class="truncate font-sans text-sm font-semibold">
                                            {{ item.product?.name }}
                                        </p>
                                        <p class="font-sans text-body-sm text-on-surface-variant">
                                            Qty: {{ item.quantity }}
                                        </p>
                                    </div>
                                    <span class="shrink-0 font-semibold">
                                        {{ formatMoney(item.line_total) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mb-lg space-y-sm border-y border-outline-variant py-md"
                        >
                            <div
                                class="flex justify-between font-sans text-on-surface-variant"
                            >
                                <span>Subtotal</span>
                                <span>{{ formatMoney(totals.subtotal) }}</span>
                            </div>
                            <div
                                v-if="Number(totals.discount_amount) > 0"
                                class="flex justify-between font-sans text-on-surface-variant"
                            >
                                <span>Discount</span>
                                <span class="font-semibold text-secondary">
                                    −{{ formatMoney(totals.discount_amount) }}
                                </span>
                            </div>
                            <div
                                class="flex justify-between font-sans text-on-surface-variant"
                            >
                                <span>Shipping</span>
                                <span
                                    class="font-semibold"
                                    :class="
                                        Number(totals.delivery_fee) <= 0
                                            ? 'text-secondary'
                                            : ''
                                    "
                                >
                                    {{ deliveryLabel }}
                                </span>
                            </div>
                            <div
                                class="flex justify-between font-sans text-on-surface-variant"
                            >
                                <span>Tax</span>
                                <span>{{ formatMoney(totals.tax_amount) }}</span>
                            </div>
                        </div>

                        <div class="mb-xl flex items-center justify-between">
                            <span class="font-serif text-headline-sm text-primary">
                                Total
                            </span>
                            <span class="font-serif text-headline-sm text-primary">
                                {{ formatMoney(totals.total) }}
                            </span>
                        </div>

                        <button
                            type="submit"
                            class="h-12 w-full rounded-full bg-secondary font-sans text-body-lg font-bold text-on-secondary shadow-md transition-all hover:scale-[1.02] active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-60"
                            :disabled="!canPlaceOrder"
                        >
                            {{ placeOrderLabel }}
                        </button>

                        <p
                            class="mt-md text-center font-sans text-[12px] text-on-surface-variant/70"
                        >
                            {{ paymentDisclaimer }}
                        </p>
                    </div>
                </aside>
            </form>
        </div>

        <AddressFormModal
            :show="addressModalOpen"
            @close="addressModalOpen = false"
        />
    </CheckoutLayout>
</template>
