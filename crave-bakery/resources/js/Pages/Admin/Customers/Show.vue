<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    IconArrowLeft,
    IconCalendar,
    IconCheck,
    IconChevronRight,
    IconMail,
    IconMapPin,
    IconPhone,
    IconShoppingBag,
    IconStar,
    IconUser,
} from '@tabler/icons-vue';
import { computed } from 'vue';

const props = defineProps({
    customer: {
        type: Object,
        required: true,
    },
});

const addresses = computed(() => props.customer.addresses ?? []);
const orders = computed(() => props.customer.orders ?? []);

const initials = computed(() => {
    const name = props.customer.name ?? '?';
    return name
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase() ?? '')
        .join('');
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

const formatGender = (value) => {
    if (!value) {
        return '—';
    }
    return value.charAt(0).toUpperCase() + value.slice(1);
};

const customerStatusClass = (value) => {
    const map = {
        active: 'bg-success/10 text-success',
        inactive: 'bg-surface-container-high text-on-surface-variant',
        banned: 'bg-error-container text-on-error-container',
    };
    return map[value] ?? map.inactive;
};

const customerStatusLabel = (value) => {
    const map = {
        active: 'Active',
        inactive: 'Inactive',
        banned: 'Banned',
    };
    return map[value] ?? value;
};

const orderStatusClass = (value) => {
    const map = {
        pending: 'bg-amber-100 text-amber-800',
        processing: 'bg-info/10 text-info',
        shipped: 'bg-purple-100 text-purple-800',
        delivered: 'bg-success/10 text-success',
        cancelled: 'bg-error-container text-on-error-container',
    };
    return map[value] ?? 'bg-surface-container-high text-on-surface-variant';
};

const paymentStatusClass = (value) => {
    const map = {
        pending: 'bg-amber-100 text-amber-800',
        paid: 'bg-success/10 text-success',
        refunded: 'bg-error-container text-on-error-container',
    };
    return map[value] ?? 'bg-surface-container-high text-on-surface-variant';
};

const formatAddressLine = (address) => {
    const parts = [
        address.address_line1,
        address.address_line2,
        [address.city, address.state, address.postal_code].filter(Boolean).join(', '),
        address.country,
    ].filter(Boolean);
    return parts.join(' · ');
};
</script>

<template>
    <AdminLayout title="Customer Detail" breadcrumb="Customers">
        <Head :title="`${customer.name} · Customers`" />

        <!-- Back + header -->
        <section class="mb-lg pt-4">
            <Link
                :href="route('admin.customers.index')"
                class="mb-md inline-flex items-center gap-2 text-body-sm font-semibold text-on-surface-variant transition hover:text-primary"
            >
                <IconArrowLeft class="size-4" stroke="1.5" />
                Back to Customers
            </Link>

            <div class="flex flex-col gap-md sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-md">
                    <img
                        v-if="customer.avatar"
                        :src="customer.avatar"
                        :alt="customer.name"
                        class="size-16 rounded-full border-2 border-outline-variant object-cover shadow-sm"
                    >
                    <div
                        v-else
                        class="flex size-16 items-center justify-center rounded-full border-2 border-outline-variant bg-primary-container text-xl font-bold text-on-primary-container shadow-sm"
                    >
                        {{ initials }}
                    </div>
                    <div>
                        <nav class="mb-1 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant">
                            <span>Admin</span>
                            <IconChevronRight class="size-3.5" stroke="2" />
                            <Link
                                :href="route('admin.customers.index')"
                                class="hover:text-secondary"
                            >
                                Customers
                            </Link>
                            <IconChevronRight class="size-3.5" stroke="2" />
                            <span class="text-secondary">Detail</span>
                        </nav>
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 class="font-serif text-headline-sm text-primary md:text-headline-md">
                                {{ customer.name }}
                            </h2>
                            <span
                                class="inline-flex rounded-md px-2.5 py-1 text-[11px] font-bold"
                                :class="customerStatusClass(customer.status)"
                            >
                                {{ customerStatusLabel(customer.status) }}
                            </span>
                        </div>
                        <p class="mt-1 flex items-center gap-1.5 text-body-sm text-on-surface-variant">
                            <IconMail class="size-4" stroke="1.5" />
                            {{ customer.email }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- KPI row -->
        <section class="mb-xl grid grid-cols-2 gap-md lg:grid-cols-5">
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Orders</p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ customer.orders_count ?? 0 }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Total Spent</p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ formatMoney(customer.total_spent) }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Avg Order</p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ customer.avg_order_value != null ? formatMoney(customer.avg_order_value) : '—' }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Last Order</p>
                <p class="font-serif text-title-lg text-primary">
                    {{ formatDate(customer.last_order_at) }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-1 text-label-caps text-outline">
                    <IconStar class="size-3" stroke="1.5" />
                    Reviews
                </p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ customer.reviews_count ?? 0 }}
                </p>
            </div>
        </section>

        <!-- Two columns -->
        <div class="grid grid-cols-1 gap-lg lg:grid-cols-5">
            <!-- Left: profile + addresses -->
            <div class="space-y-lg lg:col-span-2">
                <section class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-lg shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                    <h3 class="mb-md font-serif text-headline-sm text-primary">
                        Profile
                    </h3>
                    <dl class="space-y-md">
                        <div class="flex items-start gap-3">
                            <IconPhone class="mt-0.5 size-4 shrink-0 text-outline" stroke="1.5" />
                            <div>
                                <dt class="text-label-caps text-outline">Phone</dt>
                                <dd class="text-body-sm font-medium text-primary">
                                    {{ customer.phone || '—' }}
                                </dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <IconCalendar class="mt-0.5 size-4 shrink-0 text-outline" stroke="1.5" />
                            <div>
                                <dt class="text-label-caps text-outline">Date of Birth</dt>
                                <dd class="text-body-sm font-medium text-primary">
                                    {{ customer.date_of_birth ? formatDate(customer.date_of_birth) : '—' }}
                                </dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <IconUser class="mt-0.5 size-4 shrink-0 text-outline" stroke="1.5" />
                            <div>
                                <dt class="text-label-caps text-outline">Gender</dt>
                                <dd class="text-body-sm font-medium text-primary">
                                    {{ formatGender(customer.gender) }}
                                </dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <IconCheck class="mt-0.5 size-4 shrink-0 text-outline" stroke="1.5" />
                            <div>
                                <dt class="text-label-caps text-outline">Email Verified</dt>
                                <dd class="text-body-sm font-medium text-primary">
                                    {{ customer.email_verified_at ? formatDate(customer.email_verified_at) : 'Not verified' }}
                                </dd>
                            </div>
                        </div>
                        <div class="flex items-start gap-3">
                            <IconCalendar class="mt-0.5 size-4 shrink-0 text-outline" stroke="1.5" />
                            <div>
                                <dt class="text-label-caps text-outline">Registered</dt>
                                <dd class="text-body-sm font-medium text-primary">
                                    {{ formatDate(customer.created_at) }}
                                </dd>
                            </div>
                        </div>
                    </dl>
                </section>

                <section class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-lg shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                    <h3 class="mb-md font-serif text-headline-sm text-primary">
                        Addresses
                    </h3>
                    <div v-if="addresses.length === 0" class="py-6 text-center">
                        <IconMapPin class="mx-auto mb-2 size-8 text-outline-variant" stroke="1.5" />
                        <p class="text-body-sm text-on-surface-variant">
                            No addresses on file.
                        </p>
                    </div>
                    <ul v-else class="space-y-md">
                        <li
                            v-for="address in addresses"
                            :key="address.id"
                            class="rounded-lg border border-outline-variant/40 bg-surface p-md"
                        >
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <span class="text-sm font-bold text-primary">
                                    {{ address.label || 'Address' }}
                                </span>
                                <span
                                    v-if="address.is_default"
                                    class="rounded-md bg-primary/10 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-primary"
                                >
                                    Default
                                </span>
                            </div>
                            <p class="text-body-sm text-on-surface">
                                {{ address.first_name }} {{ address.last_name }}
                            </p>
                            <p class="mt-1 text-body-sm text-on-surface-variant">
                                {{ formatAddressLine(address) }}
                            </p>
                            <p
                                v-if="address.phone"
                                class="mt-1 text-body-sm text-on-surface-variant"
                            >
                                {{ address.phone }}
                            </p>
                        </li>
                    </ul>
                </section>
            </div>

            <!-- Right: order history -->
            <section class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-[0_2px_12px_rgba(0,0,0,0.07)] lg:col-span-3">
                <div class="flex items-center justify-between border-b border-outline-variant/30 px-lg py-md">
                    <h3 class="font-serif text-headline-sm text-primary">
                        Order History
                    </h3>
                    <span class="text-body-sm text-on-surface-variant">
                        {{ orders.length }} recent
                    </span>
                </div>

                <div v-if="orders.length === 0" class="px-lg py-12 text-center">
                    <IconShoppingBag class="mx-auto mb-2 size-10 text-outline-variant" stroke="1.5" />
                    <p class="text-body-sm text-on-surface-variant">
                        This customer has not placed any orders yet.
                    </p>
                </div>

                <div v-else class="overflow-x-auto">
                    <table class="w-full min-w-[560px] border-collapse text-left">
                        <thead>
                            <tr class="border-b border-outline-variant/20 bg-surface-container-low">
                                <th class="px-lg py-3 text-label-caps text-outline">Order</th>
                                <th class="px-md py-3 text-label-caps text-outline">Status</th>
                                <th class="px-md py-3 text-label-caps text-outline">Payment</th>
                                <th class="px-md py-3 text-label-caps text-outline">Total</th>
                                <th class="px-lg py-3 text-label-caps text-outline">Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant/10">
                            <tr
                                v-for="order in orders"
                                :key="order.id"
                                class="transition-colors hover:bg-surface-container/40"
                            >
                                <td class="px-lg py-3">
                                    <Link
                                        :href="route('admin.orders.show', order.id)"
                                        class="font-semibold text-secondary hover:underline"
                                    >
                                        {{ order.order_number }}
                                    </Link>
                                    <p class="text-xs text-on-surface-variant">
                                        {{ order.items_count }} item{{ order.items_count === 1 ? '' : 's' }}
                                    </p>
                                </td>
                                <td class="px-md py-3">
                                    <span
                                        class="inline-flex rounded-md px-2 py-0.5 text-[11px] font-bold capitalize"
                                        :class="orderStatusClass(order.status)"
                                    >
                                        {{ order.status }}
                                    </span>
                                </td>
                                <td class="px-md py-3">
                                    <span
                                        class="inline-flex rounded-md px-2 py-0.5 text-[11px] font-bold capitalize"
                                        :class="paymentStatusClass(order.payment_status)"
                                    >
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-md py-3 text-body-sm font-semibold text-primary">
                                    {{ formatMoney(order.total) }}
                                </td>
                                <td class="px-lg py-3 text-body-sm text-on-surface-variant">
                                    {{ formatDate(order.created_at) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </AdminLayout>
</template>
