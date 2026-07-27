<script setup>
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import CategoryTreeList from '@/Components/Admin/CategoryTreeList.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconInfoCircle,
    IconPlus,
    IconSearch,
    IconX,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    categoryTree: {
        type: Array,
        default: () => [],
    },
    stats: {
        type: Object,
        default: () => ({ total: 0, active: 0, empty: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({ search: '', status: '', view: 'tree' }),
    },
    view: {
        type: String,
        default: 'tree',
    },
    rootPagination: {
        type: Object,
        default: null,
    },
});

const page = usePage();
const search = ref(props.filters.search ?? '');
const status = ref(props.filters.status ?? '');
const showInfoBanner = ref(true);
const expandedIds = new Set();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.categories ?? {},
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

const statusOptions = [
    { value: '', label: 'All statuses' },
    { value: 'active', label: 'Active' },
    { value: 'draft', label: 'Draft' },
    { value: 'archived', label: 'Archived' },
];

const totalVisible = computed(() => countNodes(props.categoryTree));

const rootSummary = computed(() => {
    const pagination = props.rootPagination;

    if (!pagination?.total) {
        return 'No root categories';
    }

    if (pagination.total === 1) {
        return 'Showing 1 root category';
    }

    return `Showing ${pagination.from}–${pagination.to} of ${pagination.total} root categories`;
});

function countNodes(nodes) {
    return nodes.reduce(
        (sum, node) => sum + 1 + countNodes(node.children ?? []),
        0,
    );
}

function collectExpandableIds(nodes, set) {
    for (const node of nodes) {
        if (node.children?.length) {
            set.add(node.id);
            collectExpandableIds(node.children, set);
        }
    }
}

watch(
    () => props.categoryTree,
    (tree) => {
        expandedIds.clear();
        collectExpandableIds(tree, expandedIds);
    },
    { immediate: true, deep: true },
);

const applyFilters = () => {
    router.get(
        route('admin.categories.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            view: props.view,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    applyFilters();
};

watch(status, applyFilters);

let searchDebounce;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
});
</script>

<template>
    <AdminLayout title="Categories" breadcrumb="Categories">
        <Head title="Categories" />

        <section class="mb-lg flex flex-col justify-between gap-4 sm:flex-row sm:items-end pt-4">
            <div>
                <nav
                    class="mb-2 flex items-center gap-2 text-label-caps uppercase text-on-surface-variant"
                >
                    <span>Catalog</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-on-surface">Categories</span>
                </nav>
                <h2 class="font-serif text-headline-lg text-primary">
                    Category Management
                </h2>
            </div>

            <Link
                v-if="canCreate"
                :href="route('admin.categories.create')"
                class="inline-flex h-12 shrink-0 items-center gap-2 rounded-full bg-primary px-lg font-sans text-label-caps uppercase text-on-primary shadow-md transition-all hover:opacity-90"
            >
                <IconPlus class="size-5" stroke="2" />
                Add New Root Category
            </Link>
        </section>

        <section class="mb-lg grid grid-cols-1 gap-gutter sm:grid-cols-3">
            <div class="card-elevated p-lg">
                <p class="text-label-caps uppercase text-outline">Total</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.total }}
                </p>
            </div>
            <div class="card-elevated p-lg">
                <p class="text-label-caps uppercase text-outline">Active</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.active }}
                </p>
            </div>
            <div class="card-elevated p-lg">
                <p class="text-label-caps uppercase text-outline">Empty</p>
                <p class="mt-1 font-serif text-headline-sm text-primary">
                    {{ stats.empty }}
                </p>
            </div>
        </section>

        <section class="card-elevated mb-lg p-lg">
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="relative flex-1">
                    <IconSearch
                        class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline"
                        stroke="1.5"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search categories..."
                        class="input-field pl-10"
                    />
                </div>
                <select
                    v-model="status"
                    class="input-field sm:max-w-[200px]"
                >
                    <option
                        v-for="option in statusOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
                <button
                    type="button"
                    class="btn-ghost btn-sm"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>
        </section>

        <section
            v-if="showInfoBanner"
            class="mb-lg flex items-start gap-md rounded-xl border border-[#FFD9B3] bg-[#FFF4E5] p-md"
        >
            <IconInfoCircle
                class="mt-0.5 size-5 shrink-0 text-secondary-container"
                stroke="1.5"
            />
            <div class="flex-1">
                <p class="mb-1 font-sans text-title-lg font-bold text-secondary-container">
                    Organize Your Menu
                </p>
                <p class="text-body-lg text-on-surface-variant">
                    Drag and drop categories to reorder them or nest them within
                    each other. Changes are saved immediately after each drop.
                </p>
            </div>
            <button
                type="button"
                class="p-1 text-on-surface-variant transition-colors hover:text-primary"
                @click="showInfoBanner = false"
            >
                <IconX class="size-5" stroke="1.5" />
            </button>
        </section>

        <section
            class="overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-card"
        >
            <div
                class="hidden border-b border-outline-variant bg-surface-container-low px-lg py-4 font-sans text-label-caps uppercase tracking-wider text-on-surface-variant lg:grid lg:grid-cols-[1fr_200px_150px_150px_180px]"
            >
                <div>Category Hierarchy</div>
                <div>Slug</div>
                <div class="text-center">Products</div>
                <div class="text-center">Status</div>
                <div class="text-right">Actions</div>
            </div>

            <div class="overflow-x-auto p-md">
                <CategoryTreeList
                    v-if="categoryTree.length"
                    :nodes="categoryTree"
                    :parent-id="null"
                    :expanded-ids="expandedIds"
                    :can-edit="canEdit"
                    :can-delete="canDelete"
                    :can-create="canCreate"
                />

                <div
                    v-else
                    class="py-16 text-center text-on-surface-variant"
                >
                    <p class="font-sans text-sm font-semibold text-primary">
                        No categories found
                    </p>
                    <p class="mt-1 text-sm">
                        Try adjusting your filters or add a root category.
                    </p>
                    <Link
                        v-if="canCreate"
                        :href="route('admin.categories.create')"
                        class="btn-primary btn-sm mt-4 inline-flex"
                    >
                        Add New Root Category
                    </Link>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-outline-variant bg-surface-container-low px-lg py-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div class="space-y-1">
                    <p class="text-body-sm text-on-surface-variant">
                        {{ rootSummary }}
                    </p>
                    <p
                        v-if="totalVisible"
                        class="text-body-sm text-on-surface-variant"
                    >
                        {{ totalVisible }} categor{{
                            totalVisible === 1 ? 'y' : 'ies'
                        }} on this page (including children)
                    </p>
                </div>
                <div class="flex flex-col items-start gap-3 sm:flex-row sm:items-center">
                    <p
                        v-if="canEdit"
                        class="text-body-sm text-on-surface-variant"
                    >
                        Drag rows to reorder — changes save automatically.
                    </p>
                    <AdminPagination
                        v-if="rootPagination"
                        :pagination="rootPagination"
                    />
                </div>
            </div>
        </section>
    </AdminLayout>
</template>
