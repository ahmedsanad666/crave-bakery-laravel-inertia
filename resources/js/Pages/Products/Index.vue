<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import { IconAdjustmentsHorizontal, IconChevronRight, IconShoppingBag, IconX } from '@tabler/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CatalogueProductCard from '@/Components/Public/CatalogueProductCard.vue';
import ProductFiltersSidebar from '@/Components/Public/ProductFiltersSidebar.vue';
import ShopPagination from '@/Components/Public/ShopPagination.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { useCart } from '@/Composables/useCart';
import { useFavourite } from '@/Composables/useFavourite';
import { useSiteSeo } from '@/Composables/useSiteSeo';

const props = defineProps({
    products: {
        type: Object,
        required: true,
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
    priceBounds: {
        type: Object,
        default: () => ({ min: 0, max: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const page = usePage();
const { add } = useCart();
const { toggle } = useFavourite();
const {
    headTitle: seoHeadTitle,
    title: seoTitle,
    description: seoDescription,
    keywords: seoKeywords,
} = useSiteSeo({ pageTitle: 'Catalogue' });

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? null);
const priceMax = ref(props.filters.price_max ?? null);
const minRating = ref(props.filters.min_rating ?? null);
const inStock = ref(props.filters.in_stock === true);
const outOfStock = ref(props.filters.out_of_stock === true);
const sort = ref(props.filters.sort ?? 'recommended');
const filtersOpen = ref(false);

watch(
    () => props.filters,
    (next) => {
        search.value = next.search ?? '';
        categoryId.value = next.category_id ?? null;
        priceMax.value = next.price_max ?? null;
        minRating.value = next.min_rating ?? null;
        inStock.value = next.in_stock === true;
        outOfStock.value = next.out_of_stock === true;
        sort.value = next.sort ?? 'recommended';
    },
    { deep: true },
);

const productList = computed(() => props.products.data ?? []);
const meta = computed(() => props.products.meta ?? {});

const showingCount = computed(() => productList.value.length);
const totalCount = computed(() => meta.value.total ?? 0);

const sortOptions = [
    { value: 'recommended', label: 'Recommended' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'newest', label: 'Newest Arrivals' },
];

const buildQuery = (overrides = {}) => {
    const next = {
        search: search.value,
        category_id: categoryId.value,
        price_max: priceMax.value,
        min_rating: minRating.value,
        in_stock: inStock.value,
        out_of_stock: outOfStock.value,
        sort: sort.value,
        ...overrides,
    };

    const query = {};

    if (next.search) {
        query.search = next.search;
    }

    if (next.category_id != null && next.category_id !== '') {
        query.category_id = next.category_id;
    }

    if (next.price_max != null && next.price_max !== '') {
        const maxBound = Math.ceil(Number(props.priceBounds?.max ?? 0));
        if (Number(next.price_max) < maxBound) {
            query.price_max = next.price_max;
        }
    }

    if (next.min_rating) {
        query.min_rating = next.min_rating;
    }

    if (next.in_stock === true) {
        query.in_stock = 1;
    }

    if (next.out_of_stock === true) {
        query.out_of_stock = 1;
    }

    if (next.sort && next.sort !== 'recommended') {
        query.sort = next.sort;
    }

    return query;
};

const applyFilters = (overrides = {}) => {
    router.get(route('products.index'), buildQuery(overrides), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 350);

const debouncedPrice = useDebounceFn(() => {
    applyFilters();
}, 200);

const onSearchUpdate = (value) => {
    search.value = value;
    debouncedSearch();
};

const onCategoryUpdate = (value) => {
    categoryId.value = value;
    applyFilters({ category_id: value });
};

const onPriceMaxUpdate = (value) => {
    priceMax.value = value;
    debouncedPrice();
};

const onMinRatingUpdate = (value) => {
    minRating.value = value;
    applyFilters({ min_rating: value });
};

const onInStockUpdate = (value) => {
    inStock.value = value;
    applyFilters({ in_stock: value });
};

const onOutOfStockUpdate = (value) => {
    outOfStock.value = value;
    applyFilters({ out_of_stock: value });
};

const onSortChange = (value) => {
    sort.value = value;
    applyFilters({ sort: value });
};

const clearFilters = () => {
    search.value = '';
    categoryId.value = null;
    priceMax.value = null;
    minRating.value = null;
    inStock.value = false;
    outOfStock.value = false;
    sort.value = 'recommended';
    filtersOpen.value = false;
    router.get(
        route('products.index'),
        {},
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const paginationQuery = computed(() => buildQuery());

const handleAddToCart = (product) => {
    if (!product?.slug) {
        return;
    }

    add(product.slug, { quantity: 1 });
};

const handleToggleFavourite = (product) => {
    if (!page.props.auth?.user) {
        router.visit(route('login'));
        return;
    }

    if (!product?.slug) {
        return;
    }

    toggle(product.slug);
};

const handleNotifyMe = () => {
    // Notify-me wiring comes later
};
</script>

<template>
    <AppLayout>
        <Head>
            <title>{{ seoHeadTitle }}</title>
            <meta
                v-if="seoDescription"
                head-key="description"
                name="description"
                :content="seoDescription"
            />
            <meta
                v-if="seoKeywords"
                head-key="keywords"
                name="keywords"
                :content="seoKeywords"
            />
            <meta head-key="og:type" property="og:type" content="website" />
            <meta
                head-key="og:title"
                property="og:title"
                :content="seoTitle"
            />
            <meta
                v-if="seoDescription"
                head-key="og:description"
                property="og:description"
                :content="seoDescription"
            />
        </Head>

        <section class="bg-surface pb-xxl pt-xl">
            <div class="container-page">
                <nav
                    class="mb-md flex items-center gap-xs font-sans text-body-sm text-on-surface-variant"
                    aria-label="Breadcrumb"
                >
                    <Link
                        :href="route('home')"
                        class="hover:text-primary"
                    >
                        Home
                    </Link>
                    <IconChevronRight :size="14" stroke-width="1.5" />
                    <span class="font-semibold text-primary">Catalogue</span>
                </nav>
                <h1 class="mb-xs font-serif text-headline-lg text-primary">
                    Our Catalogue
                </h1>
                <p class="font-sans text-body-lg italic text-on-surface-variant">
                    Freshly baked every morning, made with love
                </p>
            </div>
        </section>

        <main class="container-page relative flex gap-xl py-xxl">
            <aside class="hidden w-64 shrink-0 lg:block">
                <div class="sticky top-24">
                    <ProductFiltersSidebar
                        :category-options="categoryOptions"
                        :price-bounds="priceBounds"
                        :search="search"
                        :category-id="categoryId"
                        :price-max="priceMax"
                        :min-rating="minRating"
                        :in-stock="inStock"
                        :out-of-stock="outOfStock"
                        @update:search="onSearchUpdate"
                        @update:category-id="onCategoryUpdate"
                        @update:price-max="onPriceMaxUpdate"
                        @update:min-rating="onMinRatingUpdate"
                        @update:in-stock="onInStockUpdate"
                        @update:out-of-stock="onOutOfStockUpdate"
                        @clear="clearFilters"
                    />
                </div>
            </aside>

            <div class="min-w-0 flex-1">
                <div
                    class="mb-lg flex flex-wrap items-center justify-between gap-md border-b border-outline-variant pb-sm"
                >
                    <div class="flex items-center gap-md">
                        <button
                            type="button"
                            class="inline-flex items-center gap-sm rounded-full border border-border-base px-md py-2 font-sans text-body-sm font-semibold text-primary transition-colors hover:bg-surface-container-high lg:hidden"
                            @click="filtersOpen = true"
                        >
                            <IconAdjustmentsHorizontal
                                :size="18"
                                stroke-width="1.5"
                            />
                            Filters
                        </button>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Showing
                            {{ showingCount }}
                            of
                            {{ totalCount }}
                            products
                        </p>
                    </div>

                    <div class="flex min-w-0 items-center gap-sm">
                        <span
                            class="shrink-0 font-sans text-body-sm text-on-surface-variant"
                        >
                            Sort by:
                        </span>
                        <div class="w-full min-w-[11rem] sm:w-56">
                            <AppSelect
                                :model-value="sort"
                                :options="sortOptions"
                                value-key="value"
                                label-key="label"
                                size="sm"
                                @update:model-value="onSortChange"
                            />
                        </div>
                    </div>
                </div>

                <div
                    v-if="productList.length"
                    class="grid grid-cols-1 gap-lg md:grid-cols-2 xl:grid-cols-3"
                >
                    <CatalogueProductCard
                        v-for="product in productList"
                        :key="product.id"
                        :product="product"
                        @add-to-cart="handleAddToCart"
                        @toggle-favourite="handleToggleFavourite"
                        @notify-me="handleNotifyMe"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center pt-xxl text-center"
                >
                    <div
                        class="mb-md flex h-24 w-24 items-center justify-center rounded-full border-4 border-dashed border-outline-variant"
                    >
                        <IconShoppingBag
                            :size="48"
                            stroke-width="1.5"
                            class="text-outline-variant"
                        />
                    </div>
                    <h3 class="mb-xs font-serif text-headline-sm text-primary">
                        No products found
                    </h3>
                    <p
                        class="mb-lg font-sans text-body-sm text-on-surface-variant"
                    >
                        Try adjusting your filters or search keywords.
                    </p>
                    <button
                        type="button"
                        class="rounded-full bg-accent px-xl py-3 font-sans text-body-sm font-bold text-white hover:opacity-90"
                        @click="clearFilters"
                    >
                        Clear Filters
                    </button>
                </div>

                <ShopPagination
                    v-if="productList.length"
                    :meta="meta"
                    :query="paginationQuery"
                />
            </div>
        </main>

        <TransitionRoot :show="filtersOpen" as="template">
            <Dialog class="relative z-50 lg:hidden" @close="filtersOpen = false">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-150"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-[#1A1A1A]/40" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-hidden">
                    <div class="absolute inset-0 overflow-hidden">
                        <div
                            class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10"
                        >
                            <TransitionChild
                                as="template"
                                enter="transform transition ease-out duration-300"
                                enter-from="translate-x-full"
                                enter-to="translate-x-0"
                                leave="transform transition ease-in duration-200"
                                leave-from="translate-x-0"
                                leave-to="translate-x-full"
                            >
                                <DialogPanel
                                    class="pointer-events-auto w-screen max-w-sm overflow-y-auto bg-white p-lg shadow-modal"
                                >
                                    <div
                                        class="mb-lg flex items-center justify-between"
                                    >
                                        <DialogTitle
                                            class="font-serif text-headline-sm text-primary"
                                        >
                                            Filters
                                        </DialogTitle>
                                        <button
                                            type="button"
                                            class="rounded-full p-2 text-primary hover:bg-surface-container"
                                            aria-label="Close filters"
                                            @click="filtersOpen = false"
                                        >
                                            <IconX
                                                :size="20"
                                                stroke-width="1.5"
                                            />
                                        </button>
                                    </div>

                                    <ProductFiltersSidebar
                                        :category-options="categoryOptions"
                                        :price-bounds="priceBounds"
                                        :search="search"
                                        :category-id="categoryId"
                                        :price-max="priceMax"
                                        :min-rating="minRating"
                                        :in-stock="inStock"
                                        :out-of-stock="outOfStock"
                                        :show-header="false"
                                        @update:search="onSearchUpdate"
                                        @update:category-id="onCategoryUpdate"
                                        @update:price-max="onPriceMaxUpdate"
                                        @update:min-rating="onMinRatingUpdate"
                                        @update:in-stock="onInStockUpdate"
                                        @update:out-of-stock="onOutOfStockUpdate"
                                        @clear="clearFilters"
                                    />
                                </DialogPanel>
                            </TransitionChild>
                        </div>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </AppLayout>
</template>
