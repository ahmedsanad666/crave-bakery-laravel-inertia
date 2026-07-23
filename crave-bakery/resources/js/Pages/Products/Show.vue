<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    IconChevronRight,
    IconHeart,
    IconHeartFilled,
    IconMinus,
    IconPlus,
    IconShieldCheck,
    IconShoppingCart,
    IconStarFilled,
    IconStarHalfFilled,
    IconTruck,
    IconClock,
} from '@tabler/icons-vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ProductGallery from '@/Components/Public/ProductGallery.vue';
import ShopReviewCard from '@/Components/Public/ShopReviewCard.vue';
import RelatedProductCard from '@/Components/Public/RelatedProductCard.vue';
import AppSelect from '@/Components/Shared/AppSelect.vue';
import { useCart } from '@/Composables/useCart';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    relatedProducts: {
        type: Array,
        default: () => [],
    },
    reviews: {
        type: Object,
        default: () => ({
            average_rating: 0,
            reviews_count: 0,
            rating_breakdown: {},
            items: [],
        }),
    },
});

const { add } = useCart();

const activeTab = ref('description');
const quantity = ref(1);
const selectedAttributes = reactive({});
const isFavourited = ref(Boolean(props.product.is_favourited));

watch(
    () => props.product,
    (product) => {
        isFavourited.value = Boolean(product.is_favourited);
        quantity.value = 1;

        Object.keys(selectedAttributes).forEach((key) => {
            delete selectedAttributes[key];
        });

        (product.attributes ?? []).forEach((attribute) => {
            const firstValue = attribute.values?.[0];
            if (firstValue) {
                selectedAttributes[attribute.id] = firstValue.id;
            }
        });
    },
    { immediate: true, deep: true },
);

const pageTitle = computed(
    () => props.product.meta_title || props.product.name || 'Product',
);

const metaDescription = computed(
    () =>
        props.product.meta_description
        || props.product.short_description
        || props.product.description
        || '',
);

const ogImage = computed(
    () => props.product.og_image || props.product.thumbnail || '',
);

const canonicalUrl = computed(() => {
    if (props.product.canonical_url) {
        return props.product.canonical_url;
    }

    if (typeof window === 'undefined' || !props.product.slug) {
        return '';
    }

    return route('products.show', props.product.slug);
});

const metaKeywords = computed(() => {
    const keywords = props.product.meta_keywords;

    if (Array.isArray(keywords)) {
        return keywords.filter(Boolean).join(', ');
    }

    return typeof keywords === 'string' ? keywords : '';
});

const primaryCategory = computed(
    () => props.product.categories?.[0] ?? null,
);

const isOutOfStock = computed(
    () => props.product.stock_status === 'out_of_stock',
);

const maxQuantity = computed(() => {
    if (props.product.allow_backorders) {
        return 99;
    }

    return Math.max(1, Number(props.product.stock_quantity) || 1);
});

const displayPrice = computed(() =>
    formatMoney(props.product.current_price ?? props.product.sale_price ?? props.product.regular_price),
);

const originalPrice = computed(() => {
    if (!props.product.is_on_sale) {
        return null;
    }

    return formatMoney(props.product.regular_price);
});

const averageRating = computed(() =>
    Number(props.reviews.average_rating ?? props.product.average_rating ?? 0),
);

const reviewsCount = computed(() =>
    Number(props.reviews.reviews_count ?? props.product.reviews_count ?? 0),
);

const fullStars = computed(() => Math.floor(averageRating.value));
const hasHalfStar = computed(
    () => averageRating.value - fullStars.value >= 0.5,
);

const ratingBreakdown = computed(() => {
    const breakdown = props.reviews.rating_breakdown ?? {};
    const total = reviewsCount.value || 1;

    return [5, 4, 3, 2, 1].map((star) => {
        const count = Number(breakdown[star] ?? 0);

        return {
            star,
            count,
            percent: Math.round((count / total) * 100),
        };
    });
});

const dropdownOptions = (attribute) =>
    (attribute.values ?? []).map((value) => ({
        id: value.id,
        name: value.value,
    }));

function formatMoney(price) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(price) || 0);
}

const decreaseQty = () => {
    quantity.value = Math.max(1, quantity.value - 1);
};

const increaseQty = () => {
    quantity.value = Math.min(maxQuantity.value, quantity.value + 1);
};

const selectAttributeValue = (attributeId, valueId) => {
    selectedAttributes[attributeId] = valueId;
};

const handleAddToCart = () => {
    if (isOutOfStock.value) {
        return;
    }

    add(props.product.slug, {
        quantity: quantity.value,
        attributes: { ...selectedAttributes },
    });
};

const handleToggleFavourite = () => {
    isFavourited.value = !isFavourited.value;
    // Favourites wiring comes in Phase 5
};

const handleWriteReview = () => {
    // Review form comes later
};
</script>

<template>
    <AppLayout>
        <Head>
            <title>{{ pageTitle }}</title>
            <meta
                v-if="metaDescription"
                head-key="description"
                name="description"
                :content="metaDescription"
            />
            <meta
                v-if="metaKeywords"
                head-key="keywords"
                name="keywords"
                :content="metaKeywords"
            />
            <link
                v-if="canonicalUrl"
                head-key="canonical"
                rel="canonical"
                :href="canonicalUrl"
            />
            <meta head-key="og:type" property="og:type" content="product" />
            <meta
                head-key="og:title"
                property="og:title"
                :content="pageTitle"
            />
            <meta
                v-if="metaDescription"
                head-key="og:description"
                property="og:description"
                :content="metaDescription"
            />
            <meta
                v-if="ogImage"
                head-key="og:image"
                property="og:image"
                :content="ogImage"
            />
            <meta
                v-if="canonicalUrl"
                head-key="og:url"
                property="og:url"
                :content="canonicalUrl"
            />
            <meta
                head-key="twitter:card"
                name="twitter:card"
                content="summary_large_image"
            />
            <meta
                head-key="twitter:title"
                name="twitter:title"
                :content="pageTitle"
            />
            <meta
                v-if="metaDescription"
                head-key="twitter:description"
                name="twitter:description"
                :content="metaDescription"
            />
            <meta
                v-if="ogImage"
                head-key="twitter:image"
                name="twitter:image"
                :content="ogImage"
            />
        </Head>

        <!-- Breadcrumb -->
        <div class="bg-surface-container-low px-container-margin py-md">
            <nav
                class="container-page flex flex-wrap items-center gap-xs font-sans text-label-caps uppercase text-on-surface-variant"
                aria-label="Breadcrumb"
            >
                <Link :href="route('home')" class="hover:text-primary">
                    Home
                </Link>
                <IconChevronRight :size="14" stroke-width="1.5" />
                <Link
                    :href="route('products.index')"
                    class="hover:text-primary"
                >
                    Catalogue
                </Link>
                <template v-if="primaryCategory">
                    <IconChevronRight :size="14" stroke-width="1.5" />
                    <Link
                        :href="
                            route('products.index', {
                                category_id: primaryCategory.id,
                            })
                        "
                        class="hover:text-primary"
                    >
                        {{ primaryCategory.name }}
                    </Link>
                </template>
                <IconChevronRight :size="14" stroke-width="1.5" />
                <span class="text-primary">{{ product.name }}</span>
            </nav>
        </div>

        <!-- Main product -->
        <main
            class="container-page grid grid-cols-1 gap-xxl py-xxl md:grid-cols-12"
        >
            <div class="md:col-span-7">
                <ProductGallery
                    :images="product.gallery ?? []"
                    :alt="product.name"
                />
            </div>

            <div class="flex flex-col gap-xl md:col-span-5">
                <div class="flex flex-col gap-md">
                    <span
                        v-if="primaryCategory"
                        class="inline-block self-start rounded-full bg-primary px-md py-xs font-sans text-label-caps uppercase text-white"
                    >
                        {{ primaryCategory.name }}
                    </span>

                    <h1 class="font-serif text-headline-lg text-primary">
                        {{ product.name }}
                    </h1>

                    <div class="flex items-center gap-md">
                        <div class="flex text-accent">
                            <IconStarFilled
                                v-for="i in fullStars"
                                :key="`full-${i}`"
                                :size="20"
                            />
                            <IconStarHalfFilled
                                v-if="hasHalfStar"
                                :size="20"
                            />
                        </div>
                        <span class="font-sans text-body-sm text-on-surface-variant">
                            ({{ averageRating.toFixed(1) }} /
                            {{ reviewsCount }}
                            {{ reviewsCount === 1 ? 'Review' : 'Reviews' }})
                        </span>
                    </div>

                    <div class="flex items-baseline gap-md">
                        <p class="font-serif text-headline-md text-accent">
                            {{ displayPrice }}
                        </p>
                        <p
                            v-if="originalPrice"
                            class="font-sans text-title-lg text-on-surface-variant line-through"
                        >
                            {{ originalPrice }}
                        </p>
                    </div>

                    <p
                        v-if="product.short_description"
                        class="font-sans text-body-lg text-on-surface-variant"
                    >
                        {{ product.short_description }}
                    </p>

                    <p
                        v-if="isOutOfStock"
                        class="font-sans text-body-sm font-bold text-error"
                    >
                        Currently out of stock
                    </p>
                    <p
                        v-else-if="product.is_low_stock"
                        class="font-sans text-body-sm font-semibold text-warning"
                    >
                        Only {{ product.stock_quantity }} left
                    </p>
                </div>

                <div
                    v-if="(product.attributes ?? []).length"
                    class="flex flex-col gap-lg border-y border-outline-variant py-lg"
                >
                    <div
                        v-for="attribute in product.attributes"
                        :key="attribute.id"
                        class="flex flex-col gap-sm"
                    >
                        <span
                            class="font-sans text-label-caps uppercase text-primary"
                        >
                            {{ attribute.name }}
                        </span>

                        <div
                            v-if="
                                attribute.display_type === 'swatches'
                            "
                            class="flex flex-wrap gap-sm"
                        >
                            <button
                                v-for="value in attribute.values"
                                :key="value.id"
                                type="button"
                                class="h-10 w-10 rounded-full border-2 transition-all"
                                :class="
                                    selectedAttributes[attribute.id] === value.id
                                        ? 'border-accent ring-2 ring-accent/20'
                                        : 'border-outline-variant hover:border-accent'
                                "
                                :style="{
                                    backgroundColor:
                                        value.color_swatch || '#d6c2bd',
                                }"
                                :title="value.value"
                                :aria-label="value.value"
                                @click="
                                    selectAttributeValue(
                                        attribute.id,
                                        value.id,
                                    )
                                "
                            />
                        </div>

                        <div
                            v-else-if="attribute.display_type === 'dropdown'"
                            class="max-w-xs"
                        >
                            <AppSelect
                                v-model="selectedAttributes[attribute.id]"
                                :options="dropdownOptions(attribute)"
                                :placeholder="`Select ${attribute.name}`"
                                size="sm"
                            />
                        </div>

                        <div v-else class="flex flex-wrap gap-sm">
                            <button
                                v-for="value in attribute.values"
                                :key="value.id"
                                type="button"
                                class="rounded-full px-lg py-sm font-sans text-label-caps uppercase transition-colors"
                                :class="
                                    selectedAttributes[attribute.id] === value.id
                                        ? 'bg-primary text-white'
                                        : 'border border-outline text-on-surface hover:bg-primary/5'
                                "
                                @click="
                                    selectAttributeValue(
                                        attribute.id,
                                        value.id,
                                    )
                                "
                            >
                                {{ value.value }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap items-end gap-lg">
                    <div class="flex flex-col gap-sm">
                        <span
                            class="font-sans text-label-caps uppercase text-primary"
                        >
                            Quantity
                        </span>
                        <div
                            class="flex h-12 items-center rounded-full border border-outline"
                        >
                            <button
                                type="button"
                                class="px-md text-primary transition-colors hover:text-accent disabled:opacity-40"
                                :disabled="quantity <= 1 || isOutOfStock"
                                aria-label="Decrease quantity"
                                @click="decreaseQty"
                            >
                                <IconMinus :size="18" stroke-width="1.5" />
                            </button>
                            <span class="px-md font-sans text-body-lg font-bold">
                                {{ quantity }}
                            </span>
                            <button
                                type="button"
                                class="px-md text-primary transition-colors hover:text-accent disabled:opacity-40"
                                :disabled="
                                    quantity >= maxQuantity || isOutOfStock
                                "
                                aria-label="Increase quantity"
                                @click="increaseQty"
                            >
                                <IconPlus :size="18" stroke-width="1.5" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-md">
                    <button
                        type="button"
                        class="flex h-12 w-full items-center justify-center gap-md rounded-full bg-accent font-sans text-title-lg text-white shadow-card transition-all hover:opacity-90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="isOutOfStock"
                        @click="handleAddToCart"
                    >
                        <IconShoppingCart :size="22" stroke-width="1.5" />
                        {{ isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}
                    </button>
                    <button
                        type="button"
                        class="flex h-12 w-full items-center justify-center gap-md rounded-full border border-primary font-sans text-title-lg text-primary transition-all hover:bg-primary hover:text-white"
                        @click="handleToggleFavourite"
                    >
                        <IconHeartFilled
                            v-if="isFavourited"
                            :size="22"
                            stroke-width="1.5"
                        />
                        <IconHeart
                            v-else
                            :size="22"
                            stroke-width="1.5"
                        />
                        {{
                            isFavourited
                                ? 'Saved to Favourites'
                                : 'Add to Favourites'
                        }}
                    </button>
                </div>

                <div
                    class="grid grid-cols-3 gap-md rounded-xl border border-outline-variant/30 bg-white py-md shadow-card"
                >
                    <div class="flex flex-col items-center gap-xs text-center">
                        <IconTruck
                            :size="22"
                            stroke-width="1.5"
                            class="text-accent"
                        />
                        <span class="font-sans text-[10px] font-bold uppercase leading-tight tracking-wide">
                            Free over $35
                        </span>
                    </div>
                    <div
                        class="flex flex-col items-center gap-xs border-x border-outline-variant text-center"
                    >
                        <IconClock
                            :size="22"
                            stroke-width="1.5"
                            class="text-accent"
                        />
                        <span class="font-sans text-[10px] font-bold uppercase leading-tight tracking-wide">
                            Fresh Daily
                        </span>
                    </div>
                    <div class="flex flex-col items-center gap-xs text-center">
                        <IconShieldCheck
                            :size="22"
                            stroke-width="1.5"
                            class="text-accent"
                        />
                        <span class="font-sans text-[10px] font-bold uppercase leading-tight tracking-wide">
                            Secure Checkout
                        </span>
                    </div>
                </div>
            </div>
        </main>

        <!-- Tabs -->
        <section class="container-page py-xxl">
            <div class="mb-xl flex border-b border-outline-variant">
                <button
                    type="button"
                    class="px-xl py-md font-sans text-title-lg transition-colors"
                    :class="
                        activeTab === 'description'
                            ? 'border-b-2 border-accent text-primary'
                            : 'text-on-surface-variant hover:text-primary'
                    "
                    @click="activeTab = 'description'"
                >
                    Description
                </button>
                <button
                    type="button"
                    class="px-xl py-md font-sans text-title-lg transition-colors"
                    :class="
                        activeTab === 'reviews'
                            ? 'border-b-2 border-accent text-primary'
                            : 'text-on-surface-variant hover:text-primary'
                    "
                    @click="activeTab = 'reviews'"
                >
                    Reviews ({{ reviewsCount }})
                </button>
                <button
                    type="button"
                    class="px-xl py-md font-sans text-title-lg transition-colors"
                    :class="
                        activeTab === 'ingredients'
                            ? 'border-b-2 border-accent text-primary'
                            : 'text-on-surface-variant hover:text-primary'
                    "
                    @click="activeTab = 'ingredients'"
                >
                    Ingredients
                </button>
            </div>

            <!-- Description -->
            <div
                v-if="activeTab === 'description'"
                class="grid grid-cols-1 gap-xxl md:grid-cols-2"
            >
                <div class="flex flex-col gap-lg">
                    <h3 class="font-serif text-headline-sm text-primary">
                        About this pastry
                    </h3>
                    <p
                        class="whitespace-pre-wrap leading-relaxed text-on-surface-variant"
                    >
                        {{
                            product.description
                                || product.short_description
                                || 'Details for this product will appear here soon.'
                        }}
                    </p>
                </div>
                <div
                    class="flex flex-col gap-lg rounded-xl border border-outline-variant/30 bg-white p-xl shadow-card"
                >
                    <h4 class="font-sans text-title-lg text-primary">
                        Product details
                    </h4>
                    <dl class="flex flex-col gap-md font-sans text-body-sm">
                        <div class="flex justify-between gap-md">
                            <dt class="text-on-surface-variant">SKU</dt>
                            <dd class="font-semibold text-primary">
                                {{ product.sku || '—' }}
                            </dd>
                        </div>
                        <div class="flex justify-between gap-md">
                            <dt class="text-on-surface-variant">Availability</dt>
                            <dd class="font-semibold text-primary">
                                {{
                                    isOutOfStock
                                        ? 'Out of stock'
                                        : 'In stock'
                                }}
                            </dd>
                        </div>
                        <div
                            v-if="!isOutOfStock"
                            class="flex justify-between gap-md"
                        >
                            <dt class="text-on-surface-variant">Quantity</dt>
                            <dd class="font-semibold text-primary">
                                {{ product.stock_quantity }}
                            </dd>
                        </div>
                        <div
                            v-if="product.short_description"
                            class="border-t border-outline-variant pt-md"
                        >
                            <dt class="mb-sm text-on-surface-variant">
                                Summary
                            </dt>
                            <dd class="leading-relaxed text-on-surface">
                                {{ product.short_description }}
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Reviews -->
            <div
                v-else-if="activeTab === 'reviews'"
                class="flex flex-col items-start gap-xxl md:flex-row"
            >
                <div class="w-full md:sticky md:top-32 md:w-1/3">
                    <div
                        class="rounded-xl border border-outline-variant/30 bg-white p-xl text-center shadow-card"
                    >
                        <p class="font-serif text-display-lg text-primary">
                            {{ averageRating.toFixed(1) }}
                        </p>
                        <div class="my-sm flex justify-center text-accent">
                            <IconStarFilled
                                v-for="i in fullStars"
                                :key="`summary-full-${i}`"
                                :size="22"
                            />
                            <IconStarHalfFilled
                                v-if="hasHalfStar"
                                :size="22"
                            />
                        </div>
                        <p class="font-sans text-body-sm text-on-surface-variant">
                            Based on {{ reviewsCount }}
                            {{ reviewsCount === 1 ? 'review' : 'reviews' }}
                        </p>

                        <div class="mt-xl flex flex-col gap-sm">
                            <div
                                v-for="row in ratingBreakdown"
                                :key="row.star"
                                class="flex items-center gap-md"
                                :class="{
                                    'text-on-surface-variant': row.count === 0,
                                }"
                            >
                                <span class="w-4 font-sans text-body-sm">
                                    {{ row.star }}
                                </span>
                                <div
                                    class="h-2 flex-1 overflow-hidden rounded-full bg-surface-container-highest"
                                >
                                    <div
                                        class="h-full rounded-full bg-accent transition-all"
                                        :style="{ width: `${row.percent}%` }"
                                    />
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-xl w-full rounded-full border border-primary py-sm font-sans text-title-lg text-primary transition-all hover:bg-primary hover:text-white"
                            @click="handleWriteReview"
                        >
                            Write a Review
                        </button>
                    </div>
                </div>

                <div class="flex w-full flex-col gap-lg md:w-2/3">
                    <ShopReviewCard
                        v-for="review in reviews.items ?? []"
                        :key="review.id"
                        :review="review"
                    />
                    <p
                        v-if="!(reviews.items ?? []).length"
                        class="rounded-xl border border-dashed border-outline-variant bg-white p-xl text-center text-on-surface-variant"
                    >
                        No reviews yet. Be the first to share your thoughts.
                    </p>
                </div>
            </div>

            <!-- Ingredients -->
            <div
                v-else
                class="rounded-xl border border-dashed border-outline-variant bg-white p-xxl text-center"
            >
                <h3 class="mb-sm font-serif text-headline-sm text-primary">
                    Ingredients
                </h3>
                <p class="font-sans text-body-lg text-on-surface-variant">
                    Ingredient details coming soon.
                </p>
            </div>
        </section>

        <!-- Related -->
        <section
            v-if="relatedProducts.length"
            class="bg-surface-container-low py-xxl"
        >
            <div class="container-page">
                <h2 class="mb-xxl font-serif text-headline-lg text-primary">
                    You might also like
                </h2>
                <div
                    class="grid grid-cols-1 gap-lg sm:grid-cols-2 lg:grid-cols-4"
                >
                    <RelatedProductCard
                        v-for="item in relatedProducts"
                        :key="item.id"
                        :product="item"
                    />
                </div>
            </div>
        </section>
    </AppLayout>
</template>
