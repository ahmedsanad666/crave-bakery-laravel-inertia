<script setup>
defineProps({
    value: {
        type: String,
        default: '',
    },
    for: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <label
        :for="$props.for"
        class="mb-1 block font-sans text-sm font-medium text-text-primary"
    >
        <span v-if="value">{{ value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
