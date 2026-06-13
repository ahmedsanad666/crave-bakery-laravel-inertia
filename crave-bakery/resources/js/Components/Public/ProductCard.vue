<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import {
    IconHeart,
    IconHeartFilled,
    IconShoppingCart,
    IconStarFilled,
} from '@tabler/icons-vue';
import AppButton from '@/Components/Shared/AppButton.vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['add-to-cart', 'toggle-favourite']);

const displayPrice = computed(() => {
    const price = props.product.sale_price ?? props.product.regular_price;

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
});

const originalPrice = computed(() => {
    if (!props.product.sale_price) {
        return null;
    }

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(props.product.regular_price);
});

const isOutOfStock = computed(
    () => props.product.stock_status === 'out_of_stock',
);

const categoryLabel = computed(
    () => props.product.categories?.[0]?.name ?? 'Pastry',
);
</script>

<template>
    <article
        class="group flex flex-col overflow-hidden rounded-card bg-card shadow-card transition-shadow hover:shadow-interactive"
        :class="{ 'flex-row gap-4 p-4': compact }"
    >
        <Link
            :href="`/products/${product.slug}`"
            class="relative block shrink-0 overflow-hidden"
            :class="compact ? 'h-24 w-24 rounded-lg' : 'aspect-square'"
        >
            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                :class="{ 'opacity-60 grayscale': isOutOfStock }"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-surface-container text-4xl"
                :class="{ 'opacity-60': isOutOfStock }"
            >
                🧁
            </div>

            <span
                v-if="product.badge"
                class="absolute left-3 top-3 rounded-badge bg-accent px-2 py-1 text-xs font-semibold text-white"
            >
                {{ product.badge }}
            </span>
        </Link>

        <div class="flex flex-1 flex-col p-4" :class="{ 'p-0': compact }">
            <p class="text-label-caps uppercase text-text-muted">
                {{ categoryLabel }}
            </p>

            <Link
                :href="`/products/${product.slug}`"
                class="mt-1 font-serif text-headline-sm text-primary transition-colors hover:text-accent"
            >
                {{ product.name }}
            </Link>

            <div
                v-if="product.reviews_count > 0"
                class="mt-2 flex items-center gap-1.5"
            >
                <div class="flex text-accent">
                    <IconStarFilled
                        v-for="i in 5"
                        :key="i"
                        :size="14"
                        :class="
                            i <= Math.round(product.average_rating)
                                ? 'opacity-100'
                                : 'opacity-30'
                        "
                    />
                </div>
                <span class="text-xs text-text-muted">
                    ({{ product.reviews_count }})
                </span>
            </div>

            <p
                v-if="!compact && product.short_description"
                class="mt-2 line-clamp-2 text-body-sm text-text-muted"
            >
                {{ product.short_description }}
            </p>

            <div class="mt-auto flex items-end justify-between gap-3 pt-4">
                <div>
                    <span class="font-sans text-title-lg text-primary">
                        {{ displayPrice }}
                    </span>
                    <span
                        v-if="originalPrice"
                        class="ml-2 text-body-sm text-text-muted line-through"
                    >
                        {{ originalPrice }}
                    </span>
                </div>

                <button
                    type="button"
                    class="rounded-full p-2 text-primary transition-colors hover:bg-surface-container hover:text-accent"
                    :class="{ 'text-accent': product.is_favourited }"
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

            <AppButton
                class="mt-4 w-full"
                :disabled="isOutOfStock"
                @click="emit('add-to-cart', product)"
            >
                <span class="inline-flex items-center justify-center gap-2">
                    <IconShoppingCart :size="18" stroke-width="1.5" />
                    {{ isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}
                </span>
            </AppButton>
        </div>
    </article>
</template>
