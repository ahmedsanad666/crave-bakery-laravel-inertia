<script setup>
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconEye,
    IconSearch,
    IconShoppingBag,
    IconUser,
    IconUserCheck,
    IconUserOff,
    IconUsers,
    IconUserPlus,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    customers: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            inactive: 0,
            new_this_month: 0,
            with_orders: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
        }),
    },
});

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const rows = computed(() => props.customers?.data ?? []);
const pagination = computed(() => {
    const meta = props.customers?.meta ?? {};
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
        return 'No customers';
    }
    return `Showing ${from}–${to} of ${total} customers`;
});

const statusTabs = computed(() => [
    { value: '', label: 'All', count: props.stats.total ?? 0 },
    { value: 'active', label: 'Active', count: props.stats.active ?? 0 },
    { value: 'inactive', label: 'Inactive', count: props.stats.inactive ?? 0 },
]);

const applyFilters = () => {
    router.get(
        route('admin.customers.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
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

const formatMoney = (value) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(value ?? 0));

const formatDate = (iso) => {
    if (!iso) {
        return '—';
    }
    return new Date(iso).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const customerInitials = (name) => {
    if (!name) {
        return '?';
    }
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
};

const statusBadgeClass = (value) => {
    const map = {
        active: 'bg-success/10 text-success',
        inactive: 'bg-surface-container-high text-on-surface-variant',
        banned: 'bg-error-container text-on-error-container',
    };
    return map[value] ?? map.inactive;
};

const statusLabel = (value) => {
    const map = {
        active: 'Active',
        inactive: 'Inactive',
        banned: 'Banned',
    };
    return map[value] ?? value;
};
</script>

<template>
    <AdminLayout title="Customers" breadcrumb="Customers">
        <Head title="Customers" />

        <!-- Header -->
        <section class="mb-lg flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-end">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant">
                    <span>Admin</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span>Customers</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-secondary">Customers</span>
                </nav>
                <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                    Customers
                </h2>
                <p class="mt-1 text-body-sm text-on-surface-variant">
                    View customer profiles, spend, and order history.
                </p>
            </div>
        </section>

        <!-- Stats -->
        <section class="mb-xl grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-xs text-label-caps text-outline">
                    <IconUsers class="size-3.5" stroke="1.5" />
                    Total Customers
                </p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ stats.total }}
                </p>
            </div>
            <div class="rounded-xl border-l-4 border-l-success bg-white p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-xs text-label-caps text-outline">
                    <IconUserCheck class="size-3.5" stroke="1.5" />
                    Active
                </p>
                <p class="font-serif text-headline-sm text-success">
                    {{ stats.active }}
                </p>
            </div>
            <div class="rounded-xl border-l-4 border-l-outline bg-white p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-xs text-label-caps text-outline">
                    <IconUserOff class="size-3.5" stroke="1.5" />
                    Inactive
                </p>
                <p class="font-serif text-headline-sm text-on-surface-variant">
                    {{ stats.inactive }}
                </p>
            </div>
            <div class="rounded-xl border-l-4 border-l-secondary-container bg-white p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-xs text-label-caps text-outline">
                    <IconUserPlus class="size-3.5" stroke="1.5" />
                    New This Month
                </p>
                <p class="font-serif text-headline-sm text-secondary">
                    {{ stats.new_this_month }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-xs text-label-caps text-outline">
                    <IconShoppingBag class="size-3.5" stroke="1.5" />
                    With Orders
                </p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ stats.with_orders }}
                </p>
            </div>
        </section>

        <!-- Filters -->
        <section class="mb-lg rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-lg shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
            <div class="mb-lg flex flex-wrap items-center gap-sm">
                <span class="mr-sm text-label-caps text-outline">Status:</span>
                <button
                    v-for="tab in statusTabs"
                    :key="tab.value || 'all'"
                    type="button"
                    class="rounded-full px-md py-1.5 text-body-sm transition-colors"
                    :class="
                        status === tab.value
                            ? 'bg-secondary font-bold text-white shadow-sm'
                            : 'border border-outline-variant text-on-surface-variant hover:border-secondary'
                    "
                    @click="setStatusTab(tab.value)"
                >
                    {{ tab.label }}
                    <span>({{ tab.count }})</span>
                </button>
            </div>

            <div class="flex flex-col gap-md sm:flex-row sm:items-center sm:justify-between">
                <div class="relative max-w-md flex-1">
                    <IconSearch
                        class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline"
                        stroke="1.5"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search name, email, or phone..."
                        class="h-12 w-full rounded-[10px] border border-outline-variant bg-white pl-10 pr-4 text-body-sm focus:border-primary focus:outline-none focus:ring-1 focus:ring-primary/20"
                    >
                </div>
                <button
                    type="button"
                    class="text-sm font-bold text-secondary underline hover:opacity-80"
                    @click="clearFilters"
                >
                    Reset All Filters
                </button>
            </div>
        </section>

        <!-- Table -->
        <div class="overflow-hidden rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container-low">
                            <th class="p-md text-label-caps text-outline">Customer</th>
                            <th class="p-md text-label-caps text-outline">Orders</th>
                            <th class="p-md text-label-caps text-outline">Total Spent</th>
                            <th class="p-md text-label-caps text-outline">Registered</th>
                            <th class="p-md text-label-caps text-outline">Status</th>
                            <th class="p-md text-label-caps text-outline">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <tr v-if="rows.length === 0">
                            <td
                                colspan="6"
                                class="p-xl text-center text-body-sm text-on-surface-variant"
                            >
                                <div class="flex flex-col items-center gap-2 py-8">
                                    <IconUser class="size-10 text-outline-variant" stroke="1.5" />
                                    <p>No customers match your filters.</p>
                                </div>
                            </td>
                        </tr>

                        <tr
                            v-for="customer in rows"
                            :key="customer.id"
                            class="cursor-pointer transition-colors hover:bg-surface-container/30"
                            @click="router.visit(route('admin.customers.show', customer.id))"
                        >
                            <td class="p-md">
                                <div class="flex items-center gap-md">
                                    <img
                                        v-if="customer.avatar"
                                        :src="customer.avatar"
                                        :alt="customer.name"
                                        class="size-10 shrink-0 rounded-full object-cover"
                                    >
                                    <div
                                        v-else
                                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-on-primary-container"
                                    >
                                        {{ customerInitials(customer.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-bold text-primary">
                                            {{ customer.name }}
                                        </p>
                                        <p class="truncate text-body-sm text-on-surface-variant">
                                            {{ customer.email }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-md text-body-sm font-medium text-primary">
                                {{ customer.orders_count }}
                            </td>
                            <td class="p-md text-body-sm font-semibold text-primary">
                                {{ formatMoney(customer.total_spent) }}
                            </td>
                            <td class="p-md text-body-sm text-on-surface-variant">
                                {{ formatDate(customer.created_at) }}
                            </td>
                            <td class="p-md">
                                <span
                                    class="inline-flex rounded-md px-2.5 py-1 text-[11px] font-bold"
                                    :class="statusBadgeClass(customer.status)"
                                >
                                    {{ statusLabel(customer.status) }}
                                </span>
                            </td>
                            <td class="p-md" @click.stop>
                                <Link
                                    :href="route('admin.customers.show', customer.id)"
                                    class="inline-flex items-center gap-1 rounded-full border border-outline-variant px-3 py-1.5 text-xs font-semibold text-on-surface-variant transition hover:border-primary hover:text-primary"
                                >
                                    <IconEye class="size-4" stroke="1.5" />
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="border-t border-outline-variant/30 bg-surface-container-low px-md py-md">
                <AdminPagination
                    :pagination="pagination"
                    :showing-label="showingLabel"
                />
            </div>
        </div>
    </AdminLayout>
</template>
