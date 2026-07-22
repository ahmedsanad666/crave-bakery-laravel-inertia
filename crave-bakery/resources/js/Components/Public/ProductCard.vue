<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { IconShoppingCart } from '@tabler/icons-vue';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    compact: {
        type: Boolean,
        default: false,
    },
    /** First card in a row uses filled accent CTA (theme pattern). */
    featuredCta: {
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

const isOutOfStock = computed(
    () => props.product.stock_status === 'out_of_stock',
);

const productHref = computed(() => `/products/${props.product.slug}`);
</script>

<template>
    <!-- Compact recommended card -->
    <article
        v-if="compact"
        class="group rounded-xl border border-outline-variant bg-white p-sm shadow-sm"
    >
        <Link :href="productHref" class="mb-md block aspect-square overflow-hidden rounded-lg">
            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                :class="{ 'opacity-60 grayscale': isOutOfStock }"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center bg-surface-container text-3xl"
            >
                🧁
            </div>
        </Link>
        <h4 class="mb-xs text-body-lg font-bold text-primary">
            <Link :href="productHref">{{ product.name }}</Link>
        </h4>
        <p class="mb-md text-body-sm text-on-surface-variant">
            {{ displayPrice }}
        </p>
        <button
            type="button"
            class="w-full rounded-lg bg-primary py-2 text-xs font-bold text-on-primary transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
            :disabled="isOutOfStock"
            @click="emit('add-to-cart', product)"
        >
            {{ isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}
        </button>
    </article>

    <!-- Premium selection card -->
    <article
        v-else
        class="group relative transform overflow-hidden rounded-2xl bg-white shadow-md transition-all duration-300 hover:-translate-y-2 hover:shadow-2xl"
    >
        <div
            class="absolute right-4 top-4 z-10 rounded-full bg-white/90 px-md py-xs shadow-sm backdrop-blur-sm"
        >
            <span class="font-sans text-title-lg font-bold text-accent">
                {{ displayPrice }}
            </span>
        </div>

        <Link :href="productHref" class="block aspect-square overflow-hidden bg-surface-container">
            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                :class="{ 'opacity-60 grayscale': isOutOfStock }"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-5xl"
                :class="{ 'opacity-60': isOutOfStock }"
            >
                🧁
            </div>
        </Link>

        <div class="space-y-md p-lg">
            <div>
                <h3 class="font-serif text-headline-sm text-primary">
                    <Link :href="productHref">{{ product.name }}</Link>
                </h3>
                <p
                    v-if="product.short_description"
                    class="mt-1 line-clamp-2 font-sans text-body-sm text-on-surface-variant"
                >
                    {{ product.short_description }}
                </p>
            </div>

            <button
                type="button"
                class="flex w-full items-center justify-center gap-sm rounded-full py-md font-bold transition-all active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                :class="
                    featuredCta
                        ? 'bg-accent text-on-primary'
                        : 'border border-accent text-accent hover:bg-accent hover:text-white'
                "
                :disabled="isOutOfStock"
                @click="emit('add-to-cart', product)"
            >
                <IconShoppingCart :size="18" stroke-width="1.5" />
                {{ isOutOfStock ? 'Out of Stock' : 'Add to Cart' }}
            </button>
        </div>
    </article>
</template>
