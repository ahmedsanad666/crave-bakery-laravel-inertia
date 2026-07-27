<script setup>
import CategoryForm from '@/Components/Admin/CategoryForm.vue';

defineProps({
    parentOptions: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <CategoryForm mode="create" :parent-options="parentOptions" />
</template>
