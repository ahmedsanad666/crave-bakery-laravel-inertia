<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    IconHeart,
    IconHeartFilled,
    IconStarFilled,
} from '@tabler/icons-vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['add-to-cart', 'toggle-favourite', 'notify-me']);

const formatMoney = (price) =>
    new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);

const displayPrice = computed(() => {
    const price = props.product.sale_price ?? props.product.regular_price;

    return formatMoney(price);
});

const originalPrice = computed(() => {
    if (
        props.product.sale_price == null
        || Number(props.product.sale_price) >= Number(props.product.regular_price)
    ) {
        return null;
    }

    return formatMoney(props.product.regular_price);
});

const isOutOfStock = computed(
    () => props.product.stock_status === 'out_of_stock',
);

const categoryLabel = computed(
    () => props.product.categories?.[0]?.name?.toUpperCase() ?? 'PASTRY',
);

const productHref = computed(() => route('products.show', props.product.slug));

const badgeClass = computed(() => {
    const badge = props.product.badge;

    if (badge === 'New') {
        return 'bg-accent text-white';
    }

    if (badge === 'Best Seller') {
        return 'bg-primary-container text-on-primary-container';
    }

    return 'bg-primary-container text-on-primary-container';
});

const ratingDisplay = computed(() => {
    const rating = Number(props.product.average_rating ?? 0);

    return rating > 0 ? rating.toFixed(1) : '—';
});
</script>

<template>
    <article
        class="group overflow-hidden rounded-xl bg-white shadow-card transition-all hover:-translate-y-1 hover:shadow-interactive"
        :class="{ 'opacity-80': isOutOfStock }"
    >
        <div
            class="relative aspect-square overflow-hidden"
            :class="{ grayscale: isOutOfStock }"
        >
            <Link :href="productHref" class="block h-full w-full">
                <img
                    v-if="product.thumbnail"
                    :src="product.thumbnail"
                    :alt="product.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
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
                    class="rounded-lg bg-white/90 px-lg py-sm font-sans text-body-sm font-bold tracking-widest text-primary"
                >
                    OUT OF STOCK
                </span>
            </div>

            <div
                v-if="product.badge"
                class="absolute left-md top-md rounded-md px-sm py-1 font-sans text-label-caps uppercase"
                :class="badgeClass"
            >
                {{ product.badge }}
            </div>

            <button
                type="button"
                class="absolute right-md top-md flex h-8 w-8 items-center justify-center rounded-full bg-white/80 text-primary backdrop-blur transition-colors hover:bg-white"
                :class="{
                    'bg-white text-secondary shadow-sm': product.is_favourited,
                    'opacity-50': isOutOfStock,
                }"
                :aria-label="
                    product.is_favourited
                        ? 'Remove from favourites'
                        : 'Add to favourites'
                "
                @click.prevent="emit('toggle-favourite', product)"
            >
                <IconHeartFilled
                    v-if="product.is_favourited"
                    :size="20"
                    stroke-width="1.5"
                />
                <IconHeart v-else :size="20" stroke-width="1.5" />
            </button>
        </div>

        <div class="p-md">
            <span
                class="mb-1 block font-sans text-label-caps uppercase text-on-surface-variant"
            >
                {{ categoryLabel }}
            </span>
            <h4 class="mb-xs font-serif text-headline-sm text-primary">
                <Link :href="productHref">{{ product.name }}</Link>
            </h4>

            <div class="mb-sm flex items-center gap-1">
                <span
                    class="flex"
                    :class="isOutOfStock ? 'text-outline' : 'text-[#FCB001]'"
                >
                    <IconStarFilled :size="16" />
                </span>
                <span
                    class="text-body-sm font-semibold"
                    :class="{ 'text-on-surface-variant': isOutOfStock }"
                >
                    {{ ratingDisplay }}
                </span>
                <span class="ml-1 text-body-sm text-on-surface-variant">
                    ({{ product.reviews_count ?? 0 }})
                </span>
            </div>

            <div class="mb-md flex items-center gap-2">
                <span
                    class="font-sans text-title-lg"
                    :class="isOutOfStock ? 'text-outline' : 'text-primary'"
                >
                    {{ displayPrice }}
                </span>
                <span
                    v-if="originalPrice"
                    class="font-sans text-body-sm text-on-surface-variant line-through"
                >
                    {{ originalPrice }}
                </span>
            </div>

            <button
                v-if="isOutOfStock"
                type="button"
                class="h-12 w-full rounded-full border-2 border-primary font-sans text-body-sm font-bold text-primary transition-all hover:bg-primary hover:text-white"
                @click="emit('notify-me', product)"
            >
                Notify Me
            </button>
            <button
                v-else
                type="button"
                class="h-12 w-full rounded-full bg-accent font-sans text-body-sm font-bold text-white transition-all active:scale-95 hover:opacity-90"
                @click="emit('add-to-cart', product)"
            >
                Add to Cart
            </button>
        </div>
    </article>
</template>
