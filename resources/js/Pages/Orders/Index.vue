<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import { IconPackage, IconSearch } from '@tabler/icons-vue';
import OrderHistoryCard from '@/Components/Public/OrderHistoryCard.vue';
import ShopPagination from '@/Components/Public/ShopPagination.vue';
import ProfileLayout from '@/Layouts/ProfileLayout.vue';

const props = defineProps({
    orders: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({
            status: null,
            search: null,
        }),
    },
    user: {
        type: Object,
        default: null,
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? null);

watch(
    () => props.filters,
    (next) => {
        search.value = next.search ?? '';
        status.value = next.status ?? null;
    },
    { deep: true },
);

const orderList = computed(() => props.orders.data ?? []);
const meta = computed(() => props.orders.meta ?? {});

const statusPills = [
    { value: null, label: 'All' },
    { value: 'pending', label: 'Pending' },
    { value: 'processing', label: 'Processing' },
    { value: 'delivered', label: 'Delivered' },
    { value: 'cancelled', label: 'Cancelled' },
];

const buildQuery = (overrides = {}) => {
    const next = {
        search: search.value || undefined,
        status: status.value || undefined,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(next).filter(([, value]) => value !== undefined && value !== null && value !== ''),
    );
};

const applyFilters = (overrides = {}) => {
    router.get(route('orders.index'), buildQuery(overrides), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters({ status: status.value });
}, 350);

const onSearchInput = (event) => {
    search.value = event.target.value;
    debouncedSearch();
};

const setStatus = (value) => {
    status.value = value;
    applyFilters({ status: value });
};

const paginationQuery = computed(() => buildQuery());
</script>

<template>
    <ProfileLayout>
        <Head title="My Orders" />

        <section class="space-y-1">
            <h1 class="font-serif text-headline-lg text-primary">My Orders</h1>
            <p class="font-sans text-body-lg text-on-surface-variant">
                Track and manage your purchases
            </p>
        </section>

        <div
            class="flex flex-col justify-between gap-md md:flex-row md:items-center"
        >
            <div class="relative w-full md:w-[280px]">
                <IconSearch
                    class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                    :size="20"
                    stroke-width="1.5"
                />
                <input
                    type="search"
                    class="h-12 w-full rounded-xl border border-outline-variant bg-surface-container-lowest py-2 pl-10 pr-4 font-sans text-body-sm text-on-surface outline-none transition-all placeholder:text-outline focus:border-secondary focus:ring-1 focus:ring-secondary/20"
                    placeholder="Search orders..."
                    :value="search"
                    @input="onSearchInput"
                />
            </div>

            <div class="flex flex-wrap gap-sm">
                <button
                    v-for="pill in statusPills"
                    :key="pill.label"
                    type="button"
                    class="rounded-full px-md py-2 font-sans text-body-sm transition-colors"
                    :class="
                        status === pill.value
                            ? 'bg-secondary font-bold text-white shadow-sm'
                            : 'border border-outline-variant bg-white font-semibold text-on-surface-variant hover:bg-surface-container-low'
                    "
                    @click="setStatus(pill.value)"
                >
                    {{ pill.label }}
                </button>
            </div>
        </div>

        <div v-if="orderList.length" class="space-y-md">
            <OrderHistoryCard
                v-for="order in orderList"
                :key="order.id"
                :order="order"
            />
        </div>

        <div
            v-else
            class="flex flex-col items-center justify-center rounded-xl border border-dashed border-outline-variant bg-white px-6 py-16 text-center"
        >
            <div
                class="mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-surface-container-high text-outline"
            >
                <IconPackage :size="28" stroke-width="1.5" />
            </div>
            <h2 class="font-serif text-headline-sm text-primary">No orders yet</h2>
            <p class="mt-2 max-w-sm font-sans text-body-sm text-on-surface-variant">
                <template v-if="filters.search || filters.status">
                    No orders match your current filters. Try a different status or search.
                </template>
                <template v-else>
                    When you place an order, it will show up here so you can track and manage it.
                </template>
            </p>
            <Link
                v-if="!filters.search && !filters.status"
                :href="route('products.index')"
                class="mt-6 inline-flex h-12 items-center justify-center rounded-full bg-secondary px-xl font-sans text-body-sm font-bold text-white transition-opacity hover:opacity-90"
            >
                Browse catalogue
            </Link>
        </div>

        <ShopPagination
            :meta="meta"
            :query="paginationQuery"
            route-name="orders.index"
        />
    </ProfileLayout>
</template>
