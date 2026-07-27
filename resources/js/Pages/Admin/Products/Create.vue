<script setup>
import ProductForm from '@/Components/Admin/ProductForm.vue';

defineProps({
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
        mode="create"
        :category-options="categoryOptions"
        :attribute-options="attributeOptions"
    />
</template>
