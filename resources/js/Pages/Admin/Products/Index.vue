<script setup>
import AdminBottomSheet from '@/Components/Admin/AdminBottomSheet.vue';
import AdminPagination from '@/Components/Admin/AdminPagination.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    IconAdjustmentsHorizontal,
    IconChevronRight,
    IconDownload,
    IconDotsVertical,
    IconEdit,
    IconPlus,
    IconSearch,
    IconStar,
    IconStarFilled,
    IconTrash,
    IconX,
} from '@tabler/icons-vue';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    products: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    stats: {
        type: Object,
        default: () => ({
            total: 0,
            active: 0,
            out_of_stock: 0,
            featured: 0,
        }),
    },
    filters: {
        type: Object,
        default: () => ({
            search: '',
            status: '',
            stock_status: '',
            category_id: null,
            featured: null,
        }),
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();

const permissions = computed(
    () => page.props.auth?.user?.permissions?.products ?? {},
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
const stockStatus = ref(props.filters.stock_status ?? '');
const categoryId = ref(
    props.filters.category_id != null ? String(props.filters.category_id) : '',
);

const sheetSearch = ref(search.value);
const sheetStatus = ref(status.value);
const sheetStockStatus = ref(stockStatus.value);
const sheetCategoryId = ref(categoryId.value);

const filterSheetOpen = ref(false);
const actionSheetOpen = ref(false);
const actionProduct = ref(null);

const rows = computed(() => props.products?.data ?? []);
const pagination = computed(() => {
    const meta = props.products?.meta ?? {};
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
        return 'No products';
    }
    return `Showing ${from}–${to} of ${total} products`;
});

const inventoryCount = computed(
    () => pagination.value.total || props.stats.total || 0,
);

const selectedIds = ref([]);

const allPageSelected = computed(
    () =>
        rows.value.length > 0
        && rows.value.every((product) => selectedIds.value.includes(product.id)),
);

const toggleSelectAll = () => {
    if (allPageSelected.value) {
        const pageIds = new Set(rows.value.map((p) => p.id));
        selectedIds.value = selectedIds.value.filter((id) => !pageIds.has(id));
        return;
    }

    const merged = new Set([
        ...selectedIds.value,
        ...rows.value.map((p) => p.id),
    ]);
    selectedIds.value = [...merged];
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
    () => props.products?.data,
    () => {
        const visible = new Set(rows.value.map((p) => p.id));
        selectedIds.value = selectedIds.value.filter((id) => visible.has(id));
    },
);

const applyFilters = () => {
    router.get(
        route('admin.products.index'),
        {
            search: search.value || undefined,
            status: status.value || undefined,
            stock_status: stockStatus.value || undefined,
            category_id: categoryId.value || undefined,
        },
        { preserveState: true, replace: true },
    );
};

const clearFilters = () => {
    search.value = '';
    status.value = '';
    stockStatus.value = '';
    categoryId.value = '';
    applyFilters();
};

let searchDebounce;
watch(search, () => {
    clearTimeout(searchDebounce);
    searchDebounce = setTimeout(applyFilters, 350);
});
watch([status, stockStatus, categoryId], applyFilters);

const openFilterSheet = () => {
    sheetSearch.value = search.value;
    sheetStatus.value = status.value;
    sheetStockStatus.value = stockStatus.value;
    sheetCategoryId.value = categoryId.value;
    filterSheetOpen.value = true;
};

const resetSheetFilters = () => {
    sheetSearch.value = '';
    sheetStatus.value = '';
    sheetStockStatus.value = '';
    sheetCategoryId.value = '';
};

const applySheetFilters = () => {
    search.value = sheetSearch.value;
    status.value = sheetStatus.value;
    stockStatus.value = sheetStockStatus.value;
    categoryId.value = sheetCategoryId.value;
    filterSheetOpen.value = false;
    applyFilters();
};

const openActionSheet = (product) => {
    actionProduct.value = product;
    actionSheetOpen.value = true;
};

const closeActionSheet = () => {
    actionSheetOpen.value = false;
    actionProduct.value = null;
};

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

const stockMeta = (product) => {
    const qty = product.stock_quantity ?? 0;
    if (qty <= 0) {
        return { label: 'None', color: 'bg-red-500', width: 0, textClass: 'text-red-600' };
    }
    if (qty <= (product.low_stock_threshold ?? 5)) {
        return {
            label: 'Low',
            color: 'bg-orange-400',
            width: Math.max(12, Math.min(100, (qty / 50) * 100)),
            textClass: 'text-orange-600',
        };
    }
    if (qty < 25) {
        return {
            label: 'Mid',
            color: 'bg-orange-400',
            width: Math.min(100, (qty / 50) * 100),
            textClass: 'text-on-surface-variant/60',
        };
    }
    return {
        label: 'High',
        color: 'bg-green-500',
        width: Math.min(100, (qty / 50) * 100),
        textClass: 'text-on-surface-variant/60',
    };
};

const statusBadge = (product) => {
    if (product.stock_status === 'out_of_stock') {
        return {
            label: 'Out of Stock',
            className: 'bg-error/10 text-error',
        };
    }

    return (
        {
            active: {
                label: 'Active',
                className: 'bg-success/10 text-success',
            },
            draft: {
                label: 'Draft',
                className: 'bg-surface-container-highest text-on-surface-variant',
            },
            archived: {
                label: 'Archived',
                className: 'bg-surface-container-high text-on-surface-variant',
            },
        }[product.status] ?? {
            label: product.status,
            className: 'bg-surface-container-high text-on-surface-variant',
        }
    );
};

const cardStockLabel = (product) => {
    const qty = product.stock_quantity ?? 0;
    if (qty <= 0) {
        return { text: 'Stock: 0', className: 'text-outline' };
    }
    if (qty <= (product.low_stock_threshold ?? 5)) {
        return { text: `Low Stock: ${qty}`, className: 'text-error font-bold' };
    }
    return {
        text: `Stock: ${qty}`,
        className: 'text-on-surface-variant',
    };
};

const displayPrice = (product) =>
    product.is_on_sale ? product.sale_price : product.regular_price;

const placeholderImage =
    'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=200&q=80';

const productImage = (product) =>
    product.thumbnail || product.og_image || placeholderImage;

const deleteProduct = (product) => {
    if (!canDelete.value) {
        return;
    }
    if (!confirm(`Delete "${product.name}"? This cannot be undone.`)) {
        return;
    }
    closeActionSheet();
    router.delete(route('admin.products.destroy', product.id), {
        preserveScroll: true,
        onSuccess: () => {
            selectedIds.value = selectedIds.value.filter((id) => id !== product.id);
        },
    });
};

const bulkDelete = () => {
    if (!canDelete.value || selectedIds.value.length === 0) {
        return;
    }
    if (
        !confirm(
            `Delete ${selectedIds.value.length} selected product(s)? This cannot be undone.`,
        )
    ) {
        return;
    }

    const ids = [...selectedIds.value];
    const deleteNext = () => {
        const id = ids.shift();
        if (!id) {
            clearSelection();
            return;
        }
        router.delete(route('admin.products.destroy', id), {
            preserveScroll: true,
            onFinish: deleteNext,
        });
    };
    deleteNext();
};

const toggleFeatured = (product) => {
    if (!canEdit.value) {
        return;
    }

    const payload = {
        name: product.name,
        slug: product.slug,
        sku: product.sku,
        short_description: product.short_description,
        description: product.description,
        regular_price: product.regular_price,
        sale_price: product.sale_price,
        cost_price: product.cost_price,
        barcode: product.barcode,
        stock_quantity: product.stock_quantity,
        low_stock_threshold: product.low_stock_threshold,
        allow_backorders: product.allow_backorders,
        stock_status: product.stock_status,
        is_featured: !product.is_featured,
        is_active: product.is_active,
        status: product.status,
        published_at: product.published_at,
        meta_title: product.meta_title,
        meta_description: product.meta_description,
        meta_keywords: product.meta_keywords ?? [],
        canonical_url: product.canonical_url || null,
        category_ids:
            product.category_ids
            ?? product.categories?.map((c) => c.id)
            ?? [],
    };

    if (Array.isArray(product.attribute_value_ids)) {
        payload.attribute_value_ids = product.attribute_value_ids;
    }

    closeActionSheet();
    router.put(route('admin.products.update', product.id), payload, {
        preserveScroll: true,
    });
};
</script>

<template>
    <AdminLayout title="Products" breadcrumb="Products">
        <Head title="Products" />

        <!-- Page header -->
        <section class="mb-lg space-y-md pt-2 md:flex md:flex-row md:items-center md:justify-between md:gap-md md:space-y-0 md:pt-4">
            <div>
                <nav
                    class="mb-2 hidden items-center gap-2 text-label-caps uppercase text-on-surface-variant md:flex"
                >
                    <span>Catalog</span>
                    <IconChevronRight class="size-3.5" stroke="2" />
                    <span class="text-on-surface">Products</span>
                </nav>
                <h2 class="font-serif text-headline-sm text-primary md:text-headline-lg">
                    Products
                </h2>
                <p class="mt-1 hidden text-body-sm text-on-surface-variant md:block">
                    Manage your bakery inventory and catalogs
                </p>
            </div>

            <div class="flex flex-col gap-sm md:flex-row md:flex-wrap md:gap-3">
                <button
                    type="button"
                    disabled
                    title="Coming soon"
                    class="inline-flex h-12 w-full cursor-not-allowed items-center justify-center gap-2 rounded-full border border-primary-container font-sans text-sm font-bold text-primary-container opacity-50 md:h-10 md:w-auto md:px-lg"
                >
                    <IconDownload class="size-5 md:size-[18px]" stroke="1.5" />
                    <span class="md:hidden">Export Catalog</span>
                    <span class="hidden md:inline">Export</span>
                </button>
                <Link
                    v-if="canCreate"
                    :href="route('admin.products.create')"
                    class="inline-flex h-12 w-full items-center justify-center gap-2 rounded-full bg-secondary px-lg font-sans text-sm font-bold text-on-secondary shadow-md transition-all hover:opacity-90 active:scale-95 md:h-10 md:w-auto"
                >
                    <IconPlus class="size-5 md:size-[18px]" stroke="2" />
                    <span class="md:hidden">Add New Product</span>
                    <span class="hidden md:inline">Add Product</span>
                </Link>
            </div>
        </section>

        <!-- Stats -->
        <section class="mb-lg grid grid-cols-2 gap-sm md:gap-md lg:grid-cols-4">
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-md shadow-card md:p-lg">
                <p class="mb-xs text-label-caps uppercase text-on-surface-variant">
                    Total
                </p>
                <p class="font-serif text-[24px] font-bold leading-tight text-primary md:text-headline-sm md:font-semibold">
                    {{ stats.total }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-md shadow-card md:p-lg">
                <p class="mb-xs text-label-caps uppercase text-on-surface-variant">
                    Active
                </p>
                <p class="font-serif text-[24px] font-bold leading-tight text-primary md:text-headline-sm md:font-semibold">
                    {{ stats.active }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-md shadow-card md:p-lg">
                <p class="mb-xs text-label-caps uppercase text-on-surface-variant">
                    Out of Stock
                </p>
                <p class="font-serif text-[24px] font-bold leading-tight text-error md:text-headline-sm md:font-semibold md:text-primary">
                    {{ stats.out_of_stock }}
                </p>
            </div>
            <div class="rounded-xl border border-outline-variant/20 bg-surface-container-lowest p-md shadow-card md:p-lg">
                <p class="mb-xs text-label-caps uppercase text-on-surface-variant">
                    Featured
                </p>
                <p class="font-serif text-[24px] font-bold leading-tight text-primary md:text-headline-sm md:font-semibold">
                    {{ stats.featured }}
                </p>
            </div>
        </section>

        <!-- Desktop / tablet filters -->
        <section class="card-elevated mb-lg hidden p-lg md:block">
            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                <div class="relative w-full sm:w-1/2 sm:min-w-[240px] sm:flex-none">
                    <IconSearch
                        class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline"
                        stroke="1.5"
                    />
                    <input
                        v-model="search"
                        type="search"
                        placeholder="Search products..."
                        class="input-field w-full pl-10"
                    />
                </div>
                <select
                    v-model="categoryId"
                    class="input-field w-full sm:w-auto sm:min-w-[160px] sm:max-w-[200px]"
                >
                    <option value="">All Categories</option>
                    <option
                        v-for="option in categoryOptions"
                        :key="option.id"
                        :value="String(option.id)"
                    >
                        {{ option.name }}
                    </option>
                </select>
                <select
                    v-model="status"
                    class="input-field w-full sm:w-auto sm:min-w-[140px] sm:max-w-[180px]"
                >
                    <option value="">All Statuses</option>
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="archived">Archived</option>
                </select>
                <select
                    v-model="stockStatus"
                    class="input-field w-full sm:w-auto sm:min-w-[140px] sm:max-w-[180px]"
                >
                    <option value="">Stock Level</option>
                    <option value="in_stock">In Stock</option>
                    <option value="out_of_stock">Out of Stock</option>
                    <option value="on_backorder">On Backorder</option>
                </select>
                <button
                    type="button"
                    class="btn-ghost btn-sm shrink-0"
                    @click="clearFilters"
                >
                    Clear
                </button>
            </div>
        </section>

        <!-- Mobile inventory header + filter chip -->
        <div class="mb-md flex items-center justify-between md:hidden">
            <h3 class="text-label-caps uppercase text-on-surface-variant">
                Inventory ({{ inventoryCount }})
            </h3>
            <button
                type="button"
                class="inline-flex items-center gap-1 rounded-full bg-tertiary-fixed px-md py-sm text-body-sm font-bold text-tertiary"
                @click="openFilterSheet"
            >
                <IconAdjustmentsHorizontal class="size-[18px]" stroke="1.5" />
                Filter &amp; Sort
            </button>
        </div>

        <!-- Mobile product cards -->
        <section class="mb-lg space-y-sm md:hidden">
            <div
                v-for="product in rows"
                :key="`card-${product.id}`"
                class="relative flex items-center gap-md rounded-xl bg-white p-sm shadow-card transition-colors active:bg-surface-container"
                :class="{
                    'opacity-70': product.stock_status === 'out_of_stock',
                }"
            >
                <div
                    class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-surface-variant"
                    :class="{
                        grayscale: product.stock_status === 'out_of_stock',
                    }"
                >
                    <img
                        :src="productImage(product)"
                        :alt="product.name"
                        class="h-full w-full object-cover"
                    />
                </div>
                <div class="min-w-0 flex-1 pr-lg">
                    <p class="truncate font-sans text-title-lg text-primary">
                        {{ product.name }}
                    </p>
                    <p class="text-[12px] font-medium text-on-surface-variant">
                        SKU: {{ product.sku }}
                    </p>
                    <div class="mt-1 flex flex-wrap gap-1">
                        <span
                            v-for="category in (product.categories ?? []).slice(0, 2)"
                            :key="category.id"
                            class="rounded-md bg-surface-container-high px-sm py-[2px] text-[10px] font-bold uppercase tracking-wider text-on-surface-variant"
                        >
                            {{ category.name }}
                        </span>
                        <span
                            v-if="product.is_featured"
                            class="rounded-md bg-secondary-container/10 px-sm py-[2px] text-[10px] font-bold uppercase tracking-wider text-secondary"
                        >
                            Featured
                        </span>
                        <span
                            v-if="product.stock_status === 'out_of_stock'"
                            class="rounded-md bg-outline-variant px-sm py-[2px] text-[10px] font-bold uppercase tracking-wider text-white"
                        >
                            Out of Stock
                        </span>
                    </div>
                    <div class="mt-sm flex items-center justify-between">
                        <span class="text-body-lg font-bold text-secondary">
                            {{ formatMoney(displayPrice(product)) }}
                        </span>
                        <span
                            class="text-body-sm"
                            :class="cardStockLabel(product).className"
                        >
                            <template v-if="(product.stock_quantity ?? 0) > (product.low_stock_threshold ?? 5)">
                                Stock:
                                <span class="font-bold text-primary">{{ product.stock_quantity }}</span>
                            </template>
                            <template v-else>
                                {{ cardStockLabel(product).text }}
                            </template>
                        </span>
                    </div>
                </div>
                <button
                    type="button"
                    class="absolute right-sm top-sm p-1 text-outline"
                    aria-label="Product actions"
                    @click="openActionSheet(product)"
                >
                    <IconDotsVertical class="size-5" stroke="1.5" />
                </button>
            </div>

            <div
                v-if="rows.length === 0"
                class="rounded-xl bg-white py-16 text-center text-on-surface-variant shadow-card"
            >
                <p class="font-sans text-sm font-semibold text-primary">
                    No products found
                </p>
                <p class="mt-1 text-sm">
                    Try adjusting your filters or add a new product.
                </p>
                <Link
                    v-if="canCreate"
                    :href="route('admin.products.create')"
                    class="btn-primary btn-sm mt-4 inline-flex"
                >
                    Add New Product
                </Link>
            </div>

            <div
                v-if="pagination.last_page > 1"
                class="pt-md"
            >
                <AdminPagination :pagination="pagination" />
            </div>
            <p
                v-else-if="rows.length > 0"
                class="pt-md text-center text-body-sm text-on-surface-variant"
            >
                {{ showingLabel }}
            </p>
        </section>

        <!-- Tablet / desktop table -->
        <section
            class="hidden overflow-hidden rounded-xl border border-outline-variant bg-surface-container-lowest shadow-card md:block"
        >
            <div
                v-if="selectedIds.length > 0"
                class="flex items-center justify-between bg-primary px-lg py-3 text-on-primary"
            >
                <div class="flex flex-wrap items-center gap-lg">
                    <span class="text-body-sm font-medium">
                        {{ selectedIds.length }} product(s) selected
                    </span>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            disabled
                            title="Coming soon"
                            class="cursor-not-allowed text-sm text-on-primary/50"
                        >
                            Archive
                        </button>
                        <button
                            type="button"
                            disabled
                            title="Coming soon"
                            class="cursor-not-allowed text-sm text-on-primary/50"
                        >
                            Feature
                        </button>
                        <button
                            v-if="canDelete"
                            type="button"
                            class="flex items-center gap-1 text-sm text-error-container hover:opacity-90"
                            @click="bulkDelete"
                        >
                            <IconTrash class="size-4" stroke="1.5" />
                            Delete
                        </button>
                    </div>
                </div>
                <button
                    type="button"
                    class="text-on-primary/60 hover:text-on-primary"
                    @click="clearSelection"
                >
                    <IconX class="size-5" stroke="1.5" />
                </button>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr
                            class="border-b border-outline-variant/30 bg-surface-container-low/50 font-sans text-[11px] uppercase tracking-wider text-on-surface-variant"
                        >
                            <th class="w-12 px-md py-md text-center lg:px-lg">
                                <input
                                    type="checkbox"
                                    class="rounded border-outline-variant text-secondary focus:ring-secondary"
                                    :checked="allPageSelected"
                                    :disabled="rows.length === 0"
                                    @change="toggleSelectAll"
                                />
                            </th>
                            <th class="px-md py-md">Product</th>
                            <th class="px-md py-md">Categories</th>
                            <th class="px-md py-md">Price</th>
                            <th class="px-md py-md">Stock</th>
                            <th class="px-md py-md text-center">Status</th>
                            <th class="hidden px-md py-md lg:table-cell">Featured</th>
                            <th class="hidden px-md py-md lg:table-cell">Created</th>
                            <th class="px-md py-md text-right lg:px-lg">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant/20 text-body-sm">
                        <tr
                            v-for="product in rows"
                            :key="product.id"
                            class="transition-colors hover:bg-surface-container-low/30"
                            :class="{
                                'bg-surface-container-low/30': selectedIds.includes(
                                    product.id,
                                ),
                            }"
                        >
                            <td class="px-md py-md text-center lg:px-lg">
                                <input
                                    type="checkbox"
                                    class="rounded border-outline-variant text-secondary focus:ring-secondary"
                                    :checked="selectedIds.includes(product.id)"
                                    @change="toggleSelect(product.id)"
                                />
                            </td>
                            <td class="px-md py-md">
                                <div class="flex items-center gap-md">
                                    <div
                                        class="h-12 w-12 shrink-0 overflow-hidden rounded-lg bg-surface-container"
                                    >
                                        <img
                                            :src="productImage(product)"
                                            :alt="product.name"
                                            class="h-full w-full object-cover"
                                            width="48"
                                            height="48"
                                        />
                                    </div>
                                    <div>
                                        <div class="font-sans text-[14px] font-semibold text-primary">
                                            {{ product.name }}
                                        </div>
                                        <div class="text-[11px] text-on-surface-variant">
                                            SKU: {{ product.sku }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-md py-md">
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="category in product.categories ?? []"
                                        :key="category.id"
                                        class="rounded-md bg-outline-variant/30 px-2 py-1 text-[10px] font-bold text-primary-container"
                                    >
                                        {{ category.name }}
                                    </span>
                                    <span
                                        v-if="!(product.categories?.length)"
                                        class="text-on-surface-variant"
                                    >
                                        —
                                    </span>
                                </div>
                            </td>
                            <td class="px-md py-md font-sans text-[14px] font-semibold text-primary">
                                <template v-if="product.is_on_sale">
                                    <span>{{ formatMoney(product.sale_price) }}</span>
                                    <span
                                        class="ml-1 text-[11px] font-normal text-on-surface-variant line-through"
                                    >
                                        {{ formatMoney(product.regular_price) }}
                                    </span>
                                </template>
                                <template v-else>
                                    {{ formatMoney(product.regular_price) }}
                                </template>
                            </td>
                            <td class="px-md py-md">
                                <div class="flex w-16 flex-col">
                                    <span
                                        class="text-[14px] font-semibold"
                                        :class="stockMeta(product).textClass.includes('text-') ? stockMeta(product).textClass : 'text-primary'"
                                    >
                                        {{ product.stock_quantity }}
                                    </span>
                                    <div
                                        class="mt-1 h-1 w-full overflow-hidden rounded-full bg-outline-variant"
                                    >
                                        <div
                                            class="h-full rounded-full"
                                            :class="stockMeta(product).color"
                                            :style="{
                                                width: `${stockMeta(product).width}%`,
                                            }"
                                        />
                                    </div>
                                </div>
                            </td>
                            <td class="px-md py-md text-center">
                                <span
                                    class="inline-flex rounded-full px-3 py-1 text-[10px] font-bold uppercase tracking-tight"
                                    :class="statusBadge(product).className"
                                >
                                    {{ statusBadge(product).label }}
                                </span>
                            </td>
                            <td class="hidden px-md py-md lg:table-cell">
                                <button
                                    v-if="canEdit"
                                    type="button"
                                    class="rounded-lg p-2 transition-colors hover:bg-primary-fixed-dim"
                                    :class="
                                        product.is_featured
                                            ? 'text-secondary'
                                            : 'text-on-surface-variant hover:text-secondary'
                                    "
                                    :title="
                                        product.is_featured
                                            ? 'Unfeature'
                                            : 'Feature'
                                    "
                                    @click="toggleFeatured(product)"
                                >
                                    <IconStarFilled
                                        v-if="product.is_featured"
                                        class="size-5"
                                    />
                                    <IconStar v-else class="size-5" stroke="1.5" />
                                </button>
                                <span
                                    v-else
                                    class="inline-flex p-2"
                                    :class="
                                        product.is_featured
                                            ? 'text-secondary'
                                            : 'text-on-surface-variant'
                                    "
                                >
                                    <IconStarFilled
                                        v-if="product.is_featured"
                                        class="size-5"
                                    />
                                    <IconStar v-else class="size-5" stroke="1.5" />
                                </span>
                            </td>
                            <td class="hidden px-md py-md text-on-surface-variant lg:table-cell">
                                {{ formatDate(product.created_at) }}
                            </td>
                            <td class="px-md py-md lg:px-lg">
                                <div class="flex items-center justify-end gap-1 text-on-surface-variant">
                                    <Link
                                        v-if="canEdit"
                                        :href="
                                            route('admin.products.edit', product.id)
                                        "
                                        class="rounded-lg p-1 transition-colors hover:text-secondary"
                                        title="Edit"
                                    >
                                        <IconEdit
                                            class="size-5"
                                            stroke="1.5"
                                        />
                                    </Link>
                                    <button
                                        v-if="canDelete"
                                        type="button"
                                        class="rounded-lg p-1 transition-colors hover:text-error"
                                        title="Delete"
                                        @click="deleteProduct(product)"
                                    >
                                        <IconTrash
                                            class="size-5"
                                            stroke="1.5"
                                        />
                                    </button>
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
                        No products found
                    </p>
                    <p class="mt-1 text-sm">
                        Try adjusting your filters or add a new product.
                    </p>
                    <Link
                        v-if="canCreate"
                        :href="route('admin.products.create')"
                        class="btn-primary btn-sm mt-4 inline-flex"
                    >
                        Add New Product
                    </Link>
                </div>
            </div>

            <div
                class="flex flex-col gap-3 border-t border-outline-variant/30 bg-surface-container-low px-lg py-md sm:flex-row sm:items-center sm:justify-between"
            >
                <p class="text-[12px] text-body-sm text-on-surface-variant">
                    {{ showingLabel }}
                </p>
                <AdminPagination
                    v-if="pagination.last_page > 1"
                    :pagination="pagination"
                />
            </div>
        </section>

        <!-- Filter bottom sheet (mobile) -->
        <AdminBottomSheet
            :open="filterSheetOpen"
            @close="filterSheetOpen = false"
        >
            <div class="mb-xl flex items-center justify-between">
                <h3 class="font-serif text-headline-sm text-primary">
                    Filter &amp; Sort
                </h3>
                <button
                    type="button"
                    class="text-body-sm font-bold text-secondary"
                    @click="resetSheetFilters"
                >
                    Reset
                </button>
            </div>

            <div class="space-y-xl">
                <div>
                    <p class="mb-md text-label-caps uppercase text-on-surface-variant">
                        Search
                    </p>
                    <div class="relative">
                        <IconSearch
                            class="pointer-events-none absolute left-3 top-1/2 size-5 -translate-y-1/2 text-outline"
                            stroke="1.5"
                        />
                        <input
                            v-model="sheetSearch"
                            type="search"
                            placeholder="Search products..."
                            class="input-field w-full pl-10"
                        />
                    </div>
                </div>

                <div>
                    <p class="mb-md text-label-caps uppercase text-on-surface-variant">
                        Category
                    </p>
                    <select v-model="sheetCategoryId" class="input-field w-full">
                        <option value="">All Categories</option>
                        <option
                            v-for="option in categoryOptions"
                            :key="option.id"
                            :value="String(option.id)"
                        >
                            {{ option.name }}
                        </option>
                    </select>
                </div>

                <div>
                    <p class="mb-md text-label-caps uppercase text-on-surface-variant">
                        Status
                    </p>
                    <div class="flex flex-wrap gap-sm">
                        <button
                            v-for="opt in [
                                { value: '', label: 'All' },
                                { value: 'active', label: 'Active' },
                                { value: 'draft', label: 'Draft' },
                                { value: 'archived', label: 'Archived' },
                            ]"
                            :key="`status-${opt.value}`"
                            type="button"
                            class="rounded-full px-md py-sm text-body-sm font-medium transition-colors"
                            :class="
                                sheetStatus === opt.value
                                    ? 'bg-secondary font-bold text-on-secondary'
                                    : 'border border-outline-variant text-on-surface-variant'
                            "
                            @click="sheetStatus = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <div>
                    <p class="mb-md text-label-caps uppercase text-on-surface-variant">
                        Stock
                    </p>
                    <div class="flex flex-wrap gap-sm">
                        <button
                            v-for="opt in [
                                { value: '', label: 'Any' },
                                { value: 'in_stock', label: 'In Stock' },
                                { value: 'out_of_stock', label: 'Out of Stock' },
                                { value: 'on_backorder', label: 'Backorder' },
                            ]"
                            :key="`stock-${opt.value}`"
                            type="button"
                            class="rounded-full px-md py-sm text-body-sm font-medium transition-colors"
                            :class="
                                sheetStockStatus === opt.value
                                    ? 'bg-secondary font-bold text-on-secondary'
                                    : 'border border-outline-variant text-on-surface-variant'
                            "
                            @click="sheetStockStatus = opt.value"
                        >
                            {{ opt.label }}
                        </button>
                    </div>
                </div>

                <button
                    type="button"
                    class="mt-md flex h-14 w-full items-center justify-center rounded-full bg-primary-container font-bold text-on-primary-fixed shadow-lg"
                    @click="applySheetFilters"
                >
                    Apply Filters
                </button>
            </div>
        </AdminBottomSheet>

        <!-- Actions bottom sheet (mobile) -->
        <AdminBottomSheet
            :open="actionSheetOpen"
            @close="closeActionSheet"
        >
            <div v-if="actionProduct" class="mb-lg px-sm">
                <p class="font-serif text-headline-sm text-primary">
                    {{ actionProduct.name }}
                </p>
                <p class="text-body-sm text-on-surface-variant">
                    Product SKU: {{ actionProduct.sku }}
                </p>
            </div>

            <div v-if="actionProduct" class="space-y-1">
                <Link
                    v-if="canEdit"
                    :href="route('admin.products.edit', actionProduct.id)"
                    class="flex w-full items-center gap-md rounded-xl p-md text-left transition-colors hover:bg-surface-container active:bg-surface-container"
                    @click="closeActionSheet"
                >
                    <IconEdit class="size-5 text-secondary" stroke="1.5" />
                    <span class="font-sans text-title-lg text-primary">
                        Edit Product Details
                    </span>
                </Link>

                <button
                    v-if="canEdit"
                    type="button"
                    class="flex w-full items-center gap-md rounded-xl p-md text-left transition-colors hover:bg-surface-container active:bg-surface-container"
                    @click="toggleFeatured(actionProduct)"
                >
                    <IconStarFilled
                        v-if="actionProduct.is_featured"
                        class="size-5 text-secondary"
                    />
                    <IconStar
                        v-else
                        class="size-5 text-secondary"
                        stroke="1.5"
                    />
                    <span class="font-sans text-title-lg text-primary">
                        {{ actionProduct.is_featured ? 'Remove Featured' : 'Mark as Featured' }}
                    </span>
                </button>

                <div
                    v-if="canDelete"
                    class="my-sm h-px bg-outline-variant/30"
                />

                <button
                    v-if="canDelete"
                    type="button"
                    class="flex w-full items-center gap-md rounded-xl p-md text-left text-error transition-colors hover:bg-error-container/10 active:bg-error-container/20"
                    @click="deleteProduct(actionProduct)"
                >
                    <IconTrash class="size-5" stroke="1.5" />
                    <span class="font-sans text-title-lg">Delete Product</span>
                </button>
            </div>
        </AdminBottomSheet>
    </AdminLayout>
</template>
