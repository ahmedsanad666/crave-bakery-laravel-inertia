<script setup>
import ProductForm from '@/Components/Admin/ProductForm.vue';

defineProps({
    product: {
        type: Object,
        required: true,
    },
    categoryOptions: {
        type: Array,
        default: () => [],
    },
    attributeOptions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <ProductForm
        mode="edit"
        :product="product"
        :category-options="categoryOptions"
        :attribute-options="attributeOptions"
    />
</template>
