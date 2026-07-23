<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const productHref = computed(() => route('products.show', props.product.slug));

const categoryLabel = computed(
    () => props.product.categories?.[0]?.name ?? 'Pastry',
);

const displayPrice = computed(() => {
    const price = props.product.sale_price ?? props.product.regular_price;

    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(price);
});
</script>

<template>
    <Link
        :href="productHref"
        class="group overflow-hidden rounded-xl bg-white shadow-card transition-all hover:shadow-interactive"
    >
        <div class="aspect-square overflow-hidden bg-surface-container">
            <img
                v-if="product.thumbnail"
                :src="product.thumbnail"
                :alt="product.name"
                class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            />
            <div
                v-else
                class="flex h-full w-full items-center justify-center text-4xl"
            >
                🧁
            </div>
        </div>
        <div class="flex flex-col gap-sm p-lg">
            <span
                class="font-sans text-label-caps uppercase text-on-surface-variant"
            >
                {{ categoryLabel }}
            </span>
            <h4 class="font-serif text-title-lg text-primary">
                {{ product.name }}
            </h4>
            <p class="font-sans text-title-lg text-accent">
                {{ displayPrice }}
            </p>
        </div>
    </Link>
</template>
