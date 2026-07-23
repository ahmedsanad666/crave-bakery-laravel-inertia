<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['add-to-cart']);

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

const productHref = computed(() => route('products.show', props.product.slug));
</script>

<template>
    <article
        class="group rounded-2xl border border-white/10 bg-white/5 p-md backdrop-blur-sm transition-all hover:bg-white/10"
    >
        <div class="relative mb-lg aspect-[4/3] overflow-hidden rounded-xl">
            <span
                class="absolute left-3 top-3 z-10 rounded-full bg-accent px-md py-1 text-xs font-bold uppercase tracking-wider text-on-primary"
            >
                New
            </span>
            <Link :href="productHref" class="block h-full w-full">
                <img
                    v-if="product.thumbnail"
                    :src="product.thumbnail"
                    :alt="product.name"
                    class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110"
                    :class="{ 'opacity-60 grayscale': isOutOfStock }"
                />
                <div
                    v-else
                    class="flex h-full w-full items-center justify-center bg-white/5 text-4xl"
                >
                    🧁
                </div>
            </Link>
        </div>

        <h3 class="mb-sm font-serif text-title-lg text-on-primary">
            <Link :href="productHref">{{ product.name }}</Link>
        </h3>
        <p
            class="mb-lg line-clamp-2 text-body-sm text-on-primary/60"
        >
            {{
                product.short_description ||
                'Fresh from our ovens — limited seasonal batch.'
            }}
        </p>
        <div class="flex items-center justify-between">
            <span class="font-serif text-headline-sm font-bold text-accent">
                {{ displayPrice }}
            </span>
            <button
                type="button"
                class="rounded-full bg-white/10 px-lg py-2 text-sm font-bold text-on-primary transition-all hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                :disabled="isOutOfStock"
                @click="emit('add-to-cart', product)"
            >
                {{ isOutOfStock ? 'Sold Out' : 'Quick Add' }}
            </button>
        </div>
    </article>
</template>
