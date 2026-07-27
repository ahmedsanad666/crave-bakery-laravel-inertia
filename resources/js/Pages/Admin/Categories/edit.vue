<script setup>
import CategoryForm from '@/Components/Admin/CategoryForm.vue';

defineProps({
    category: {
        type: Object,
        required: true,
    },
    parentOptions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <CategoryForm
        mode="edit"
        :category="category"
        :parent-options="parentOptions"
    />
</template>
