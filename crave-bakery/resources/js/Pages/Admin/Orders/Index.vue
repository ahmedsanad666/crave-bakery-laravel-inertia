<script setup>
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    IconCheck,
    IconChevronRight,
    IconCircleCheck,
    IconCircleX,
    IconDots,
    IconDotsVertical,
    IconDownload,
    IconEye,
    IconFilter,
    IconPrinter,
    IconRefresh,
    IconSearch,
    IconTrash,
    IconTruck,
    IconX,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            pending: 0,
            processing: 0,
            shipped: 0,
            delivered: 0,
            cancelled: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            payment_status: '',
            payment_method: '',
            delivery_method: '',
            date_from: '',
            date_to: '',
            amount_min: null,
            amount_max: null,
        }),
    },
});

const page = usePage();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.orders ?? {},
);
const isSuperAdmin = computed(
    () => page.props.auth?.user?.role === 'super_admin',
);
const canUpdateStatus = computed(
    () => isSuperAdmin.value || permissions.value.update_status === true,
);
const canRefund = computed(
    () => isSuperAdmin.value || permissions.value.refund === true,
);

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const paymentMethod = ref(props.filters.payment_method ?? '');
const deliveryMethod = ref(props.filters.delivery_method ?? '');
const dateFrom = ref(props.filters.date_from ?? '');
const dateTo = ref(props.filters.date_to ?? '');
const amountMin = ref(
    props.filters.amount_min != null ? String(props.filters.amount_min) : '',
);
const amountMax = ref(
    props.filters.amount_max != null ? String(props.filters.amount_max) : '',
);
const datePreset = ref(detectDatePreset(props.filters.date_from, props.filters.date_to));
const showAdvancedFilters = ref(true);

const datePresetOptions = [
    { id: '', name: 'Any time' },
    { id: '7', name: 'Last 7 Days' },
    { id: '30', name: 'Last 30 Days' },
    { id: 'quarter', name: 'This Quarter' },
    { id: 'custom', name: 'Custom Range' },
];

const paymentMethodOptions = [
    { id: '', name: 'All Methods' },
    { id: 'card', name: 'Credit Card' },
    { id: 'paypal', name: 'PayPal' },
    { id: 'apple_pay', name: 'Apple Pay' },
];

const deliveryMethodOptions = [
    { id: '', name: 'All Types' },
    { id: 'standard', name: 'Standard Delivery' },
    { id: 'express', name: 'Express Delivery' },
];

const rows = computed(() => props.orders?.data ?? []);
const pagination = computed(() => {
    const meta = props.orders?.meta ?? {};
    return {
        current_page: meta.current_page ?? 1,
        last_page: meta.last_page ?? 1,
        from: meta.from ?? null,
        to: meta.to ?? null,
        total: meta.total ?? rows.value.length,
    };
});

const showingLabel = computed(() => {
    const { from, to, total } = pagination.value;
    if (!total) {
        return 'No orders';
    }
    return `Showing ${from}–${to} of ${total} orders`;
});

const statusTabs = computed(() => [
    { value: '', label: 'All', count: props.stats.total ?? 0 },
    { value: 'pending', label: 'Pending', count: props.stats.pending ?? 0 },
    { value: 'processing', label: 'Processing', count: props.stats.processing ?? 0 },
    { value: 'shipped', label: 'Shipped', count: props.stats.shipped ?? 0 },
    { value: 'delivered', label: 'Delivered', count: props.stats.delivered ?? 0 },
]);

const selectedIds = ref([]);

const allPageSelected = computed(
    () =>
        rows.value.length > 0
        && rows.value.every((order) => selectedIds.value.includes(order.id)),
);

const toggleSelectAll = () => {
    if (allPageSelected.value) {
        const pageIds = new Set(rows.value.map((o) => o.id));
        selectedIds.value = selectedIds.value.filter((id) => !pageIds.has(id));
        return;
    }
    selectedIds.value = [...new Set([
        ...selectedIds.value,
        ...rows.value.map((o) => o.id),
    ])];
};

const toggleSelect = (id) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter((item) => item !== id);
        return;
    }
    selectedIds.value = [...selectedIds.value, id];
};

const clearSelection = () => {
    selectedIds.value = [];
};

watch(
    () => props.orders?.data,
    () => {
        const visible = new Set(rows.value.map((o) => o.id));
        selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
    },
);

function detectDatePreset(from, to) {
    if (!from && !to) {
        return '';
    }
    const today = new Date();
    const toIso = (d) => d.toISOString().slice(0, 10);
    const end = toIso(today);

    const last7 = new Date(today);
    last7.setDate(last7.getDate() - 7);
    if (from === toIso(last7) && to === end) {
        return '7';
    }

    const last30 = new Date(today);
    last30.setDate(last30.getDate() - 30);
    if (from === toIso(last30) && to === end) {
        return '30';
    }

    const quarterStart = new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 1);
    if (from === toIso(quarterStart) && to === end) {
        return 'quarter';
    }

    return 'custom';
}

function syncDatesFromPreset(preset) {
    const today = new Date();
    const toIso = (d) => d.toISOString().slice(0, 10);

    if (preset === 'custom') {
        return;
    }

    if (preset === '') {
        dateFrom.value = '';
        dateTo.value = '';
        applyFilters();
        return;
    }

    dateTo.value = toIso(today);

    if (preset === '7') {
        const start = new Date(today);
        start.setDate(start.getDate() - 7);
        dateFrom.value = toIso(start);
    } else if (preset === '30') {
        const start = new Date(today);
        start.setDate(start.getDate() - 30);
        dateFrom.value = toIso(start);
    } else if (preset === 'quarter') {
        dateFrom.value = toIso(
            new Date(today.getFullYear(), Math.floor(today.getMonth() / 3) * 3, 1),
        );
    }

    applyFilters();
}

watch(datePreset, (preset) => {
    syncDatesFromPreset(preset ?? '');
});

const applyFilters = () => {
    router.get(
        route('admin.orders.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            payment_method: paymentMethod.value || undefined,
            delivery_method: deliveryMethod.value || undefined,
            date_from: dateFrom.value || undefined,
            date_to: dateTo.value || undefined,
            amount_min: amountMin.value || undefined,
            amount_max: amountMax.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    paymentMethod.value = '';
    deliveryMethod.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    amountMin.value = '';
    amountMax.value = '';
    datePreset.value = '';
    applyFilters();
};

const setStatusTab = (value) => {
    status.value = value;
    applyFilters();
};

let searchDebounce;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
});

watch([paymentMethod, deliveryMethod], applyFilters);

let amountDebounce;
watch([amountMin, amountMax], () => {
    clearTimeout(amountDebounce);
    amountDebounce = setTimeout(applyFilters, 350);
});

watch([dateFrom, dateTo], () => {
    if (datePreset.value === 'custom') {
        applyFilters();
    }
});

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value ?? 0));

const formatDate = (iso) => {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
};

const customerName = (order) => {
    if (order.customer?.name) {
        return order.customer.name;
    }
    return `${order.first_name ?? ''} ${order.last_name ?? ''}`.trim() || 'Guest';
};

const customerEmail = (order) => order.customer?.email || order.email || '';

const customerInitials = (order) => {
    const name = customerName(order);
    return name
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
};

const paymentLabel = (method) => {
    const map = {
        card: 'Card',
        stripe: 'Stripe',
        paypal: 'PayPal',
        apple_pay: 'Apple Pay',
        google_pay: 'Google Pay',
    };
    return map[method] ?? (method ? String(method) : '—');
};

const paymentBadgeClass = (method) => {
    if (method === 'paypal') {
        return 'bg-gray-100 text-gray-600';
    }
    if (method === 'apple_pay' || method === 'google_pay') {
        return 'bg-green-100 text-green-700';
    }
    return 'bg-blue-100 text-blue-700';
};

const deliveryBadgeClass = (method) => {
    if (method === 'express') {
        return 'bg-secondary-container/10 text-secondary-container font-bold';
    }
    return 'bg-surface-container text-on-surface-variant font-semibold';
};

const statusMeta = (orderStatus) => {
    const map = {
        pending: {
            label: 'Pending',
            wrap: 'bg-amber-50 text-amber-700 border-amber-200',
            dot: 'bg-amber-500 animate-pulse',
        },
        processing: {
            label: 'Processing',
            wrap: 'bg-blue-50 text-blue-700 border-blue-200',
            dot: 'bg-blue-500',
        },
        shipped: {
            label: 'Shipped',
            wrap: 'bg-purple-50 text-purple-700 border-purple-200',
            dot: 'bg-purple-500',
        },
        delivered: {
            label: 'Delivered',
            wrap: 'bg-green-50 text-green-700 border-green-200',
            dot: 'bg-green-500',
        },
        cancelled: {
            label: 'Cancelled',
            wrap: 'bg-error-container/20 text-error border-error/20',
            dot: 'bg-error',
        },
    };
    return map[orderStatus] ?? {
        label: orderStatus,
        wrap: 'bg-surface-container text-on-surface-variant border-outline-variant',
        dot: 'bg-outline',
    };
};

const itemThumbnails = (order) => {
    const items = order.items ?? [];
    return items
        .map((item) => item.thumbnail)
        .filter(Boolean)
        .slice(0, 2);
};

const extraItemCount = (order) => {
    const total = order.items_count ?? order.items?.length ?? 0;
    return Math.max(0, total - 2);
};

const placeholderImage =
    'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=80&q=80';

const STATUS_OPTIONS = [
    'pending',
    'processing',
    'shipped',
    'delivered',
    'cancelled',
];

const canShipOrder = (order) =>
    !['shipped', 'delivered', 'cancelled'].includes(order.status);

const updateOrderStatus = (order, nextStatus) => {
    if (!canUpdateStatus.value || order.status === nextStatus) {
        return;
    }
    router.patch(
        route('admin.orders.update', order.id),
        { status: nextStatus },
        { preserveScroll: true },
    );
};

const refundOrder = (order) => {
    if (!canRefund.value || order.payment_status !== 'paid') {
        return;
    }
    if (!confirm(`Refund order ${order.order_number}?`)) {
        return;
    }
    router.post(
        route('admin.orders.refund', order.id),
        {},
        { preserveScroll: true },
    );
};

const bulkMarkShipped = () => {
    if (!canUpdateStatus.value || selectedIds.value.length === 0) {
        return;
    }

    const shippable = rows.value.filter(
        (order) =>
            selectedIds.value.includes(order.id) && canShipOrder(order),
    );

    if (shippable.length === 0) {
        clearSelection();
        return;
    }

    const queue = [...shippable];
    const patchNext = () => {
        const order = queue.shift();
        if (!order) {
            clearSelection();
            return;
        }
        router.patch(
            route('admin.orders.update', order.id),
            { status: 'shipped' },
            {
                preserveScroll: true,
                onFinish: patchNext,
            },
        );
    };
    patchNext();
};
</script>

<template>
    <AdminLayout title="Orders" breadcrumb="Orders">
        <Head title="Orders" />

        <!-- Header -->
        <section
            class="mb-lg flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-end"
        >
            <div>
                <nav
                    class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant"
                >
                    <span>Admin</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span>Sales</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-secondary">Orders</span>
                </nav>
                <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                    Orders
                </h2>
                <p class="mt-1 text-body-sm text-on-surface-variant">
                    Track, manage and fulfil all customer orders
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <button
                    type="button"
                    disabled
                    title="Coming soon"
                    class="inline-flex h-12 cursor-not-allowed items-center gap-2 rounded-full border border-primary-container px-lg text-body-sm font-semibold text-primary-container opacity-50"
                >
                    <IconPrinter class="size-5" stroke="1.5" />
                    Print Orders
                </button>
                <button
                    type="button"
                    disabled
                    title="Coming soon"
                    class="inline-flex h-12 cursor-not-allowed items-center gap-2 rounded-full bg-secondary px-lg text-body-sm font-semibold text-on-secondary opacity-50 shadow-md"
                >
                    <IconDownload class="size-5" stroke="1.5" />
                    Export CSV
                </button>
            </div>
        </section>

        <!-- Stats -->
        <section class="mb-lg grid grid-cols-2 gap-4 lg:grid-cols-5">
            <div
                class="rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-5 shadow-sm"
            >
                <p class="mb-1 font-sans text-body-sm text-outline">Total Orders</p>
                <div class="flex items-end justify-between">
                    <h4 class="font-serif text-headline-sm font-bold text-primary">
                        {{ stats.total }}
                    </h4>
                </div>
            </div>
            <div
                class="rounded-2xl border border-outline-variant/20 border-l-4 border-l-amber-400 bg-surface-container-lowest p-5 shadow-sm"
            >
                <p class="mb-1 font-sans text-body-sm text-outline">Pending</p>
                <div class="flex items-end justify-between">
                    <h4 class="font-serif text-headline-sm font-bold text-amber-600">
                        {{ stats.pending }}
                    </h4>
                    <IconDots class="size-6 text-amber-400" stroke="1.5" />
                </div>
            </div>
            <div
                class="rounded-2xl border border-outline-variant/20 border-l-4 border-l-blue-400 bg-surface-container-lowest p-5 shadow-sm"
            >
                <p class="mb-1 font-sans text-body-sm text-outline">Processing</p>
                <div class="flex items-end justify-between">
                    <h4 class="font-serif text-headline-sm font-bold text-blue-600">
                        {{ stats.processing }}
                    </h4>
                    <IconRefresh class="size-6 text-blue-400" stroke="1.5" />
                </div>
            </div>
            <div
                class="rounded-2xl border border-outline-variant/20 border-l-4 border-l-green-400 bg-surface-container-lowest p-5 shadow-sm"
            >
                <p class="mb-1 font-sans text-body-sm text-outline">Delivered</p>
                <div class="flex items-end justify-between">
                    <h4 class="font-serif text-headline-sm font-bold text-green-600">
                        {{ stats.delivered }}
                    </h4>
                    <IconCircleCheck class="size-6 text-green-400" stroke="1.5" />
                </div>
            </div>
            <div
                class="rounded-2xl border border-outline-variant/20 border-l-4 border-l-error bg-surface-container-lowest p-5 shadow-sm"
            >
                <p class="mb-1 font-sans text-body-sm text-outline">Cancelled</p>
                <div class="flex items-end justify-between">
                    <h4 class="font-serif text-headline-sm font-bold text-error">
                        {{ stats.cancelled }}
                    </h4>
                    <IconCircleX class="size-6 text-error opacity-50" stroke="1.5" />
                </div>
            </div>
        </section>

        <!-- Search (page-level; theme keeps search in topbar) -->
        <div class="mb-4">
            <div class="relative max-w-md">
                <IconSearch
                    class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline-variant"
                    stroke="1.5"
                />
                <input
                    v-model="search"
                    type="search"
                    placeholder="Search orders, customers..."
                    class="w-full rounded-full border-none bg-surface-container-low py-2 pl-10 pr-4 text-body-sm text-on-surface placeholder:text-outline focus:ring-2 focus:ring-secondary/20"
                />
            </div>
        </div>

        <!-- Toolbar & filters -->
        <section
            class="mb-lg space-y-4 rounded-2xl border border-outline-variant/20 bg-surface-container-lowest p-4 shadow-sm"
        >
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tab in statusTabs"
                        :key="tab.value || 'all'"
                        type="button"
                        class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-body-sm transition-colors"
                        :class="
                            status === tab.value
                                ? 'bg-secondary font-semibold text-on-secondary'
                                : 'text-on-surface-variant hover:bg-surface-container-high'
                        "
                        @click="setStatusTab(tab.value)"
                    >
                        {{ tab.label }}
                        <span
                            class="rounded-full px-2 text-[10px]"
                            :class="
                                status === tab.value
                                    ? 'bg-on-secondary/20'
                                    : 'bg-surface-container-highest'
                            "
                        >
                            {{ tab.count }}
                        </span>
                    </button>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center gap-2 rounded-full border border-secondary-container/30 px-4 py-2 text-body-sm font-semibold text-secondary-container transition-all hover:bg-secondary-container/5"
                    @click="showAdvancedFilters = !showAdvancedFilters"
                >
                    <IconFilter class="size-[18px]" stroke="1.5" />
                    Advanced Filters
                </button>
            </div>

            <div
                v-show="showAdvancedFilters"
                class="grid grid-cols-1 gap-4 border-t border-outline-variant/10 pt-4 sm:grid-cols-2 lg:grid-cols-4"
            >
                <div class="space-y-1">
                    <label class="px-1 text-[10px] font-bold uppercase text-outline">
                        Date Range
                    </label>
                    <AppSelect
                        v-model="datePreset"
                        :options="datePresetOptions"
                        placeholder="Any time"
                    />
                    <div
                        v-if="datePreset === 'custom'"
                        class="mt-2 flex items-center gap-2"
                    >
                        <input
                            v-model="dateFrom"
                            type="date"
                            class="input-field h-12 w-1/2 text-body-sm"
                        />
                        <input
                            v-model="dateTo"
                            type="date"
                            class="input-field h-12 w-1/2 text-body-sm"
                        />
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="px-1 text-[10px] font-bold uppercase text-outline">
                        Payment Method
                    </label>
                    <AppSelect
                        v-model="paymentMethod"
                        :options="paymentMethodOptions"
                        placeholder="All Methods"
                    />
                </div>

                <div class="space-y-1">
                    <label class="px-1 text-[10px] font-bold uppercase text-outline">
                        Delivery
                    </label>
                    <AppSelect
                        v-model="deliveryMethod"
                        :options="deliveryMethodOptions"
                        placeholder="All Types"
                    />
                </div>

                <div class="space-y-1">
                    <label class="px-1 text-[10px] font-bold uppercase text-outline">
                        Amount Range
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            v-model="amountMin"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Min"
                            class="input-field h-12 w-1/2 text-body-sm"
                        />
                        <input
                            v-model="amountMax"
                            type="number"
                            min="0"
                            step="0.01"
                            placeholder="Max"
                            class="input-field h-12 w-1/2 text-body-sm"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Table -->
        <section
            class="relative overflow-hidden rounded-2xl border border-outline-variant/20 bg-surface-container-lowest shadow-sm"
        >
            <div
                v-if="selectedIds.length > 0"
                class="flex items-center justify-between bg-primary-container px-lg py-4 text-on-primary-container"
            >
                <div class="flex flex-wrap items-center gap-4 lg:gap-6">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex size-6 items-center justify-center rounded-full bg-secondary text-[12px] font-bold text-on-secondary"
                        >
                            {{ selectedIds.length }}
                        </span>
                        <span class="font-semibold">orders selected</span>
                    </div>
                    <div class="hidden h-8 w-px bg-on-primary-container/20 sm:block" />
                    <div class="flex flex-wrap gap-4">
                        <button
                            v-if="canUpdateStatus"
                            type="button"
                            class="flex items-center gap-2 transition-colors hover:text-white"
                            @click="bulkMarkShipped"
                        >
                            <IconTruck class="size-5" stroke="1.5" />
                            Mark as Shipped
                        </button>
                        <button
                            type="button"
                            disabled
                            title="Coming soon"
                            class="flex cursor-not-allowed items-center gap-2 opacity-50"
                        >
                            <IconPrinter class="size-5" stroke="1.5" />
                            Print Labels
                        </button>
                        <button
                            type="button"
                            disabled
                            title="Coming soon"
                            class="flex cursor-not-allowed items-center gap-2 opacity-50"
                        >
                            <IconTrash class="size-5" stroke="1.5" />
                            Delete
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="rounded-full p-2 hover:bg-white/10"
                    @click="clearSelection"
                >
                    <IconX class="size-5" stroke="1.5" />
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead class="bg-surface-container-low/50">
                        <tr>
                            <th class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    class="rounded border-outline-variant text-secondary focus:ring-secondary"
                                    :checked="allPageSelected"
                                    :disabled="rows.length === 0"
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="px-2 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Order ID
                            </th>
                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Date
                            </th>
                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Customer
                            </th>
                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Items
                            </th>
                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Total
                            </th>
                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Delivery
                            </th>
                            <th class="px-4 py-4 text-[10px] font-bold uppercase tracking-widest text-outline">
                                Status
                            </th>
                            <th class="px-6 py-4 text-right" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <tr
                            v-for="order in rows"
                            :key="order.id"
                            class="group transition-colors hover:bg-surface-container-low/30"
                        >
                            <td class="px-6 py-4">
                                <input
                                    type="checkbox"
                                    class="rounded border-outline-variant text-secondary focus:ring-secondary"
                                    :checked="selectedIds.includes(order.id)"
                                    @change="toggleSelect(order.id)"
                                />
                            </td>
                            <td class="px-2 py-4 font-bold text-primary">
                                {{ order.order_number }}
                            </td>
                            <td class="px-4 py-4 text-body-sm text-on-surface-variant">
                                {{ formatDate(order.created_at) }}
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex size-8 items-center justify-center rounded-full bg-primary-fixed text-[12px] font-bold text-primary"
                                    >
                                        <img
                                            v-if="order.customer?.avatar"
                                            :src="order.customer.avatar"
                                            :alt="customerName(order)"
                                            class="size-8 rounded-full object-cover"
                                        />
                                        <span v-else>{{ customerInitials(order) }}</span>
                                    </div>
                                    <div>
                                        <p class="text-body-sm font-semibold text-primary">
                                            {{ customerName(order) }}
                                        </p>
                                        <p class="text-[11px] text-outline">
                                            {{ customerEmail(order) }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div class="flex -space-x-3">
                                    <img
                                        v-for="(thumb, idx) in itemThumbnails(order)"
                                        :key="`${order.id}-thumb-${idx}`"
                                        :src="thumb"
                                        alt=""
                                        class="size-8 rounded-full border-2 border-surface object-cover"
                                    />
                                    <div
                                        v-if="!itemThumbnails(order).length && (order.items_count || order.items?.length)"
                                        class="flex size-8 items-center justify-center rounded-full border-2 border-surface bg-surface-container-high text-[10px] font-bold"
                                    >
                                        <img
                                            :src="placeholderImage"
                                            alt=""
                                            class="size-8 rounded-full object-cover"
                                        />
                                    </div>
                                    <div
                                        v-if="extraItemCount(order) > 0"
                                        class="flex size-8 items-center justify-center rounded-full border-2 border-surface bg-surface-container-high text-[10px] font-bold"
                                    >
                                        +{{ extraItemCount(order) }}
                                    </div>
                                    <div
                                        v-else-if="!(order.items_count || order.items?.length)"
                                        class="text-body-sm text-on-surface-variant"
                                    >
                                        —
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <p class="text-body-sm font-bold text-primary">
                                    {{ formatMoney(order.total) }}
                                </p>
                                <span
                                    class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase"
                                    :class="paymentBadgeClass(order.payment_method)"
                                >
                                    {{ paymentLabel(order.payment_method) }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <span
                                    class="rounded-full px-2 py-1 text-[11px] capitalize"
                                    :class="deliveryBadgeClass(order.delivery_method)"
                                >
                                    {{ order.delivery_method || '—' }}
                                </span>
                            </td>
                            <td class="px-4 py-4">
                                <div
                                    class="inline-flex w-fit items-center gap-1.5 rounded-full border px-3 py-1"
                                    :class="statusMeta(order.status).wrap"
                                >
                                    <div
                                        class="size-1.5 rounded-full"
                                        :class="statusMeta(order.status).dot"
                                    />
                                    <span class="text-[11px] font-bold uppercase">
                                        {{ statusMeta(order.status).label }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    class="flex justify-end gap-1 opacity-100 transition-opacity lg:opacity-0 lg:group-hover:opacity-100"
                                >
                                    <Link
                                        :href="route('admin.orders.show', order.id)"
                                        class="rounded-full p-2 text-secondary transition-colors hover:bg-secondary/10"
                                        title="View"
                                    >
                                        <IconEye class="size-5" stroke="1.5" />
                                    </Link>

                                    <Menu
                                        v-if="canUpdateStatus || canRefund"
                                        as="div"
                                        class="relative"
                                    >
                                        <MenuButton
                                            class="rounded-full p-2 text-on-surface-variant transition-colors hover:bg-on-surface-variant/10"
                                            title="More actions"
                                        >
                                            <IconDotsVertical class="size-5" stroke="1.5" />
                                        </MenuButton>
                                        <MenuItems
                                            class="absolute right-0 z-20 mt-1 w-48 origin-top-right rounded-xl border border-outline-variant/30 bg-surface-container-lowest py-1 shadow-modal focus:outline-none"
                                        >
                                            <template v-if="canUpdateStatus">
                                                <div
                                                    class="border-b border-outline-variant/20 px-3 py-2 text-[10px] font-bold uppercase tracking-wider text-outline"
                                                >
                                                    Update status
                                                </div>
                                                <MenuItem
                                                    v-for="opt in STATUS_OPTIONS"
                                                    v-slot="{ active }"
                                                    :key="`${order.id}-${opt}`"
                                                >
                                                    <button
                                                        type="button"
                                                        class="flex w-full items-center justify-between px-3 py-2 text-left text-body-sm capitalize"
                                                        :class="active ? 'bg-surface-container' : ''"
                                                        @click="updateOrderStatus(order, opt)"
                                                    >
                                                        {{ opt }}
                                                        <IconCheck
                                                            v-if="order.status === opt"
                                                            class="size-4 text-secondary"
                                                            stroke="2"
                                                        />
                                                    </button>
                                                </MenuItem>
                                            </template>
                                            <MenuItem
                                                v-if="canRefund && order.payment_status === 'paid'"
                                                v-slot="{ active }"
                                            >
                                                <button
                                                    type="button"
                                                    class="mt-1 flex w-full border-t border-outline-variant/20 px-3 py-2 text-left text-body-sm text-error"
                                                    :class="active ? 'bg-error-container/10' : ''"
                                                    @click="refundOrder(order)"
                                                >
                                                    Refund payment
                                                </button>
                                            </MenuItem>
                                            <MenuItem v-slot="{ active }">
                                                <Link
                                                    :href="route('admin.orders.show', order.id)"
                                                    class="flex w-full px-3 py-2 text-left text-body-sm text-primary"
                                                    :class="active ? 'bg-surface-container' : ''"
                                                >
                                                    View details
                                                </Link>
                                            </MenuItem>
                                        </MenuItems>
                                    </Menu>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div
                    v-if="rows.length === 0"
                    class="py-16 text-center text-on-surface-variant"
                >
                    <p class="font-sans text-sm font-semibold text-primary">
                        No orders found
                    </p>
                    <p class="mt-1 text-sm">
                        Try adjusting your filters or seed demo orders.
                    </p>
                    <button
                        type="button"
                        class="btn-ghost btn-sm mt-4"
                        @click="clearFilters"
                    >
                        Clear filters
                    </button>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-outline-variant/10 bg-surface-container-low/50 px-lg py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-body-sm text-outline">
                    {{ showingLabel }}
                </p>
                <AdminPagination
                    v-if="pagination.last_page > 1"
                    :pagination="pagination"
                />
            </div>
        </section>
    </AdminLayout>
</template>
