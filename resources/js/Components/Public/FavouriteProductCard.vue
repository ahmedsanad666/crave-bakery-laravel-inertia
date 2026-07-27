<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { IconHeartFilled, IconTrash } from '@tabler/icons-vue';
import { formatFavouritedAgo } from '@/Utils/formatFavouritedAgo';

const props = defineProps({
    favourite: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits([
    'add-to-cart',
    'remove',
    'notify-me',
    'toggle-favourite',
    'add-to-collection',
]);

const product = computed(() => props.favourite.product ?? {});

const formatMoney = (price) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);

const displayPrice = computed(() => {
    const price = product.value.sale_price ?? product.value.regular_price;
    return formatMoney(price ?? 0);
});

const originalPrice = computed(() => {
    if (
        product.value.sale_price == null
        || Number(product.value.sale_price) >= Number(product.value.regular_price)
    ) {
        return null;
    }

    return formatMoney(product.value.regular_price);
});

const isOutOfStock = computed(
    () => product.value.stock_status === 'out_of_stock',
);

const productHref = computed(() =>
    product.value.slug ? route('products.show', product.value.slug) : '#',
);

const addedLabel = computed(() =>
    formatFavouritedAgo(props.favourite.favourited_at ?? props.favourite.created_at),
);
</script>

<template>
    <article
        class="group relative overflow-hidden rounded-xl bg-white shadow-card transition-all hover:-translate-y-1 hover:shadow-interactive"
        :class="{ 'opacity-60 grayscale': isOutOfStock }"
    >
        <div class="relative aspect-square overflow-hidden bg-surface-container">
            <Link :href="productHref" class="block h-full w-full">
                <img
                    v-if="product.thumbnail"
                    :src="product.thumbnail"
                    :alt="product.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-surface-container text-5xl"
                >
                    🧁
                </div>
            </Link>

            <div
                v-if="isOutOfStock"
                class="pointer-events-none absolute inset-0 flex items-center justify-center bg-black/20"
            >
                <span
                    class="rounded-lg bg-white px-md py-xs font-sans text-label-caps uppercase text-primary"
                >
                    Out of Stock
                </span>
            </div>

            <button
                type="button"
                class="absolute left-md top-md z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-error shadow-sm transition-colors hover:bg-white"
                aria-label="Remove from favourites"
                @click.prevent="emit('remove', product)"
            >
                <IconTrash :size="18" stroke-width="1.5" />
            </button>

            <button
                type="button"
                class="absolute right-md top-md z-10 flex h-10 w-10 items-center justify-center rounded-full bg-white/90 text-secondary shadow-sm transition-colors hover:bg-white"
                aria-label="Remove from favourites"
                @click.prevent="emit('toggle-favourite', product)"
            >
                <IconHeartFilled :size="20" stroke-width="1.5" />
            </button>
        </div>

        <div class="relative space-y-xs p-md">
            <span
                v-if="product.badge && !isOutOfStock"
                class="rounded-lg bg-secondary px-sm py-xs font-sans text-label-caps uppercase text-white"
                style="position: absolute; left: 11px; margin-top: -26px;"
            >
                {{ product.badge }}
            </span>
            <span class="block font-sans text-[10px] uppercase text-on-surface-variant">
                {{ addedLabel }}
            </span>
            <h3 class="font-serif leading-snug text-on-surface" style="font-size: 0.8rem;">
                <Link :href="productHref">{{ product.name }}</Link>
            </h3>
            <div class="flex items-center gap-sm">
                <p
                    class="font-sans text-title-lg"
                    :class="isOutOfStock ? 'text-on-surface-variant' : 'text-secondary'"
                >
                    {{ displayPrice }}
                </p>
                <p
                    v-if="originalPrice"
                    class="font-sans text-body-sm text-on-surface-variant line-through"
                >
                    {{ originalPrice }}
                </p>
            </div>

            <div class="space-y-sm pt-sm">
                <button
                    v-if="isOutOfStock"
                    type="button"
                    class="w-full rounded-full border border-outline-variant py-2.5 font-sans text-label-caps text-on-surface-variant transition-colors hover:bg-surface-container"
                    @click="emit('notify-me', product)"
                >
                    Notify Me
                </button>
                <button
                    v-else
                    type="button"
                    class="w-full rounded-full bg-secondary py-2.5 font-sans text-label-caps text-white transition-opacity hover:opacity-90"
                    @click="emit('add-to-cart', product)"
                >
                    Add to Cart
                </button>
                <button
                    type="button"
                    class="w-full rounded-full border border-outline-variant py-2.5 font-sans text-label-caps text-on-surface transition-colors hover:bg-surface-container"
                    @click="emit('add-to-collection', product)"
                >
                    Add to Collection
                </button>
            </div>
        </div>
    </article>
</template>
