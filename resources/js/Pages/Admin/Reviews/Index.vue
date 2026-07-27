<script setup>
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import ReviewDetailPanel from '@/Components/Admin/ReviewDetailPanel.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Menu, MenuButton, MenuItem, MenuItems } from '@headlessui/vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import {
    IconBan,
    IconCamera,
    IconCheck,
    IconChevronRight,
    IconCircleCheck,
    IconDotsVertical,
    IconFlag,
    IconPhoto,
    IconRefresh,
    IconSearch,
    IconStar,
    IconStarFilled,
    IconTrash,
    IconX,
} from '@tabler/icons-vue';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    reviews: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            pending: 0,
            approved: 0,
            flagged: 0,
            rejected: 0,
            avg_rating: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            rating: null,
        }),
    },
});

const page = usePage();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.reviews ?? {},
);
const isSuperAdmin = computed(
    () => page.props.auth?.user?.role === 'super_admin',
);
const canApprove = computed(
    () => isSuperAdmin.value || permissions.value.approve === true,
);
const canDelete = computed(
    () => isSuperAdmin.value || permissions.value.delete === true,
);
const canRespond = computed(
    () => isSuperAdmin.value || permissions.value.respond === true,
);

const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const rating = ref(
    props.filters.rating != null && props.filters.rating !== ''
        ? Number(props.filters.rating)
        : null,
);

const selectedReview = ref(null);
const panelOpen = ref(false);

const rows = computed(() => props.reviews?.data ?? []);
const pagination = computed(() => {
    const meta = props.reviews?.meta ?? {};
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
        return 'No reviews';
    }
    return `Showing ${from}–${to} of ${total} reviews`;
});

const statusTabs = computed(() => [
    { value: '', label: 'All', count: props.stats.total ?? 0 },
    { value: 'pending', label: 'Pending', count: props.stats.pending ?? 0 },
    { value: 'approved', label: 'Approved', count: props.stats.approved ?? 0 },
    { value: 'flagged', label: 'Flagged', count: props.stats.flagged ?? 0 },
    { value: 'rejected', label: 'Rejected', count: props.stats.rejected ?? 0 },
]);

const selectedIds = ref([]);

const allPageSelected = computed(
    () =>
        rows.value.length > 0
        && rows.value.every((review) => selectedIds.value.includes(review.id)),
);

const toggleSelectAll = () => {
    if (allPageSelected.value) {
        const pageIds = new Set(rows.value.map((r) => r.id));
        selectedIds.value = selectedIds.value.filter((id) => !pageIds.has(id));
        return;
    }
    selectedIds.value = [...new Set([
        ...selectedIds.value,
        ...rows.value.map((r) => r.id),
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
    () => props.reviews?.data,
    () => {
        const visible = new Set(rows.value.map((r) => r.id));
        selectedIds.value = selectedIds.value.filter((id) => visible.has(id));

        if (selectedReview.value) {
            const updated = rows.value.find((r) => r.id === selectedReview.value.id);
            if (updated) {
                selectedReview.value = updated;
            }
        }
    },
);

const applyFilters = () => {
    router.get(
        route('admin.reviews.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            rating: rating.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    rating.value = null;
    applyFilters();
};

const setStatusTab = (value) => {
    status.value = value;
    applyFilters();
};

const setRatingFilter = (value) => {
    rating.value = rating.value === value ? null : value;
    applyFilters();
};

let searchDebounce;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
});

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
        pending: 'bg-amber-100 text-amber-800',
        approved: 'bg-green-100 text-green-800',
        flagged: 'bg-error-container text-on-error-container',
        rejected: 'bg-surface-container-high text-on-surface-variant',
    };
    return map[value] ?? map.pending;
};

const statusLabel = (value) => {
    const map = {
        pending: 'Pending',
        approved: 'Approved',
        flagged: 'Flagged',
        rejected: 'Rejected',
    };
    return map[value] ?? value;
};

const rowClass = (review) => {
    if (review.status === 'flagged') {
        return 'bg-error-container/5';
    }
    if (review.status === 'rejected') {
        return 'bg-surface-container-low opacity-60 grayscale';
    }
    return '';
};

const openPanel = (review) => {
    selectedReview.value = review;
    panelOpen.value = true;

    const url = new URL(window.location.href);
    url.searchParams.set('review', String(review.id));
    window.history.replaceState({}, '', `${url.pathname}${url.search}`);
};

const closePanel = () => {
    panelOpen.value = false;
    selectedReview.value = null;

    const url = new URL(window.location.href);
    url.searchParams.delete('review');
    window.history.replaceState({}, '', `${url.pathname}${url.search}`);
};

const updateStatus = (review, nextStatus, flagReason = null) => {
    if (!canApprove.value || !review || review.status === nextStatus) {
        return;
    }

    const payload = { status: nextStatus };
    if (nextStatus === 'flagged' && flagReason) {
        payload.flag_reason = flagReason;
    }

    router.patch(route('admin.reviews.update', review.id), payload, {
        preserveScroll: true,
    });
};

const approveReview = (review) => updateStatus(review, 'approved');
const rejectReview = (review) => updateStatus(review, 'rejected');
const clearFlag = (review) => updateStatus(review, 'approved');
const undoReject = (review) => updateStatus(review, 'pending');

const deleteReview = (review) => {
    if (!canDelete.value || !review) {
        return;
    }
    if (!confirm(`Delete review “${review.title}”? This cannot be undone.`)) {
        return;
    }

    router.delete(route('admin.reviews.destroy', review.id), {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedReview.value?.id === review.id) {
                closePanel();
            }
            clearSelection();
        },
    });
};

const runBulk = (action) => {
    const queue = rows.value.filter((r) => selectedIds.value.includes(r.id));
    if (queue.length === 0) {
        return;
    }

    const next = () => {
        const review = queue.shift();
        if (!review) {
            clearSelection();
            return;
        }

        if (action === 'delete') {
            if (!canDelete.value) {
                next();
                return;
            }
            router.delete(route('admin.reviews.destroy', review.id), {
                preserveScroll: true,
                onFinish: next,
            });
            return;
        }

        if (!canApprove.value) {
            next();
            return;
        }

        router.patch(
            route('admin.reviews.update', review.id),
            { status: action },
            {
                preserveScroll: true,
                onFinish: next,
            },
        );
    };

    if (action === 'delete') {
        if (!confirm(`Delete ${selectedIds.value.length} selected review(s)?`)) {
            return;
        }
    }

    next();
};

const bulkApprove = () => runBulk('approved');
const bulkReject = () => runBulk('rejected');
const bulkDelete = () => runBulk('delete');

onMounted(() => {
    const params = new URLSearchParams(window.location.search);
    const reviewId = Number(params.get('review'));
    if (!reviewId) {
        return;
    }
    const match = rows.value.find((r) => r.id === reviewId);
    if (match) {
        openPanel(match);
    }
});
</script>

<template>
    <AdminLayout title="Reviews" breadcrumb="Reviews">
        <Head title="Reviews" />

        <!-- Header -->
        <section class="mb-lg flex flex-col justify-between gap-4 pt-4 sm:flex-row sm:items-end">
            <div>
                <nav class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant">
                    <span>Admin</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span>Customers</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-secondary">Reviews</span>
                </nav>
                <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                    Reviews
                </h2>
                <p class="mt-1 text-body-sm text-on-surface-variant">
                    Manage customer feedback and moderate store ratings.
                </p>
            </div>
        </section>

        <!-- Stats -->
        <section class="mb-xl grid grid-cols-1 gap-md sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Total Reviews</p>
                <p class="font-serif text-headline-sm text-primary">
                    {{ stats.total }}
                </p>
            </div>
            <div class="rounded-xl border-l-4 border-l-secondary-container bg-white p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs flex items-center gap-xs text-label-caps text-outline">
                    Pending Approval
                    <span
                        v-if="stats.pending > 0"
                        class="size-2 animate-pulse rounded-full bg-secondary-container"
                    />
                </p>
                <p class="font-serif text-headline-sm text-on-secondary-container">
                    {{ stats.pending }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Approved</p>
                <p class="font-serif text-headline-sm text-green-700">
                    {{ stats.approved }}
                </p>
            </div>
            <div class="rounded-xl border-l-4 border-l-error bg-white p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Flagged</p>
                <p class="font-serif text-headline-sm text-error">
                    {{ stats.flagged }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-md shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
                <p class="mb-xs text-label-caps text-outline">Average Rating</p>
                <div class="flex items-center gap-sm">
                    <p class="font-serif text-headline-sm text-secondary">
                        {{ Number(stats.avg_rating ?? 0).toFixed(1) }}
                    </p>
                    <div class="flex text-secondary">
                        <IconStarFilled
                            v-for="n in 5"
                            :key="n"
                            class="size-4"
                            :class="n <= Math.round(stats.avg_rating ?? 0) ? 'opacity-100' : 'opacity-30'"
                        />
                    </div>
                </div>
            </div>
        </section>

        <!-- Filters -->
        <section class="mb-lg rounded-xl border border-outline-variant/30 bg-surface-container-lowest p-lg shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
            <div class="mb-lg flex flex-col items-start justify-between gap-lg xl:flex-row xl:items-center">
                <div class="flex flex-wrap items-center gap-sm">
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

                <div class="flex items-center gap-sm">
                    <span class="mr-sm text-label-caps text-outline">Rating:</span>
                    <div class="flex gap-xs">
                        <button
                            v-for="n in 5"
                            :key="n"
                            type="button"
                            class="flex items-center justify-center rounded-lg border p-2 transition-all"
                            :class="
                                rating === n
                                    ? 'border-secondary text-secondary'
                                    : 'border-outline-variant text-on-surface-variant hover:border-secondary hover:text-secondary'
                            "
                            :title="`${n} star${n > 1 ? 's' : ''}`"
                            @click="setRatingFilter(n)"
                        >
                            <IconStarFilled v-if="rating === n" class="size-5" />
                            <IconStar v-else class="size-5" stroke="1.5" />
                            <span class="ml-1 text-xs font-semibold">{{ n }}</span>
                        </button>
                    </div>
                </div>
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
                        placeholder="Search reviews, customers..."
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
        <div class="relative overflow-hidden rounded-xl border border-outline-variant/30 bg-surface-container-lowest shadow-[0_2px_12px_rgba(0,0,0,0.07)]">
            <!-- Bulk bar -->
            <div
                v-if="selectedIds.length > 0"
                class="absolute left-0 right-0 top-0 z-20 flex h-16 items-center justify-between bg-primary-container px-lg text-white"
            >
                <div class="flex items-center gap-lg">
                    <p class="font-bold text-on-primary-container">
                        <span>{{ selectedIds.length }}</span>
                        review{{ selectedIds.length === 1 ? '' : 's' }} selected
                    </p>
                    <div class="hidden h-6 w-px bg-on-primary-container/30 sm:block" />
                    <div class="flex flex-wrap gap-sm">
                        <button
                            v-if="canApprove"
                            type="button"
                            class="inline-flex items-center gap-xs rounded-full bg-green-600 px-md py-1.5 text-xs font-bold transition-colors hover:bg-green-500"
                            @click="bulkApprove"
                        >
                            <IconCircleCheck class="size-4" stroke="1.5" />
                            Approve
                        </button>
                        <button
                            v-if="canApprove"
                            type="button"
                            class="inline-flex items-center gap-xs rounded-full bg-secondary px-md py-1.5 text-xs font-bold transition-colors hover:bg-secondary/80"
                            @click="bulkReject"
                        >
                            <IconBan class="size-4" stroke="1.5" />
                            Reject
                        </button>
                        <button
                            v-if="canDelete"
                            type="button"
                            class="inline-flex items-center gap-xs rounded-full bg-error px-md py-1.5 text-xs font-bold transition-colors hover:bg-error/80"
                            @click="bulkDelete"
                        >
                            <IconTrash class="size-4" stroke="1.5" />
                            Delete
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="text-on-primary-container/70 transition-colors hover:text-white"
                    @click="clearSelection"
                >
                    <IconX class="size-5" stroke="1.5" />
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[960px] border-collapse text-left">
                    <thead>
                        <tr class="border-b border-outline-variant/30 bg-surface-container-low">
                            <th class="w-12 p-md text-center">
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-outline-variant text-secondary focus:ring-secondary"
                                    :checked="allPageSelected"
                                    :indeterminate.prop="
                                        selectedIds.length > 0 && !allPageSelected
                                    "
                                    @change="toggleSelectAll"
                                >
                            </th>
                            <th class="p-md text-label-caps text-outline">
                                Customer &amp; Review
                            </th>
                            <th class="p-md text-label-caps text-outline">Product</th>
                            <th class="p-md text-label-caps text-outline">Rating</th>
                            <th class="p-md text-label-caps text-outline">Status</th>
                            <th class="p-md text-label-caps text-outline">Date</th>
                            <th class="p-md text-label-caps text-outline">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/10">
                        <tr v-if="rows.length === 0">
                            <td colspan="7" class="p-xl text-center text-body-sm text-on-surface-variant">
                                No reviews match your filters.
                            </td>
                        </tr>

                        <tr
                            v-for="review in rows"
                            :key="review.id"
                            class="group cursor-pointer transition-colors hover:bg-surface-container/30"
                            :class="rowClass(review)"
                            @click="openPanel(review)"
                        >
                            <td class="p-md text-center" @click.stop>
                                <input
                                    type="checkbox"
                                    class="size-4 rounded border-outline-variant text-secondary focus:ring-secondary"
                                    :checked="selectedIds.includes(review.id)"
                                    @change="toggleSelect(review.id)"
                                >
                            </td>
                            <td class="p-md">
                                <div class="flex gap-md">
                                    <img
                                        v-if="review.customer?.avatar"
                                        :src="review.customer.avatar"
                                        :alt="review.customer.name"
                                        class="size-10 shrink-0 rounded-full object-cover"
                                    >
                                    <div
                                        v-else
                                        class="flex size-10 shrink-0 items-center justify-center rounded-full bg-primary-container text-xs font-bold text-on-primary-container"
                                    >
                                        {{ customerInitials(review.customer?.name) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="mb-xs flex items-center gap-xs">
                                            <span class="truncate text-sm font-bold text-primary">
                                                {{ review.customer?.name ?? 'Customer' }}
                                            </span>
                                            <IconCheck
                                                v-if="review.is_verified_purchase"
                                                class="size-3.5 shrink-0 text-blue-500"
                                                stroke="2"
                                                title="Verified purchase"
                                            />
                                            <IconCamera
                                                v-if="(review.photos ?? []).length"
                                                class="size-3.5 shrink-0 text-outline"
                                                stroke="1.5"
                                                title="Contains photos"
                                            />
                                        </div>
                                        <p class="mb-1 text-sm font-bold text-primary">
                                            {{ review.title }}
                                        </p>
                                        <p class="line-clamp-1 text-body-sm italic text-on-surface-variant">
                                            “{{ review.body_excerpt }}”
                                        </p>
                                        <p
                                            v-if="review.status === 'flagged' && review.flag_reason"
                                            class="mt-1 text-xs text-error"
                                        >
                                            Flag Reason: {{ review.flag_reason }}
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-md">
                                <div class="flex items-center gap-sm">
                                    <div class="size-8 shrink-0 overflow-hidden rounded-lg bg-surface-container-high">
                                        <img
                                            v-if="review.product?.thumbnail"
                                            :src="review.product.thumbnail"
                                            :alt="review.product.name"
                                            class="size-full object-cover"
                                        >
                                        <div
                                            v-else
                                            class="flex size-full items-center justify-center"
                                        >
                                            <IconPhoto class="size-4 text-outline" stroke="1.5" />
                                        </div>
                                    </div>
                                    <span class="max-w-[140px] truncate text-body-sm font-medium text-primary">
                                        {{ review.product?.name ?? '—' }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-md">
                                <div class="flex items-center gap-xs">
                                    <div class="flex text-secondary-container">
                                        <IconStarFilled
                                            v-for="n in 5"
                                            :key="n"
                                            class="size-4"
                                            :class="n <= review.rating ? 'opacity-100' : 'opacity-25'"
                                        />
                                    </div>
                                    <span class="text-body-sm font-medium text-primary">
                                        {{ Number(review.rating).toFixed(1) }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-md">
                                <span
                                    class="inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold"
                                    :class="statusBadgeClass(review.status)"
                                >
                                    {{ statusLabel(review.status) }}
                                </span>
                            </td>
                            <td class="p-md text-body-sm text-on-surface-variant">
                                {{ formatDate(review.created_at) }}
                            </td>
                            <td class="p-md" @click.stop>
                                <div class="flex items-center gap-1">
                                    <template v-if="canApprove && review.status === 'pending'">
                                        <button
                                            type="button"
                                            class="rounded-full p-2 text-green-700 transition hover:bg-green-50"
                                            title="Approve"
                                            @click="approveReview(review)"
                                        >
                                            <IconCheck class="size-4" stroke="2" />
                                        </button>
                                        <button
                                            type="button"
                                            class="rounded-full p-2 text-error transition hover:bg-red-50"
                                            title="Reject"
                                            @click="rejectReview(review)"
                                        >
                                            <IconX class="size-4" stroke="2" />
                                        </button>
                                    </template>

                                    <template v-else-if="canApprove && review.status === 'flagged'">
                                        <button
                                            type="button"
                                            class="rounded-full p-2 text-green-700 transition hover:bg-green-50"
                                            title="Clear flag"
                                            @click="clearFlag(review)"
                                        >
                                            <IconCircleCheck class="size-4" stroke="1.5" />
                                        </button>
                                        <button
                                            v-if="canDelete"
                                            type="button"
                                            class="rounded-full p-2 text-error transition hover:bg-red-50"
                                            title="Delete"
                                            @click="deleteReview(review)"
                                        >
                                            <IconTrash class="size-4" stroke="1.5" />
                                        </button>
                                    </template>

                                    <template v-else-if="canApprove && review.status === 'rejected'">
                                        <button
                                            type="button"
                                            class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container-low"
                                            title="Undo reject"
                                            @click="undoReject(review)"
                                        >
                                            <IconRefresh class="size-4" stroke="1.5" />
                                        </button>
                                    </template>

                                    <Menu as="div" class="relative">
                                        <MenuButton
                                            class="rounded-full p-2 text-on-surface-variant transition hover:bg-surface-container-low"
                                        >
                                            <IconDotsVertical class="size-4" stroke="1.5" />
                                        </MenuButton>
                                        <MenuItems
                                            class="absolute right-0 z-10 mt-1 w-40 overflow-hidden rounded-lg border border-outline-variant bg-white shadow-lg focus:outline-none"
                                        >
                                            <MenuItem v-slot="{ active }">
                                                <button
                                                    type="button"
                                                    class="block w-full px-3 py-2 text-left text-body-sm"
                                                    :class="active ? 'bg-surface-container-low' : ''"
                                                    @click="openPanel(review)"
                                                >
                                                    View details
                                                </button>
                                            </MenuItem>
                                            <MenuItem
                                                v-if="canApprove && review.status !== 'flagged'"
                                                v-slot="{ active }"
                                            >
                                                <button
                                                    type="button"
                                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-body-sm"
                                                    :class="active ? 'bg-surface-container-low' : ''"
                                                    @click="updateStatus(review, 'flagged', 'Flagged by admin')"
                                                >
                                                    <IconFlag class="size-4" stroke="1.5" />
                                                    Flag
                                                </button>
                                            </MenuItem>
                                            <MenuItem
                                                v-if="canDelete"
                                                v-slot="{ active }"
                                            >
                                                <button
                                                    type="button"
                                                    class="flex w-full items-center gap-2 px-3 py-2 text-left text-body-sm text-error"
                                                    :class="active ? 'bg-red-50' : ''"
                                                    @click="deleteReview(review)"
                                                >
                                                    <IconTrash class="size-4" stroke="1.5" />
                                                    Delete
                                                </button>
                                            </MenuItem>
                                        </MenuItems>
                                    </Menu>
                                </div>
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

        <ReviewDetailPanel
            :open="panelOpen"
            :review="selectedReview"
            :can-approve="canApprove"
            :can-delete="canDelete"
            :can-respond="canRespond"
            @close="closePanel"
            @approve="approveReview"
            @reject="rejectReview"
            @delete="deleteReview"
        />
    </AdminLayout>
</template>
