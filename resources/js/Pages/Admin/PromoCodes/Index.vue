<script setup>
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconEdit,
    IconPlus,
    IconSearch,
    IconTrash,
    IconX,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    promoCodes: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            expired: 0,
            inactive: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '' }),
    },
});

const page = usePage();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.promo_codes ?? {},
);
const isSuperAdmin = computed(
    () => page.props.auth?.user?.role === 'super_admin',
);
const canCreate = computed(
    () => isSuperAdmin.value || permissions.value.create === true,
);
const canEdit = computed(
    () => isSuperAdmin.value || permissions.value.edit === true,
);
const canDelete = computed(
    () => isSuperAdmin.value || permissions.value.delete === true,
);

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');

const rows = computed(() => props.promoCodes?.data ?? []);
const pagination = computed(() => {
    const meta = props.promoCodes?.meta ?? {};

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
        return 'No promo codes';
    }

    return `Showing ${from}–${to} of ${total} promo codes`;
});

const applyFilters = () => {
    router.get(
        route('admin.promo-codes.index'),
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

let searchDebounce;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
});
watch(status, applyFilters);

const formatDiscount = (promo) => {
    if (promo.type === 'percentage') {
        return `${promo.value}% off`;
    }

    return `$${Number(promo.value).toFixed(2)} off`;
};

const formatDate = (iso) => {
    if (!iso) {
        return '—';
    }

    return new Date(iso).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

const toggleActive = (promo) => {
    if (!canEdit.value) {
        return;
    }

    router.patch(route('admin.promo-codes.toggle', promo.id), {}, {
        preserveScroll: true,
    });
};

const destroyPromo = (promo) => {
    if (!canDelete.value) {
        return;
    }

    if (!confirm(`Delete promo code "${promo.code}"? This cannot be undone.`)) {
        return;
    }

    router.delete(route('admin.promo-codes.destroy', promo.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout title="Promo Codes" breadcrumb="Promo Codes">
        <Head title="Promo Codes" />

        <section
            class="mb-lg flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-end"
        >
            <div>
                <nav
                    class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant"
                >
                    <span>Sales</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-on-surface">Promo Codes</span>
                </nav>
                <h2 class="font-serif text-headline-lg text-primary">
                    Promo Codes
                </h2>
                <p class="mt-1 text-body-sm text-on-surface-variant">
                    Create and manage discount codes for checkout
                </p>
            </div>

            <Link
                v-if="canCreate"
                :href="route('admin.promo-codes.create')"
                class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-full bg-secondary px-lg font-sans text-sm font-bold text-on-secondary shadow-md transition-all hover:opacity-90"
            >
                <IconPlus class="size-5" stroke="2" />
                Add Promo Code
            </Link>
        </section>

        <section class="mb-lg grid grid-cols-2 gap-sm md:gap-md lg:grid-cols-4">
            <div class="card-elevated p-md md:p-lg">
                <p class="text-label-caps uppercase text-outline">Total</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.total }}
                </p>
            </div>
            <div class="card-elevated p-md md:p-lg">
                <p class="text-label-caps uppercase text-outline">Active</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.active }}
                </p>
            </div>
            <div class="card-elevated p-md md:p-lg">
                <p class="text-label-caps uppercase text-outline">Expired</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.expired }}
                </p>
            </div>
            <div class="card-elevated p-md md:p-lg">
                <p class="text-label-caps uppercase text-outline">Inactive</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.inactive }}
                </p>
            </div>
        </section>

        <section class="card-elevated mb-lg p-lg">
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="relative w-full sm:min-w-[240px] sm:flex-1">
                    <IconSearch
                        class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline"
                        stroke="1.5"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search by code…"
                        class="input-field w-full pl-10"
                    />
                </div>
                <select v-model="status" class="input-field w-full sm:w-auto sm:min-w-[160px]">
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="expired">Expired</option>
                </select>
                <button
                    v-if="search || status"
                    type="button"
                    class="inline-flex h-12 items-center gap-2 rounded-full border border-outline-variant px-4 text-sm font-medium text-on-surface-variant transition hover:bg-surface-container-low"
                    @click="clearFilters"
                >
                    <IconX class="size-4" stroke="1.5" />
                    Clear
                </button>
            </div>
        </section>

        <section class="card-elevated overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[860px] text-left">
                    <thead>
                        <tr class="border-b border-outline-variant/40 bg-surface-container-low/60">
                            <th class="px-lg py-md text-label-caps uppercase text-outline">
                                Code
                            </th>
                            <th class="px-lg py-md text-label-caps uppercase text-outline">
                                Discount
                            </th>
                            <th class="px-lg py-md text-label-caps uppercase text-outline">
                                Min order
                            </th>
                            <th class="px-lg py-md text-label-caps uppercase text-outline">
                                Usage
                            </th>
                            <th class="px-lg py-md text-label-caps uppercase text-outline">
                                Window
                            </th>
                            <th class="px-lg py-md text-label-caps uppercase text-outline">
                                Status
                            </th>
                            <th class="px-lg py-md text-right text-label-caps uppercase text-outline">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="promo in rows"
                            :key="promo.id"
                            class="border-b border-outline-variant/30 last:border-0"
                        >
                            <td class="px-lg py-md">
                                <div class="min-w-0">
                                    <span class="font-sans text-sm font-bold tracking-wide text-primary">
                                        {{ promo.code }}
                                    </span>
                                    <p
                                        v-if="promo.title"
                                        class="mt-0.5 truncate font-serif text-body-sm text-on-surface"
                                    >
                                        {{ promo.title }}
                                    </p>
                                </div>
                            </td>
                            <td class="px-lg py-md text-body-sm text-on-surface">
                                {{ formatDiscount(promo) }}
                            </td>
                            <td class="px-lg py-md text-body-sm text-on-surface-variant">
                                <template v-if="promo.min_order_amount != null">
                                    ${{ Number(promo.min_order_amount).toFixed(2) }}
                                </template>
                                <template v-else>—</template>
                            </td>
                            <td class="px-lg py-md text-body-sm text-on-surface">
                                {{ promo.usage_label }}
                            </td>
                            <td class="px-lg py-md text-body-sm text-on-surface-variant">
                                {{ formatDate(promo.starts_at) }}
                                →
                                {{ formatDate(promo.expires_at) }}
                            </td>
                            <td class="px-lg py-md">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        v-if="promo.is_expired"
                                        class="rounded-md bg-error/10 px-2 py-1 text-[11px] font-bold uppercase tracking-wide text-error"
                                    >
                                        Expired
                                    </span>
                                    <button
                                        type="button"
                                        class="relative inline-flex h-6 w-11 shrink-0 rounded-full border-2 border-transparent transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
                                        :class="[
                                            promo.is_active
                                                ? 'bg-accent'
                                                : 'bg-outline-variant',
                                            canEdit
                                                ? 'cursor-pointer'
                                                : 'cursor-not-allowed opacity-60',
                                        ]"
                                        :disabled="!canEdit"
                                        :aria-pressed="promo.is_active"
                                        :aria-label="
                                            promo.is_active
                                                ? `Deactivate ${promo.code}`
                                                : `Activate ${promo.code}`
                                        "
                                        @click="toggleActive(promo)"
                                    >
                                        <span
                                            class="pointer-events-none inline-block size-5 rounded-full bg-white shadow transition"
                                            :class="
                                                promo.is_active
                                                    ? 'translate-x-5'
                                                    : 'translate-x-0'
                                            "
                                        />
                                    </button>
                                </div>
                            </td>
                            <td class="px-lg py-md">
                                <div class="flex items-center justify-end gap-1">
                                    <Link
                                        v-if="canEdit"
                                        :href="
                                            route(
                                                'admin.promo-codes.edit',
                                                promo.id,
                                            )
                                        "
                                        class="inline-flex size-9 items-center justify-center rounded-lg text-on-surface-variant transition hover:bg-surface-container-low hover:text-primary"
                                        :title="`Edit ${promo.code}`"
                                    >
                                        <IconEdit class="size-4" stroke="1.5" />
                                    </Link>
                                    <button
                                        v-if="canDelete"
                                        type="button"
                                        class="inline-flex size-9 items-center justify-center rounded-lg text-on-surface-variant transition hover:bg-error/10 hover:text-error"
                                        :title="`Delete ${promo.code}`"
                                        @click="destroyPromo(promo)"
                                    >
                                        <IconTrash class="size-4" stroke="1.5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="rows.length === 0">
                            <td colspan="7" class="px-lg py-xxl text-center">
                                <p class="font-serif text-headline-sm text-primary">
                                    No promo codes found
                                </p>
                                <p class="mt-2 text-body-sm text-on-surface-variant">
                                    Try adjusting your filters or create a new code.
                                </p>
                                <Link
                                    v-if="canCreate"
                                    :href="route('admin.promo-codes.create')"
                                    class="mt-md inline-flex h-12 items-center gap-2 rounded-full bg-secondary px-lg font-sans text-sm font-bold text-on-secondary"
                                >
                                    <IconPlus class="size-5" stroke="2" />
                                    Add Promo Code
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                v-if="pagination.total > 0"
                class="border-t border-outline-variant/30 px-lg py-md"
            >
                <AdminPagination
                    :pagination="pagination"
                    :showing-label="showingLabel"
                />
            </div>
        </section>
    </AdminLayout>
</template>
