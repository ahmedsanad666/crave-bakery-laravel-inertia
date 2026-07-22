<script setup>
import ProductCard from '@/Components/Public/ProductCard.vue';

defineProps({
    product: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['add-to-cart']);
</script>

<template>
    <ProductCard
        :product="product"
        compact
        @add-to-cart="emit('add-to-cart', $event)"
    />
</template>
