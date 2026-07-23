<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    IconShoppingCartPlus,
    IconStarFilled,
    IconTrash,
} from '@tabler/icons-vue';
import { formatFavouritedAgo } from '@/Utils/formatFavouritedAgo';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    addedAt: {
        type: String,
        default: null,
    },
});

const emit = defineEmits(['add-to-cart', 'remove']);

const formatMoney = (price) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);

const displayPrice = computed(() => {
    const price = props.product.sale_price ?? props.product.regular_price;
    return formatMoney(price ?? 0);
});

const isOutOfStock = computed(
    () => props.product.stock_status === 'out_of_stock',
);

const productHref = computed(() =>
    props.product.slug ? route('products.show', props.product.slug) : '#',
);

const categoryLabel = computed(
    () => props.product.categories?.[0]?.name ?? 'Pastry',
);

const addedLabel = computed(() => {
    const label = formatFavouritedAgo(props.addedAt);
    return label.replace(/^Added /i, 'Saved ');
});

const ratingDisplay = computed(() => {
    const rating = Number(props.product.average_rating ?? 0);
    return rating > 0 ? rating.toFixed(1) : '—';
});
</script>

<template>
    <div
        class="flex flex-col gap-md border-b border-outline-variant p-md last:border-0 sm:flex-row sm:items-center"
    >
        <Link
            :href="productHref"
            class="h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-surface-container"
        >
            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-full w-full object-cover"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-2xl"
            >
                🧁
            </div>
        </Link>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-md">
                <div>
                    <h4 class="font-sans text-title-lg text-on-surface">
                        <Link :href="productHref">{{ product.name }}</Link>
                    </h4>
                    <p class="font-sans text-body-sm text-on-surface-variant">
                        {{ categoryLabel }} • {{ addedLabel }}
                    </p>
                </div>
                <div class="flex shrink-0 items-center gap-xs">
                    <IconStarFilled
                        class="text-[#FCB001]"
                        :size="18"
                        stroke-width="1.5"
                    />
                    <span class="font-sans text-label-caps">{{ ratingDisplay }}</span>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-lg sm:justify-end">
            <div class="text-right">
                <p class="font-sans text-title-lg text-primary">{{ displayPrice }}</p>
                <span
                    class="inline-block rounded-lg px-sm py-xs text-[10px] font-bold uppercase"
                    :class="
                        isOutOfStock
                            ? 'bg-surface-container text-on-surface-variant'
                            : 'bg-green-50 text-green-700'
                    "
                >
                    {{ isOutOfStock ? 'Out of Stock' : 'In Stock' }}
                </span>
            </div>

            <div class="flex gap-sm">
                <button
                    v-if="!isOutOfStock"
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full bg-secondary text-white transition-opacity hover:opacity-90"
                    aria-label="Add to cart"
                    @click="emit('add-to-cart', product)"
                >
                    <IconShoppingCartPlus :size="18" stroke-width="1.5" />
                </button>
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-full border border-outline-variant text-error transition-colors hover:bg-error/5"
                    aria-label="Remove from collection"
                    @click="emit('remove', product)"
                >
                    <IconTrash :size="18" stroke-width="1.5" />
                </button>
            </div>
        </div>
    </div>
</template>
