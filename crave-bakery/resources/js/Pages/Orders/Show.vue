<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconCash,
    IconMapPin,
} from '@tabler/icons-vue';
import OrderStatusStepper from '@/Components/Public/OrderStatusStepper.vue';
import ProfileLayout from '@/Layouts/ProfileLayout.vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
    user: {
        type: Object,
        default: null,
    },
});

const formatMoney = (value) =>
    Number(value ?? 0).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
    });

const formatOptions = (attrs) => {
    if (!attrs || typeof attrs !== 'object') {
        return '';
    }
    if (Array.isArray(attrs)) {
        return attrs
            .map((a) =>
                typeof a === 'string'
                    ? a
                    : `${a.name ?? a.label ?? ''}: ${a.value ?? ''}`.trim(),
            )
            .filter(Boolean)
            .join(' • ');
    }
    return Object.entries(attrs)
        .map(([key, value]) => {
            if (value && typeof value === 'object') {
                return value.label ?? value.value ?? key;
            }
            return `${key}: ${value}`;
        })
        .filter(Boolean)
        .join(' • ');
};

const items = computed(() => props.order.items ?? []);

const statusLabel = computed(() => {
    const status = props.order.status ?? '';
    return status.charAt(0).toUpperCase() + status.slice(1);
});

const statusBadgeClass = computed(() => {
    switch (props.order.status) {
        case 'delivered':
            return 'bg-emerald-100 text-emerald-800';
        case 'processing':
        case 'shipped':
            return 'bg-secondary-container/20 text-secondary';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'pending':
        default:
            return 'bg-primary-container text-on-primary-container';
    }
});

const customerName = computed(() =>
    [props.order.first_name, props.order.last_name].filter(Boolean).join(' '),
);

const addressLines = computed(() => {
    const lines = [
        props.order.address_line1,
        props.order.address_line2,
        [props.order.city, props.order.state, props.order.postal_code]
            .filter(Boolean)
            .join(', '),
        props.order.country,
    ];
    return lines.filter(Boolean);
});

const paymentLabel = computed(() => {
    if (!props.order.payment_method || props.order.payment_method === 'cod') {
        return 'Cash on Delivery';
    }
    return props.order.payment_method
        .split('_')
        .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
        .join(' ');
});

const itemMeta = (item) => {
    const parts = [`Qty: ${item.quantity}`];
    const options = formatOptions(item.selected_attributes);
    if (options) {
        parts.push(options);
    }
    return parts.join(' • ');
};

const deliveryFeeDisplay = computed(() => {
    if (Number(props.order.delivery_fee ?? 0) <= 0) {
        return { text: 'FREE', free: true };
    }
    return { text: formatMoney(props.order.delivery_fee), free: false };
});

const hasDiscount = computed(
    () => Number(props.order.discount_amount ?? 0) > 0,
);
</script>

<template>
    <ProfileLayout>
        <Head :title="`Order #${order.order_number}`" />

        <div>
            <Link
                :href="route('orders.index')"
                class="group mb-md inline-flex items-center font-sans text-body-sm text-on-surface-variant transition-colors hover:text-secondary"
            >
                <IconArrowLeft
                    class="mr-sm transition-transform group-hover:-translate-x-0.5"
                    :size="18"
                    stroke-width="1.5"
                />
                Back to My Orders
            </Link>

            <div class="flex flex-col gap-md md:flex-row md:items-center md:justify-between">
                <div class="flex flex-wrap items-center gap-md">
                    <h1 class="font-serif text-headline-lg text-primary">
                        Order #{{ order.order_number }}
                    </h1>
                    <span
                        class="rounded-full px-md py-xs font-sans text-label-caps uppercase tracking-wider"
                        :class="statusBadgeClass"
                    >
                        {{ statusLabel }}
                    </span>
                </div>
            </div>
        </div>

        <OrderStatusStepper
            :status="order.status"
            :created-at="order.created_at"
            :estimated-delivery-at="order.estimated_delivery_at"
            :delivered-at="order.delivered_at"
        />

        <div class="grid grid-cols-1 gap-xl lg:grid-cols-3">
            <div class="space-y-xl lg:col-span-2">
                <div
                    class="overflow-hidden rounded-xl border border-outline-variant/30 bg-white shadow-sm"
                >
                    <div
                        class="border-b border-outline-variant/30 bg-surface-container-low p-lg"
                    >
                        <h2 class="font-serif text-headline-sm text-primary">
                            Items Ordered
                        </h2>
                    </div>

                    <div class="divide-y divide-outline-variant/20 p-lg">
                        <component
                            :is="item.product_slug ? Link : 'div'"
                            v-for="item in items"
                            :key="item.id"
                            v-bind="
                                item.product_slug
                                    ? {
                                          href: route(
                                              'products.show',
                                              item.product_slug,
                                          ),
                                      }
                                    : {}
                            "
                            class="flex items-center justify-between gap-md py-md first:pt-0 last:pb-0"
                            :class="{
                                'rounded-lg transition-colors hover:bg-surface-container-low/60 -mx-2 px-2':
                                    !!item.product_slug,
                                'cursor-default': !item.product_slug,
                            }"
                        >
                            <div class="flex min-w-0 items-center gap-lg">
                                <div
                                    class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-lg bg-surface-container-high"
                                >
                                    <img
                                        v-if="item.thumbnail"
                                        :src="item.thumbnail"
                                        :alt="item.product_name"
                                        class="h-full w-full object-cover"
                                    />
                                    <div
                                        v-else
                                        class="flex h-full w-full items-center justify-center font-sans text-body-sm font-bold text-outline"
                                    >
                                        {{ (item.product_name || '?').charAt(0) }}
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <h4
                                        class="truncate font-sans text-title-lg font-semibold text-primary"
                                        :class="{
                                            'group-hover:text-secondary':
                                                !!item.product_slug,
                                        }"
                                    >
                                        {{ item.product_name }}
                                    </h4>
                                    <p
                                        class="font-sans text-body-sm text-on-surface-variant"
                                    >
                                        {{ itemMeta(item) }}
                                    </p>
                                </div>
                            </div>
                            <span
                                class="shrink-0 font-sans text-title-lg font-semibold text-secondary"
                            >
                                {{ formatMoney(item.line_total) }}
                            </span>
                        </component>
                    </div>

                    <div
                        class="border-t border-outline-variant/30 bg-surface-container-low p-lg"
                    >
                        <div class="space-y-sm font-sans text-body-lg text-on-surface-variant">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span>{{ formatMoney(order.subtotal) }}</span>
                            </div>
                            <div
                                v-if="hasDiscount"
                                class="flex justify-between"
                            >
                                <span>
                                    Discount
                                    <template v-if="order.promo_code">
                                        ({{ order.promo_code }})
                                    </template>
                                </span>
                                <span class="text-success">
                                    −{{ formatMoney(order.discount_amount) }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span>Tax</span>
                                <span>{{ formatMoney(order.tax_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Delivery Fee</span>
                                <span
                                    :class="{
                                        'font-semibold text-green-600':
                                            deliveryFeeDisplay.free,
                                    }"
                                >
                                    {{ deliveryFeeDisplay.text }}
                                </span>
                            </div>
                            <div
                                class="flex justify-between border-t border-outline-variant/50 pt-md"
                            >
                                <span class="font-serif text-headline-sm text-primary">
                                    Total
                                </span>
                                <span class="font-serif text-headline-sm text-secondary">
                                    {{ formatMoney(order.total) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-xl">
                <div
                    class="rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm"
                >
                    <div class="mb-md flex items-center gap-sm text-primary">
                        <IconMapPin :size="22" stroke-width="1.5" />
                        <h3 class="font-sans text-title-lg font-semibold">
                            Delivery Address
                        </h3>
                    </div>
                    <div class="space-y-xs font-sans text-body-lg text-on-surface-variant">
                        <p v-if="customerName" class="font-bold text-primary">
                            {{ customerName }}
                        </p>
                        <p v-for="(line, index) in addressLines" :key="index">
                            {{ line }}
                        </p>
                        <p v-if="order.phone">{{ order.phone }}</p>
                        <p
                            v-if="order.delivery_notes"
                            class="mt-sm border-t border-outline-variant/30 pt-sm text-body-sm"
                        >
                            Notes: {{ order.delivery_notes }}
                        </p>
                    </div>
                </div>

                <div
                    class="rounded-xl border border-outline-variant/30 bg-white p-lg shadow-sm"
                >
                    <div class="mb-md flex items-center gap-sm text-primary">
                        <IconCash :size="22" stroke-width="1.5" />
                        <h3 class="font-sans text-title-lg font-semibold">
                            Payment Method
                        </h3>
                    </div>
                    <div class="flex items-center gap-md">
                        <div
                            class="flex h-10 w-12 items-center justify-center rounded bg-surface-variant text-primary"
                        >
                            <IconCash :size="22" stroke-width="1.5" />
                        </div>
                        <div class="font-sans text-body-lg text-on-surface-variant">
                            <p class="font-bold text-primary">
                                {{ paymentLabel }}
                            </p>
                            <p class="text-body-sm capitalize">
                                Payment status:
                                {{ order.payment_status || 'pending' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </ProfileLayout>
</template>
