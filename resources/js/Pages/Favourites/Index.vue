<script setup>
import { computed, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { useDebounceFn } from '@vueuse/core';
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from '@headlessui/vue';
import {
    IconHeartBroken,
    IconLayoutGrid,
    IconList,
    IconPlus,
    IconSearch,
} from '@tabler/icons-vue';
import CollectionFormModal from '@/Components/Public/CollectionFormModal.vue';
import CollectionMosaicCard from '@/Components/Public/CollectionMosaicCard.vue';
import CollectionProductsDrawer from '@/Components/Public/CollectionProductsDrawer.vue';
import AddToCollectionModal from '@/Components/Public/AddToCollectionModal.vue';
import FavouriteListRow from '@/Components/Public/FavouriteListRow.vue';
import FavouriteProductCard from '@/Components/Public/FavouriteProductCard.vue';
import ShopPagination from '@/Components/Public/ShopPagination.vue';
import AppButton from '@/Components/Shared/AppButton.vue';
import { useCart } from '@/Composables/useCart';
import { useFavourite } from '@/Composables/useFavourite';
import ProfileLayout from '@/Layouts/ProfileLayout.vue';

const props = defineProps({
    favourites: {
        type: Object,
        default: () => ({ data: [], meta: {} }),
    },
    collections: {
        type: Array,
        default: () => [],
    },
    openCollection: {
        type: Object,
        default: null,
    },
    filters: {
        type: Object,
        default: () => ({
            search: null,
            category_id: null,
            sort: 'newest',
            per_page: 12,
        }),
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
    user: {
        type: Object,
        default: null,
    },
});

const { add } = useCart();
const { toggle, clear } = useFavourite();

const search = ref(props.filters.search ?? '');
const categoryId = ref(props.filters.category_id ?? '');
const sort = ref(props.filters.sort ?? 'newest');
const viewMode = ref('grid');
const collectionModalOpen = ref(false);
const clearConfirmOpen = ref(false);
const clearing = ref(false);
const removeTarget = ref(null);
const removing = ref(false);
const addToCollectionProduct = ref(null);

watch(
    () => props.filters,
    (next) => {
        search.value = next.search ?? '';
        categoryId.value = next.category_id ?? '';
        sort.value = next.sort ?? 'newest';
    },
    { deep: true },
);

const favouriteList = computed(() => props.favourites.data ?? []);
const meta = computed(() => props.favourites.meta ?? {});
const totalCount = computed(() => Number(meta.value.total ?? favouriteList.value.length));

const hasActiveFilters = computed(
    () => !!(props.filters.search || props.filters.category_id),
);

const isEmpty = computed(() => favouriteList.value.length === 0);

const sortOptions = [
    { value: 'newest', label: 'Recently Added' },
    { value: 'price_asc', label: 'Price: Low to High' },
    { value: 'price_desc', label: 'Price: High to Low' },
    { value: 'name', label: 'Name' },
];

const buildQuery = (overrides = {}) => {
    const next = {
        search: search.value || undefined,
        category_id: categoryId.value || undefined,
        sort: sort.value && sort.value !== 'newest' ? sort.value : undefined,
        collection: props.openCollection?.id || undefined,
        ...overrides,
    };

    return Object.fromEntries(
        Object.entries(next).filter(
            ([, value]) => value !== undefined && value !== null && value !== '',
        ),
    );
};

const applyFilters = (overrides = {}) => {
    router.get(route('favourites.index'), buildQuery(overrides), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const debouncedSearch = useDebounceFn(() => {
    applyFilters();
}, 350);

const onSearchInput = (event) => {
    search.value = event.target.value;
    debouncedSearch();
};

const onCategoryChange = (event) => {
    categoryId.value = event.target.value;
    applyFilters();
};

const onSortChange = (event) => {
    sort.value = event.target.value;
    applyFilters();
};

const paginationQuery = computed(() => buildQuery());

const handleAddToCart = (product) => {
    if (!product?.slug) {
        return;
    }
    add(product.slug, { quantity: 1 });
};

const handleToggleFavourite = (product) => {
    if (!product?.slug) {
        return;
    }
    toggle(product.slug);
};

const handleNotifyMe = () => {
    // Notify-me wiring comes later
};

const askRemove = (product) => {
    removeTarget.value = product;
};

const openAddToCollection = (product) => {
    addToCollectionProduct.value = product;
};

const closeAddToCollection = () => {
    addToCollectionProduct.value = null;
};

const openCreateFromAddToCollection = () => {
    addToCollectionProduct.value = null;
    collectionModalOpen.value = true;
};

const closeRemove = () => {
    if (removing.value) {
        return;
    }
    removeTarget.value = null;
};

const confirmRemove = () => {
    if (!removeTarget.value?.slug) {
        return;
    }

    removing.value = true;
    toggle(removeTarget.value.slug, {
        onFinish: () => {
            removing.value = false;
            removeTarget.value = null;
        },
    });
};

const openClearConfirm = () => {
    if (totalCount.value === 0) {
        return;
    }
    clearConfirmOpen.value = true;
};

const closeClearConfirm = () => {
    if (clearing.value) {
        return;
    }
    clearConfirmOpen.value = false;
};

const confirmClear = () => {
    clearing.value = true;
    clear({
        onFinish: () => {
            clearing.value = false;
            clearConfirmOpen.value = false;
        },
    });
};

const openCollectionDrawer = (collection) => {
    if (!collection?.id) {
        return;
    }

    router.get(
        route('favourites.index'),
        buildQuery({ collection: collection.id }),
        {
            preserveState: true,
            preserveScroll: true,
            only: ['openCollection'],
        },
    );
};

const closeCollectionDrawer = () => {
    router.get(
        route('favourites.index'),
        buildQuery({ collection: null }),
        {
            preserveState: true,
            preserveScroll: true,
            only: ['openCollection'],
        },
    );
};

const detachFromCollection = (product) => {
    if (!props.openCollection?.id || !product?.slug) {
        return;
    }

    router.delete(
        route('collections.products.detach', {
            collection: props.openCollection.id,
            product: product.slug,
        }),
        {
            preserveScroll: true,
        },
    );
};
</script>

<template>
    <ProfileLayout>
        <Head title="My Favourites" />

        <section class="flex flex-wrap items-baseline gap-md">
            <h1 class="font-serif text-headline-lg text-primary">My Favourites</h1>
            <span
                class="rounded-lg bg-secondary-fixed px-sm py-xs font-sans text-label-caps text-on-secondary-fixed-variant"
            >
                {{ totalCount }} {{ totalCount === 1 ? 'item' : 'items' }}
            </span>
        </section>

        <div
            class="flex flex-col items-stretch justify-between gap-md md:flex-row md:items-center"
        >
            <div class="flex w-full flex-1 flex-col gap-md sm:flex-row">
                <div class="relative flex-1">
                    <IconSearch
                        class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-outline"
                        :size="20"
                        stroke-width="1.5"
                    />
                    <input
                        type="search"
                        class="h-12 w-full rounded-xl border border-outline-variant bg-white py-2 pl-10 pr-4 font-sans text-body-sm text-on-surface outline-none transition-all placeholder:text-outline focus:border-secondary focus:ring-1 focus:ring-secondary/20"
                        placeholder="Search saved items..."
                        :value="search"
                        @input="onSearchInput"
                    />
                </div>

                <select
                    class="h-12 rounded-xl border border-outline-variant bg-white px-md font-sans text-label-caps text-on-surface outline-none focus:border-secondary"
                    :value="categoryId"
                    @change="onCategoryChange"
                >
                    <option value="">All Categories</option>
                    <option
                        v-for="category in categoryOptions"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                    </option>
                </select>
            </div>

            <div class="flex w-full items-center gap-md md:w-auto">
                <select
                    class="h-12 flex-1 rounded-xl border border-outline-variant bg-white px-md font-sans text-label-caps text-on-surface outline-none focus:border-secondary md:flex-none"
                    :value="sort"
                    @change="onSortChange"
                >
                    <option
                        v-for="option in sortOptions"
                        :key="option.value"
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>

                <div
                    class="flex rounded-xl border border-outline-variant bg-white p-1"
                    role="group"
                    aria-label="View mode"
                >
                    <button
                        type="button"
                        class="rounded-lg p-1.5 transition-colors"
                        :class="
                            viewMode === 'grid'
                                ? 'bg-surface-container text-primary'
                                : 'text-on-surface-variant hover:bg-surface-container'
                        "
                        aria-label="Grid view"
                        :aria-pressed="viewMode === 'grid'"
                        @click="viewMode = 'grid'"
                    >
                        <IconLayoutGrid :size="20" stroke-width="1.5" />
                    </button>
                    <button
                        type="button"
                        class="rounded-lg p-1.5 transition-colors"
                        :class="
                            viewMode === 'list'
                                ? 'bg-surface-container text-primary'
                                : 'text-on-surface-variant hover:bg-surface-container'
                        "
                        aria-label="List view"
                        :aria-pressed="viewMode === 'list'"
                        @click="viewMode = 'list'"
                    >
                        <IconList :size="20" stroke-width="1.5" />
                    </button>
                </div>

                <AppButton
                    type="button"
                    variant="secondary"
                    class="shrink-0"
                    :disabled="totalCount === 0"
                    @click="openClearConfirm"
                >
                    Clear All
                </AppButton>
            </div>
        </div>

        <template v-if="!isEmpty">
            <div
                v-if="viewMode === 'grid'"
                class="mb-xxl grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4"
            >
                <FavouriteProductCard
                    v-for="favourite in favouriteList"
                    :key="favourite.id"
                    :favourite="favourite"
                    @add-to-cart="handleAddToCart"
                    @remove="askRemove"
                    @toggle-favourite="askRemove"
                    @add-to-collection="openAddToCollection"
                    @notify-me="handleNotifyMe"
                />
            </div>

            <div v-else class="mb-xxl">
                <h2 class="mb-lg font-serif text-headline-sm text-primary">
                    Recent Favourites List
                </h2>
                <div class="overflow-hidden rounded-xl bg-white shadow-card">
                    <FavouriteListRow
                        v-for="favourite in favouriteList"
                        :key="favourite.id"
                        :favourite="favourite"
                        @add-to-cart="handleAddToCart"
                        @remove="askRemove"
                        @notify-me="handleNotifyMe"
                    />
                </div>
            </div>

            <ShopPagination
                :meta="meta"
                :query="paginationQuery"
                route-name="favourites.index"
            />
        </template>

        <div
            v-else
            class="mx-auto max-w-lg rounded-2xl bg-white p-xxl text-center shadow-card"
        >
            <div
                class="mx-auto mb-lg flex h-20 w-20 items-center justify-center rounded-full bg-surface-container text-outline"
            >
                <IconHeartBroken :size="40" stroke-width="1.5" />
            </div>
            <h2 class="mb-md font-serif text-headline-sm text-primary">
                {{ hasActiveFilters ? 'No matching favourites' : 'No favourites yet' }}
            </h2>
            <p class="mb-xl font-sans text-body-sm text-on-surface-variant">
                <template v-if="hasActiveFilters">
                    Try a different search or category filter.
                </template>
                <template v-else>
                    Start exploring our artisanal collection and save your top bites for later.
                </template>
            </p>
            <Link
                v-if="!hasActiveFilters"
                :href="route('products.index')"
                class="inline-flex h-12 items-center justify-center rounded-full bg-secondary px-xl font-sans text-body-sm font-bold text-white transition-opacity hover:opacity-90"
            >
                Browse Catalogue
            </Link>
        </div>

        <section class="mb-xxl">
            <div class="mb-lg flex items-center justify-between">
                <h2 class="font-serif text-headline-sm text-primary">My Collections</h2>
                <button
                    type="button"
                    class="font-sans text-label-caps text-secondary transition-colors hover:underline"
                    @click="collectionModalOpen = true"
                >
                    Create Collection
                </button>
            </div>

            <div class="grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4">
                <CollectionMosaicCard
                    v-for="collection in collections"
                    :key="collection.id"
                    :collection="collection"
                    @select="openCollectionDrawer"
                />

                <button
                    type="button"
                    class="flex aspect-square cursor-pointer flex-col items-center justify-center gap-md rounded-xl border-2 border-dashed border-outline-variant bg-white/50 text-on-surface-variant transition-all hover:border-secondary hover:text-secondary"
                    @click="collectionModalOpen = true"
                >
                    <IconPlus :size="48" stroke-width="1.2" />
                    <span class="font-sans text-label-caps">New Collection</span>
                </button>
            </div>
        </section>

        <CollectionProductsDrawer
            :show="!!openCollection"
            :collection="openCollection"
            @close="closeCollectionDrawer"
            @add-to-cart="handleAddToCart"
            @remove="detachFromCollection"
        />

        <CollectionFormModal
            :show="collectionModalOpen"
            @close="collectionModalOpen = false"
        />

        <AddToCollectionModal
            :show="!!addToCollectionProduct"
            :product="addToCollectionProduct"
            :collections="collections"
            @close="closeAddToCollection"
            @create-collection="openCreateFromAddToCollection"
        />

        <TransitionRoot appear :show="clearConfirmOpen" as="template">
            <Dialog as="div" class="relative z-50" @close="closeClearConfirm">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-150"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-tertiary/40 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto p-md">
                    <div class="flex min-h-full items-center justify-center">
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-200"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="ease-in duration-150"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full max-w-sm rounded-2xl bg-white p-xl shadow-xl"
                            >
                                <DialogTitle class="font-serif text-headline-sm text-primary">
                                    Clear all favourites?
                                </DialogTitle>
                                <p class="mt-md font-sans text-body-sm text-on-surface-variant">
                                    This will remove every saved item from your favourites list.
                                    This cannot be undone.
                                </p>
                                <div class="mt-xl flex gap-md">
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        class="flex-1"
                                        :disabled="clearing"
                                        @click="closeClearConfirm"
                                    >
                                        Keep
                                    </AppButton>
                                    <AppButton
                                        type="button"
                                        variant="danger"
                                        class="flex-1"
                                        :disabled="clearing"
                                        @click="confirmClear"
                                    >
                                        Clear All
                                    </AppButton>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>

        <TransitionRoot appear :show="!!removeTarget" as="template">
            <Dialog as="div" class="relative z-50" @close="closeRemove">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-200"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-150"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-tertiary/40 backdrop-blur-sm" />
                </TransitionChild>

                <div class="fixed inset-0 overflow-y-auto p-md">
                    <div class="flex min-h-full items-center justify-center">
                        <TransitionChild
                            as="template"
                            enter="ease-out duration-200"
                            enter-from="opacity-0 scale-95"
                            enter-to="opacity-100 scale-100"
                            leave="ease-in duration-150"
                            leave-from="opacity-100 scale-100"
                            leave-to="opacity-0 scale-95"
                        >
                            <DialogPanel
                                class="w-full max-w-sm rounded-2xl bg-white p-xl shadow-xl"
                            >
                                <DialogTitle class="font-serif text-headline-sm text-primary">
                                    Remove from Favourites?
                                </DialogTitle>
                                <p class="mt-md font-sans text-body-sm text-on-surface-variant">
                                    <template v-if="removeTarget?.name">
                                        Remove
                                        <span class="font-semibold text-on-surface">
                                            {{ removeTarget.name }}
                                        </span>
                                        from your favourites?
                                    </template>
                                    <template v-else>
                                        Remove this item from your favourites?
                                    </template>
                                </p>
                                <div class="mt-xl flex gap-md">
                                    <AppButton
                                        type="button"
                                        variant="secondary"
                                        class="flex-1"
                                        :disabled="removing"
                                        @click="closeRemove"
                                    >
                                        Keep
                                    </AppButton>
                                    <AppButton
                                        type="button"
                                        variant="danger"
                                        class="flex-1"
                                        :disabled="removing"
                                        @click="confirmRemove"
                                    >
                                        Remove
                                    </AppButton>
                                </div>
                            </DialogPanel>
                        </TransitionChild>
                    </div>
                </div>
            </Dialog>
        </TransitionRoot>
    </ProfileLayout>
</template>
