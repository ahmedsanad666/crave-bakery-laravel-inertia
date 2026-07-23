<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const isCancelled = computed(() => props.order.status === 'cancelled');

const items = computed(() => props.order.items ?? []);
const itemCount = computed(
    () => props.order.items_count ?? items.value.length,
);

const visibleThumbs = computed(() => items.value.slice(0, 2));
const overflowCount = computed(() => Math.max(0, itemCount.value - 2));

const placedOn = computed(() => {
    if (!props.order.created_at) {
        return '';
    }
    return new Date(props.order.created_at).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
});

const totalFormatted = computed(() =>
    Number(props.order.total ?? 0).toLocaleString('en-US', {
        style: 'currency',
        currency: 'USD',
    }),
);

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
            return 'bg-orange-100 text-orange-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'pending':
        default:
            return 'bg-primary-container text-on-primary-container';
    }
});
</script>

<template>
    <article
        class="rounded-xl bg-white p-lg shadow-[0_2px_12px_rgba(0,0,0,0.07)] transition-all duration-300 hover:shadow-[0_4px_16px_rgba(0,0,0,0.1)]"
        :class="{ 'opacity-80': isCancelled }"
    >
        <div
            class="mb-md flex flex-col items-start justify-between gap-md border-b border-outline-variant pb-md lg:flex-row lg:items-center"
        >
            <div>
                <div class="flex flex-wrap items-center gap-md">
                    <span class="font-sans text-title-lg font-semibold text-primary">
                        Order #{{ order.order_number }}
                    </span>
                    <span
                        class="rounded-[6px] px-sm py-1 font-sans text-[12px] font-bold uppercase tracking-wider"
                        :class="statusBadgeClass"
                    >
                        {{ statusLabel }}
                    </span>
                </div>
                <p v-if="placedOn" class="mt-1 font-sans text-body-sm text-outline">
                    Placed on {{ placedOn }}
                </p>
            </div>
            <div class="text-left lg:text-right">
                <p class="font-sans text-body-sm text-outline">Total Amount</p>
                <p class="font-sans text-title-lg font-semibold text-secondary">
                    {{ totalFormatted }}
                </p>
            </div>
        </div>

        <div
            class="flex flex-col items-center justify-between gap-lg md:flex-row"
        >
            <div
                class="flex w-full items-center gap-md overflow-x-auto pb-2 md:w-auto"
            >
                <div class="flex -space-x-4">
                    <div
                        v-for="(item, index) in visibleThumbs"
                        :key="item.id ?? index"
                        class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-lg border-2 border-white shadow-sm"
                        :class="{ grayscale: isCancelled }"
                    >
                        <img
                            v-if="item.thumbnail"
                            :src="item.thumbnail"
                            :alt="item.product_name"
                            class="h-full w-full object-cover"
                        />
                        <div
                            v-else
                            class="flex h-full w-full items-center justify-center bg-surface-container-high font-sans text-body-sm font-bold text-outline"
                        >
                            {{ (item.product_name || '?').charAt(0) }}
                        </div>
                    </div>
                    <div
                        v-if="overflowCount > 0"
                        class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-lg border-2 border-white bg-surface-container-high font-sans text-body-sm font-bold text-outline"
                    >
                        +{{ overflowCount }}
                    </div>
                </div>
                <span
                    class="whitespace-nowrap font-sans text-body-sm font-semibold text-on-surface-variant"
                >
                    {{ itemCount }}
                    {{ itemCount === 1 ? 'Item' : 'Items' }}
                </span>
            </div>

            <div class="flex w-full gap-md md:w-auto">
                <Link
                    :href="route('orders.show', order.id)"
                    class="flex h-12 flex-1 items-center justify-center rounded-full border border-primary px-lg font-sans text-body-sm font-bold text-primary transition-colors hover:bg-surface-variant/10 md:flex-none"
                >
                    View Details
                </Link>
            </div>
        </div>
    </article>
</template>
